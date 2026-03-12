# ESG-BALANCE - Project Guide

## Overview

**ESG-BALANCE** is a web platform for integrated management of corporate financial balance sheets and ESG (Environmental, Social, Governance) indicators. Developed as a Database course project for the University of Bologna (A.A. 2025/2026). It supports the complete lifecycle of balance sheet creation, review, and approval with focus on corporate sustainability.

Language: Italian (UI, database, comments).

## Tech Stack

- **Backend**: PHP 8.0+ (procedural, separated concerns)
- **Database**: MySQL/MariaDB 8.0+ (16 normalized BCNF tables) via PDO prepared statements
- **NoSQL**: MongoDB 5.0+ (event logging/audit trail) via composer mongodb/mongodb ^1.19
- **Frontend**: HTML5, Bootstrap 5.3.3, vanilla ES6+ JavaScript, Bootstrap Icons
- **Server**: Apache 2.4+ via XAMPP
- **Security**: Bcrypt password hashing, session regeneration, XSS prevention via htmlspecialchars(), MIME validation with finfo

## File Structure

```text
ESG-BALANCE/
├── assets/
│   ├── css/style.css              # Custom design system (Inter font, CSS variables)
│   ├── js/app.js                  # Vanilla JS utilities (auto-dismiss, tooltips)
│   └── uploads/                   # User-uploaded files (logos, CVs)
├── config/
│   ├── database.php               # MySQL PDO singleton (localhost, esg_balance, utf8mb4)
│   └── mongodb.php                # MongoDB singleton (localhost:27017, esg_balance.events)
├── includes/
│   ├── auth.php                   # Guards: requireLogin(), requireRole(), isLoggedIn(), currentRole(), currentUser()
│   ├── db.php                     # DB wrappers: callSP(), execSP(), query(), queryOne()
│   ├── functions.php              # logEvent(), setFlash(), getFlash(), renderFlash(), redirectWith()
│   ├── header.php                 # HTML head + navbar (role-based menus)
│   └── footer.php                 # Footer + Bootstrap JS + app.js
├── pages/
│   ├── admin/
│   │   ├── template.php           # Manage accounting template (voci_contabili)
│   │   ├── indicatori.php         # Manage ESG indicators (with subtypes)
│   │   └── assegna_revisore.php   # Assign reviewers to balance sheets
│   ├── responsabile/
│   │   ├── aziende.php            # Company CRUD
│   │   ├── bilancio.php           # Balance sheet creation + value editing
│   │   └── indicatori_bilancio.php # Link ESG indicators to balance items
│   ├── revisore/
│   │   ├── competenze.php         # Skills management (levels 0-5)
│   │   ├── revisione.php          # Review assigned balances + add notes
│   │   └── giudizio.php           # Submit final judgment
│   ├── dashboard.php              # Role-based dashboard with personalized widgets
│   ├── login.php                  # Login page
│   ├── register.php               # Registration (revisore/responsabile)
│   └── statistiche.php            # 4 analytics views (accessible to all authenticated)
├── sql/
│   ├── schema.sql                 # 16 tables DDL
│   ├── stored_procedures.sql      # 14 stored procedures
│   ├── triggers.sql               # 4 triggers
│   ├── views.sql                  # 4 views
│   └── seed.sql                   # Demo data (5 users, 3 companies, 6 balances)
├── index.php                      # Landing page (redirects to dashboard if logged in)
├── composer.json                  # MongoDB dependency
├── README.md                      # Full documentation
└── relazione.md                   # Technical report
```

## User Roles & Permissions

### Amministratore

- Manage accounting template (`pages/admin/template.php`)
- Manage ESG indicators with subtypes (`pages/admin/indicatori.php`)
- Assign reviewers to balances (`pages/admin/assegna_revisore.php`)
- View all statistics
- Cannot self-register (seeded in DB)

### Revisore

- Declare professional skills (`pages/revisore/competenze.php`)
- Review assigned balances with notes (`pages/revisore/revisione.php`)
- Submit judgments: approvazione / approvazione_con_rilievi / respingimento (`pages/revisore/giudizio.php`)
- View personal reliability index

### Responsabile

- Register and manage companies (`pages/responsabile/aziende.php`)
- Create balance sheets (`pages/responsabile/bilancio.php`)
- Fill in accounting values (only when stato = 'bozza')
- Link ESG indicators to balance items (`pages/responsabile/indicatori_bilancio.php`)

## Database Schema (16 Tables)

### User Hierarchy (Total, Exclusive)

- `utenti` — PK: username | password_hash, codice_fiscale, data_nascita, luogo_nascita, ruolo ENUM('amministratore','revisore','responsabile')
- `email_utente` — PK: (username, email) | FK: username → utenti
- `revisori` — PK: username → utenti | nr_revisioni, indice_affidabilita
- `responsabili` — PK: username → utenti | curriculum_pdf
- `competenze_revisore` — PK: (username_revisore, nome_indicatore) | livello CHECK(0-5)

### ESG Indicator Hierarchy (Partial, Exclusive)

- `indicatori_esg` — PK: nome | immagine, rilevanza CHECK(0-10), tipo ENUM('ambientale','sociale','governance')
- `indicatori_ambientali` — PK: nome → indicatori_esg | codice_normativa
- `indicatori_sociali` — PK: nome → indicatori_esg | ambito_sociale, frequenza_rilevazione

### Core Business Entities

- `aziende` — PK: id (auto_increment) | ragione_sociale (UNIQUE), partita_iva, settore, num_dipendenti, logo, nr_bilanci (redundancy), username_responsabile FK
- `voci_contabili` — PK: nome | descrizione (shared accounting template)
- `bilanci` — PK: id (auto_increment) | id_azienda FK, data_creazione, stato ENUM('bozza','in_revisione','approvato','respinto')
- `valori_bilancio` — PK: (id_bilancio, nome_voce) | valore DECIMAL(15,2)
- `voci_indicatori` — PK: (id_bilancio, nome_voce, nome_indicatore) | valore_indicatore, fonte, data_rilevazione

### Review Workflow

- `revisioni` — PK: (username_revisore, id_bilancio) | M:N reviewer-balance assignments
- `note_revisione` — PK: id (auto_increment) | username_revisore, id_bilancio, nome_voce FK, data_nota, testo
- `giudizi` — PK: (username_revisore, id_bilancio) | esito ENUM('approvazione','approvazione_con_rilievi','respingimento'), data_giudizio, rilievi

## 14 Stored Procedures

| Procedure | Purpose |
| --------- | ------- |
| `sp_login` | Returns user credentials for authentication |
| `sp_registra_utente` | Atomic registration (parent + subtable + email) |
| `sp_aggiungi_email` | Add email to existing user |
| `sp_crea_voce_contabile` | Create accounting template entry |
| `sp_inserisci_indicatore_esg` | Insert ESG indicator with hierarchy |
| `sp_registra_azienda` | Register company for manager |
| `sp_crea_bilancio` | Create balance sheet (returns LAST_INSERT_ID) |
| `sp_inserisci_valore_bilancio` | Insert/update balance value (UPSERT) |
| `sp_collega_indicatore_voce` | Link ESG indicator to balance item |
| `sp_associa_revisore_bilancio` | Assign reviewer to balance (increments nr_revisioni) |
| `sp_inserisci_competenza` | Insert/update reviewer skill |
| `sp_inserisci_nota` | Add review note |
| `sp_inserisci_giudizio` | Submit final judgment |
| `sp_aggiorna_indice_affidabilita` | Recalculate reviewer reliability index |

## 4 Triggers

| Trigger | Event | Function |
| ------- | ----- | -------- |
| `trg_bilancio_in_revisione` | AFTER INSERT on revisioni | Changes 'bozza' → 'in_revisione' when first reviewer assigned |
| `trg_bilancio_giudizio` | AFTER INSERT on giudizi | Finalizes balance: 'respinto' if any rejection, else 'approvato'. Calls sp_aggiorna_indice_affidabilita |
| `trg_incrementa_nr_bilanci` | AFTER INSERT on bilanci | Increments aziende.nr_bilanci |
| `trg_decrementa_nr_bilanci` | AFTER DELETE on bilanci | Decrements aziende.nr_bilanci |

## 4 Views

| View | Purpose |
| ---- | ------- |
| `v_num_aziende` | Total registered companies |
| `v_num_revisori` | Total registered reviewers |
| `v_affidabilita_aziende` | Company reliability ranking (% pure approvals) |
| `v_classifica_bilanci_esg` | Balance sheets ranked by # of ESG indicators |

## Key DB Wrapper Functions (includes/db.php)

```php
callSP($sp_name, $params)  // Call SP, return result set
execSP($sp_name, $params)  // Call SP, no results
query($sql, $params)        // Execute query, return all rows
queryOne($sql, $params)     // Execute query, return first row
```

All use PDO prepared statements with `?` placeholders.

## Coding Conventions

### PHP

- Procedural architecture (no OOP)
- All DB writes via Stored Procedures (never raw INSERT/UPDATE from PHP)
- Snake_case for variables (`$password_hash`, `$nr_revisioni`)
- Guards at top of each protected page: `requireLogin()` + `requireRole()`
- Flash messages for user feedback via `setFlash()` / `renderFlash()`
- Events logged to MongoDB via `logEvent($evento, $dettagli, $utente)`
- Error handling: try-catch with PDOException code 23000 → "already exists"

### Database Naming

- Tables: snake_case plural (`voci_contabili`, `note_revisione`)
- Procedures: `sp_` prefix (`sp_inserisci_giudizio`)
- Triggers: `trg_` prefix (`trg_bilancio_giudizio`)
- Views: `v_` prefix (`v_affidabilita_aziende`)

### Frontend

- Design palette: Black (#111111) / White (#ffffff) / Blue accent (#2563eb)
- Typography: Inter font (system-ui fallback)
- CSS custom properties for design tokens
- Component classes: `.card-modern`, `.dashboard-card`, `.glass-card`, `.badge-animated`
- Status badge colors: bozza=secondary, in_revisione=primary, approvato=primary, respinto=secondary+border
- Two-column layouts: form (left) + data table (right)
- Icons: Bootstrap Icons with semantic meaning

### Security

- `password_hash()` with bcrypt (cost 12)
- `password_verify()` for timing-safe comparison
- `session_regenerate_id(true)` on login
- Session timeout after 1 hour of inactivity (`$_SESSION['last_activity']`)
- CSRF tokens on all forms via `csrfField()` / `verifyCsrf()` using `random_bytes(32)` + `hash_equals()`
- `htmlspecialchars()` on all user-generated output
- `finfo_file()` for MIME type validation on uploads with error feedback
- Generic error messages (don't reveal which field is wrong)
- 100% prepared statements (no string concatenation in SQL)

## Seed Data (sql/seed.sql)

- **f.montanari** (amministratore)
- **m.conti**, **s.ferrara** (revisori)
- **a.pellegrini**, **l.damico** (responsabili)
- 3 companies, 6 balance sheets, sample accounting entries, ESG indicators, and reviews
- All passwords: "password123" (bcrypt hashed)

## MongoDB Event Logging

Collection: `esg_balance.events`

```json
{
  "evento": "login|registrazione|creazione_bilancio|...",
  "utente": "username",
  "dettagli": "Description string",
  "timestamp": ISODate()
}
```

Fallback to `error_log()` if MongoDB is unavailable.

## Business Rules (Automated)

- **RV4**: Trigger T1 auto-transitions 'bozza' → 'in_revisione' on first reviewer assignment
- **RV5**: Trigger T2 auto-finalizes balance state after all reviewers vote
- **RV6**: Triggers T3/T4 maintain nr_bilanci redundancy (60.7% cost savings vs COUNT(*))
- **RV7**: SP5 handles ESG indicator hierarchy insertion
- **RV8**: SP2 ensures atomic user registration (parent + subtable + email)
- **RV9**: Composite PK on giudizi prevents duplicate votes
- **RV10**: Application-level check: edit values only if stato = 'bozza'
- **RV11**: Trigger T2 calls SP14 to update reviewer reliability after judgment
