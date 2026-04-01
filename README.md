# ESG-BALANCE

Progetto per il corso di Basi di Dati (A.A. 2025/2026, Unibo). Applicazione web PHP per bilanci aziendali e indicatori ESG.

## Requisiti

- PHP >= 8.0 (con estensioni: pdo_mysql, fileinfo)
- MySQL 8.0
- XAMPP (o equivalente con Apache)
- Composer (solo se si vuole usare MongoDB)
- MongoDB >= 5.0 (opzionale — il sistema funziona anche senza)

## Installazione

1. Clonare la repo in `htdocs/ESG-BALANCE`
2. Importare lo schema e i dati di esempio (nell'ordine indicato):
   ```sql
   mysql -u root -p < sql/schema.sql
   mysql -u root -p esg_balance < sql/stored_procedures.sql
   mysql -u root -p esg_balance < sql/triggers.sql
   mysql -u root -p esg_balance < sql/views.sql
   mysql -u root -p esg_balance < sql/seed.sql
   ```
3. (Opzionale) Installare le dipendenze Composer per MongoDB:
   ```bash
   composer install
   ```
4. Avviare Apache e MySQL da XAMPP
5. Aprire `http://localhost/ESG-BALANCE/`

## Credenziali demo

| Utente | Password | Ruolo |
|--------|----------|-------|
| admin    | admin       | Amministratore |
| tesfaye  | password123 | Revisore ESG   |
| giovanni | password123 | Responsabile   |

## Struttura

```
ESG-BALANCE/
├── assets/          # CSS, JS, uploads
├── config/          # Configurazione DB e MongoDB
├── includes/        # Funzioni condivise (auth, db, header, footer)
├── pages/           # Pagine per ruolo (admin, revisore, responsabile)
├── sql/             # Schema, seed, stored procedure, trigger, viste
├── index.php        # Landing page
├── relazione.html   # Relazione di progetto (formato A4)
└── presentazione.html
```

## Configurazione tramite variabili d'ambiente

È possibile sovrascrivere i parametri di connessione senza modificare il codice:

```
DB_HOST=localhost
DB_NAME=esg_balance
DB_USER=root
DB_PASS=
MONGO_URI=mongodb://localhost:27017
```
