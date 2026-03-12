<div align="center">

# 🌱 ESG-BALANCE

### Enterprise Sustainability & Governance Balance Management System

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.0-7952B3?style=flat&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

**Corso di Basi di Dati** • **A.A. 2025/2026**  
**CdS Informatica per il Management** • **Università di Bologna**

[Caratteristiche](#-caratteristiche-principali) •
[Installazione](#-installazione-rapida) •
[Tecnologie](#️-stack-tecnologico) •
[Documentazione](#-documentazione-tecnica) •
[Autori](#-autori)

</div>

---

## 📋 Panoramica

**ESG-BALANCE** è una piattaforma web enterprise per la gestione integrata dei bilanci di esercizio aziendali e dei relativi **indicatori ESG** (Environmental, Social, Governance). Il sistema supporta il ciclo completo di gestione, revisione e approvazione dei bilanci con focus sulla sostenibilità aziendale.

### 🎯 Caratteristiche Principali

- ✅ **Gestione Multi-Ruolo**: Sistema di autenticazione con tre livelli (Amministratore, Revisore ESG, Responsabile Aziendale)
- 📊 **Template Bilancio Condiviso**: Voci contabili standardizzate per tutte le aziende
- 🌍 **Indicatori ESG**: Gestione completa di metriche ambientali, sociali e di governance
- 🔍 **Sistema di Revisione**: Workflow di revisione con note, giudizi e approvazioni
- 📈 **Analytics Avanzati**: Statistiche e classifiche basate su affidabilità e compliance ESG
- 🔒 **Sicurezza Enterprise**: Bcrypt hashing, prepared statements, protezione XSS
- 📝 **Event Logging**: Tracciamento completo delle attività su tabella MySQL dedicata
- 🏗️ **Database Normalizzato**: Schema in BCNF con stored procedures e trigger

---

## 🚀 Installazione Rapida

### Prerequisiti

- **XAMPP** (Apache 2.4+, PHP 8.0+, MySQL 8.0+)
- **Composer** (opzionale, per dipendenze)

```bash
# 1. Clona il repository
cd C:\xampp\htdocs
git clone https://github.com/tesfaye174/ESG-BALANCE.git
cd ESG-BALANCE

# 2. Importa il database (via phpMyAdmin o CLI)
mysql -u root -p < sql/schema.sql
mysql -u root -p < sql/stored_procedures.sql
mysql -u root -p < sql/triggers.sql
mysql -u root -p < sql/views.sql

# 3. (Opzionale) Carica dati demo
mysql -u root -p < sql/seed.sql

# 4. Configura le credenziali database
# Modifica config/database.php

# 5. Avvia i servizi
# Avvia Apache e MySQL tramite XAMPP Control Panel
```

### 🌐 Accesso all'Applicazione

Apri il browser e vai su: **<http://localhost/ESG-BALANCE>**

### 👥 Utenti Demo (dopo seed.sql)

| Username | Ruolo | Password | Descrizione |
|----------|-------|----------|-------------|
| `f.montanari` | 👨‍💼 Amministratore | `password123` | Gestione template e assegnazioni |
| `m.conti` | 🔍 Revisore | `password123` | Revisione bilanci Fonderia Bertoni |
| `s.ferrara` | 🔍 Revisore | `password123` | Revisione bilanci Cantieri Damico |
| `a.pellegrini` | 📊 Responsabile | `password123` | Gestisce Fonderia Bertoni e Agri Valsamoggia |
| `l.damico` | 📊 Responsabile | `password123` | Gestisce Cantieri Damico |

---

## 🏗️ Stack Tecnologico

<div align="center">

| Categoria | Tecnologia | Versione | Utilizzo |
|-----------|-----------|----------|----------|
| **Backend** | PHP | 8.0+ | Business Logic & API |
| **Database RDBMS** | MySQL/MariaDB | 8.0+ | Gestione dati strutturati |
| **Frontend** | HTML5 + Bootstrap | 5.3 | UI/UX responsive |
| **JavaScript** | Vanilla JS | ES6+ | Interattività client-side |
| **Web Server** | Apache | 2.4+ | HTTP Server (XAMPP) |
| **Security** | Bcrypt + PDO | - | Password hashing & SQL injection prevention |

</div>

---

## 📚 Documentazione Tecnica

### Indice Completo

1. [📋 Raccolta e Analisi dei Requisiti](#1-raccolta-e-analisi-dei-requisiti)
2. [🎨 Progettazione Concettuale](#2-progettazione-concettuale)
3. [⚙️ Progettazione Logica](#3-progettazione-logica)
4. [✨ Normalizzazione](#4-normalizzazione)
5. [💻 Funzionalità Applicazione Web](#5-descrizione-funzionalita-applicazione-web)
6. [📄 Appendice: Codice SQL Completo](#6-appendice-codice-sql-completo)

---

## 1. Raccolta e Analisi dei Requisiti

### 1.1 Specifica dei Dati in Linguaggio Naturale

Si vuole realizzare una piattaforma web denominata **ESG-BALANCE** per la gestione integrata dei bilanci di esercizio aziendali e dei relativi indicatori ESG (Environmental, Social, Governance). Il sistema e' destinato a tre categorie di utenti: amministratori, revisori ESG e responsabili aziendali.

Ogni **utente** del sistema e' identificato da un username univoco e possiede una password (memorizzata come hash crittografico), un codice fiscale, una data di nascita, un luogo di nascita e un ruolo che puo' essere esclusivamente uno tra *amministratore*, *revisore* o *responsabile*. Ogni utente puo' avere uno o piu' indirizzi email associati.

I **revisori ESG** sono utenti specializzati dotati di un contatore del numero di revisioni effettuate e di un indice di affidabilita' espresso come valore decimale. Ogni revisore puo' dichiarare le proprie competenze professionali, ciascuna identificata da un nome e associata a un livello di padronanza compreso tra 0 e 5.

I **responsabili aziendali** sono utenti che gestiscono una o piu' aziende. Ogni responsabile puo' opzionalmente caricare il proprio curriculum vitae in formato PDF.

Ogni **azienda** e' identificata da un codice numerico auto-incrementante e possiede un nome, una ragione sociale (che deve essere univoca nel sistema), una partita IVA, un settore di appartenenza, il numero di dipendenti e, opzionalmente, un logo. Ogni azienda e' associata a un unico responsabile aziendale. Per ciascuna azienda si tiene traccia del numero di bilanci presenti nel sistema (dato ridondante, mantenuto aggiornato tramite trigger).

L'amministratore definisce un **template di voci contabili** condiviso fra tutte le aziende. Ogni voce contabile e' identificata dal proprio nome e possiede una descrizione testuale opzionale. Esempi di voci contabili sono "Ricavi vendite", "Costo del personale", "Ammortamenti", eccetera.

Ogni azienda puo' avere uno o piu' **bilanci di esercizio**, ciascuno identificato da un codice auto-incrementante. Ogni bilancio e' associato a un'unica azienda, possiede una data di creazione e uno stato che segue il ciclo di vita: *bozza* (stato iniziale alla creazione), *in_revisione* (quando viene assegnato almeno un revisore), *approvato* o *respinto* (esito del processo di revisione).

Per ogni bilancio, il responsabile aziendale compila i valori delle voci contabili, associando a ciascuna voce un importo numerico espresso in euro. La coppia (bilancio, voce contabile) identifica univocamente un valore.

L'amministratore popola anche la lista degli **indicatori ESG**. Ogni indicatore e' identificato dal nome, possiede opzionalmente un'immagine, un valore di rilevanza compreso tra 0 e 10 e un tipo che puo' essere *ambientale*, *sociale* oppure nullo (indicatore generico). Gli indicatori ambientali possiedono un campo aggiuntivo per il codice della normativa di riferimento. Gli indicatori sociali possiedono due campi aggiuntivi: l'ambito sociale di pertinenza e la frequenza di rilevazione.

Il responsabile aziendale puo' collegare gli indicatori ESG alle voci contabili di un bilancio. Per ogni collegamento si memorizzano il valore misurato dell'indicatore, la fonte da cui proviene il dato e la data di rilevazione. La terna (bilancio, voce contabile, indicatore ESG) identifica univocamente un collegamento.

L'amministratore assegna i revisori ESG ai bilanci da verificare. Un bilancio puo' essere assegnato a piu' revisori e un revisore puo' essere assegnato a piu' bilanci (relazione molti-a-molti). Quando il primo revisore viene assegnato a un bilancio in stato *bozza*, lo stato passa automaticamente a *in_revisione*.

Ogni revisore puo' inserire **note di revisione** sulle singole voci contabili di un bilancio a cui e' assegnato. Ogni nota possiede un identificativo auto-incrementante, una data e un testo.

Al termine dell'analisi, ogni revisore esprime un **giudizio complessivo** sul bilancio. Il giudizio possiede un esito che puo' essere *approvazione*, *approvazione_con_rilievi* o *respingimento*, una data e un campo opzionale per i rilievi testuali. Quando tutti i revisori assegnati a un bilancio hanno emesso il proprio giudizio, il sistema determina automaticamente lo stato finale del bilancio: se almeno un giudizio e' di *respingimento*, lo stato diventa *respinto*; altrimenti diventa *approvato*.

Il sistema utilizza inoltre una tabella MySQL dedicata (`log_eventi`) per il logging degli eventi significativi. Ogni record contiene un campo testuale per il tipo di evento, il nome dell'utente che ha effettuato l'operazione, i dettagli dell'evento e un timestamp.

### 1.2 Glossario dei Dati

| Termine | Descrizione | Sinonimi | Collegamenti |
|---------|------------|----------|--------------|
| Utente | Persona registrata nel sistema con credenziali di accesso | Utilizzatore, Account | Email, Revisore, Responsabile |
| Email utente | Indirizzo di posta elettronica associato a un utente | Recapito email | Utente |
| Revisore | Utente specializzato nella revisione dei bilanci ESG | Revisore ESG, Auditor | Utente, Competenza, Revisione, Nota, Giudizio |
| Responsabile | Utente che gestisce le aziende e i loro bilanci | Responsabile aziendale | Utente, Azienda |
| Competenza revisore | Abilita' professionale del revisore con livello di padronanza | Skill, Capacita' | Revisore |
| Azienda | Ente economico registrato nella piattaforma | Societa', Impresa | Responsabile, Bilancio |
| Voce contabile | Elemento del template condiviso di bilancio | Voce di bilancio, Conto | Valore bilancio, Indicatore ESG |
| Bilancio | Documento finanziario di esercizio di un'azienda | Bilancio di esercizio | Azienda, Valore bilancio, Revisione, Giudizio |
| Valore bilancio | Importo numerico associato a una voce contabile in un bilancio | Dato contabile | Bilancio, Voce contabile |
| Indicatore ESG | Metrica di sostenibilita' ambientale, sociale o di governance | KPI ESG, Indicatore di sostenibilita' | Voce contabile, Bilancio |
| Indicatore ambientale | Sotto-tipo di indicatore ESG relativo all'ambiente | Indicatore Environmental | Indicatore ESG |
| Indicatore sociale | Sotto-tipo di indicatore ESG relativo alla societa' | Indicatore Social | Indicatore ESG |
| Collegamento indicatore-voce | Associazione tra un indicatore ESG e una voce contabile di un bilancio | Voci indicatori | Bilancio, Voce contabile, Indicatore ESG |
| Revisione | Assegnazione di un revisore a un bilancio | Assegnazione revisore | Revisore, Bilancio |
| Nota di revisione | Osservazione testuale del revisore su una voce di bilancio | Nota, Commento | Revisore, Bilancio, Voce contabile |
| Giudizio | Valutazione complessiva del revisore su un bilancio | Parere, Valutazione | Revisore, Bilancio |

### 1.3 Operazioni Principali e Frequenza

| # | Operazione | Tipo | Frequenza |
|---|-----------|------|-----------|
| Op1 | Login utente (verifica credenziali) | Interattiva (I) | 50 volte/giorno |
| Op2 | Registrazione nuovo utente con email e sotto-tabella di ruolo | I | 2 volte/giorno |
| Op3 | Aggiunta indirizzo email a utente esistente | I | 1 volta/giorno |
| Op4 | Creazione nuova voce contabile nel template | I | 1 volta/settimana |
| Op5 | Inserimento nuovo indicatore ESG (con eventuale sotto-tabella) | I | 1 volta/settimana |
| Op6 | Registrazione nuova azienda associata a un responsabile | I | 1 volta/mese |
| Op7 | Creazione nuovo bilancio di esercizio per un'azienda | I | 3 volte/mese |
| Op8 | Inserimento/aggiornamento valore di una voce contabile in un bilancio | I | 20 volte/giorno |
| Op9 | Collegamento indicatore ESG a voce contabile di un bilancio | I | 10 volte/giorno |
| Op10 | Assegnazione revisore a un bilancio (con cambio stato automatico) | I | 5 volte/settimana |
| Op11 | Inserimento/aggiornamento competenza del revisore | I | 2 volte/settimana |
| Op12 | Inserimento nota del revisore su una voce di bilancio | I | 10 volte/giorno |
| Op13 | Emissione giudizio complessivo su un bilancio (con aggiornamento stato) | I | 3 volte/settimana |
| Op14 | Visualizzazione numero totale aziende registrate (vista V1) | I | 5 volte/giorno |
| Op15 | Visualizzazione numero totale revisori registrati (vista V2) | I | 5 volte/giorno |
| Op16 | Visualizzazione classifica affidabilita' aziende (vista V3) | I | 5 volte/giorno |
| Op17 | Visualizzazione classifica bilanci per indicatori ESG (vista V4) | I | 5 volte/giorno |
| OpA | Registrazione azienda con 3 bilanci annuali (analisi ridondanza) | I | 1 volta/mese |
| OpB | Conteggio bilanci per tutte le aziende (analisi ridondanza) | Batch (B) | 3 volte/mese |
| OpC | Rimozione azienda con tutti i suoi bilanci (analisi ridondanza) | B | 1 volta/mese |

### 1.4 Tavola dei Volumi

| Concetto | Tipo | Volume |
|----------|------|--------|
| Utente | Entita' | 50 |
| Email utente | Attributo multivalore | 60 |
| Revisore | Entita' (sotto-tipo) | 15 |
| Responsabile | Entita' (sotto-tipo) | 15 |
| Amministratore | Ruolo | 5 |
| Competenza revisore | Entita' | 40 |
| Azienda | Entita' | 10 |
| Voce contabile | Entita' | 20 |
| Bilancio | Entita' | 50 |
| Valore bilancio | Relazione | 250 |
| Indicatore ESG | Entita' | 30 |
| Indicatore ambientale | Entita' (sotto-tipo) | 12 |
| Indicatore sociale | Entita' (sotto-tipo) | 10 |
| Collegamento indicatore-voce | Relazione | 150 |
| Revisione (assegnazione) | Relazione | 80 |
| Nota di revisione | Entita' | 120 |
| Giudizio | Entita' | 60 |

---

## 2. Progettazione Concettuale

### 2.1 Schema E-R (Descrizione Testuale)

Lo schema Entity-Relationship del sistema ESG-BALANCE e' strutturato attorno alle seguenti entita' principali e relazioni.

#### Gerarchia degli Utenti (Totale, Esclusiva)

L'entita' **UTENTE** e' la radice di una gerarchia **totale ed esclusiva** con due sotto-entita': **REVISORE** e **RESPONSABILE**. Il terzo valore del discriminatore (`ruolo`) e' *amministratore*, che non ha attributi aggiuntivi e non necessita di una sotto-tabella. Ogni utente appartiene esattamente a una delle tre categorie.

- **UTENTE**: username (PK), password_hash, codice_fiscale, data_nascita, luogo_nascita, ruolo {amministratore, revisore, responsabile}
  - Attributo multivalore: **email** (puo' avere uno o piu' indirizzi)
- **REVISORE** (sotto-entita', ruolo = 'revisore'): nr_revisioni, indice_affidabilita
- **RESPONSABILE** (sotto-entita', ruolo = 'responsabile'): curriculum_pdf

#### Gerarchia degli Indicatori ESG (Parziale, Esclusiva)

L'entita' **INDICATORE_ESG** e' la radice di una gerarchia **parziale ed esclusiva** con due sotto-entita': **INDICATORE_AMBIENTALE** e **INDICATORE_SOCIALE**. Gli indicatori con tipo NULL sono generici e non appartengono a nessuna sotto-entita'.

- **INDICATORE_ESG**: nome (PK), immagine, rilevanza [0-10], tipo {ambientale, sociale, NULL}
- **INDICATORE_AMBIENTALE** (sotto-entita', tipo = 'ambientale'): codice_normativa
- **INDICATORE_SOCIALE** (sotto-entita', tipo = 'sociale'): ambito_sociale, frequenza_rilevazione

#### Entita' Principali

- **AZIENDA**: id (PK, auto-incrementante), nome, ragione_sociale (UNIQUE), partita_iva, settore, num_dipendenti, logo, nr_bilanci (ridondanza)
- **VOCE_CONTABILE**: nome (PK), descrizione
- **BILANCIO**: id (PK, auto-incrementante), data_creazione, stato {bozza, in_revisione, approvato, respinto}
- **COMPETENZA_REVISORE**: identificata dalla coppia (username revisore, nome_competenza), con attributo livello [0-5]
- **NOTA_REVISIONE**: id (PK, auto-incrementante), data_nota, testo
- **GIUDIZIO**: esito {approvazione, approvazione_con_rilievi, respingimento}, data_giudizio, rilievi

#### Relazioni

1. **GESTISCE** (RESPONSABILE -- AZIENDA): un responsabile gestisce una o piu' aziende; ogni azienda e' gestita da esattamente un responsabile. Cardinalita': (1,N) -- (1,1).

2. **APPARTIENE** (AZIENDA -- BILANCIO): un'azienda possiede zero o piu' bilanci; ogni bilancio appartiene a esattamente un'azienda. Cardinalita': (0,N) -- (1,1).

3. **VALORIZZA** (BILANCIO -- VOCE_CONTABILE): relazione molti-a-molti tra bilanci e voci contabili, con attributo *valore*. La coppia (id_bilancio, nome_voce) identifica univocamente un valore. Cardinalita': (0,N) -- (0,N).

4. **COLLEGA_INDICATORE** (VALORE_BILANCIO -- INDICATORE_ESG): relazione molti-a-molti tra valori di bilancio e indicatori ESG, con attributi *valore_indicatore*, *fonte*, *data_rilevazione*. La terna (id_bilancio, nome_voce, nome_indicatore) e' identificante. Cardinalita': (0,N) -- (0,N).

5. **REVISIONA** (REVISORE -- BILANCIO): relazione molti-a-molti che rappresenta l'assegnazione dei revisori ai bilanci. Cardinalita': (0,N) -- (0,N).

6. **ANNOTA** (REVISORE, BILANCIO -- VOCE_CONTABILE): un revisore puo' scrivere note sulle voci contabili dei bilanci a lui assegnati. Ogni nota e' associata alla coppia (revisore, bilancio) e a una voce contabile. Cardinalita': (0,N).

7. **GIUDICA** (REVISORE -- BILANCIO): un revisore emette al piu' un giudizio per ogni bilancio assegnato. La coppia (username_revisore, id_bilancio) identifica univocamente il giudizio. Cardinalita': (0,1) -- (0,N).

8. **POSSIEDE_COMPETENZA** (REVISORE -- COMPETENZA_REVISORE): un revisore dichiara zero o piu' competenze. Cardinalita': (0,N).

### 2.2 Dizionario delle Entita'

| Entita' | Descrizione | Attributi | Identificatore |
|---------|-------------|-----------|----------------|
| UTENTE | Persona registrata nel sistema | username, password_hash, codice_fiscale, data_nascita, luogo_nascita, ruolo | username |
| REVISORE | Sotto-entita' di UTENTE (ruolo='revisore') | nr_revisioni, indice_affidabilita | username (ereditato) |
| RESPONSABILE | Sotto-entita' di UTENTE (ruolo='responsabile') | curriculum_pdf | username (ereditato) |
| AZIENDA | Ente economico registrato | id, nome, ragione_sociale, partita_iva, settore, num_dipendenti, logo, nr_bilanci | id |
| VOCE_CONTABILE | Elemento del template di bilancio | nome, descrizione | nome |
| BILANCIO | Documento di bilancio aziendale | id, data_creazione, stato | id |
| INDICATORE_ESG | Metrica di sostenibilita' | nome, immagine, rilevanza, tipo | nome |
| INDICATORE_AMBIENTALE | Sotto-entita' di INDICATORE_ESG (tipo='ambientale') | codice_normativa | nome (ereditato) |
| INDICATORE_SOCIALE | Sotto-entita' di INDICATORE_ESG (tipo='sociale') | ambito_sociale, frequenza_rilevazione | nome (ereditato) |
| COMPETENZA_REVISORE | Abilita' del revisore | nome_competenza, livello | (username, nome_competenza) |
| NOTA_REVISIONE | Osservazione su una voce di bilancio | id, data_nota, testo | id |
| GIUDIZIO | Valutazione complessiva del revisore | esito, data_giudizio, rilievi | (username_revisore, id_bilancio) |

### 2.3 Dizionario delle Relazioni

| Relazione | Descrizione | Entita' coinvolte | Attributi di relazione | Cardinalita' |
|-----------|-------------|-------------------|----------------------|--------------|
| GESTISCE | Responsabile gestisce aziende | RESPONSABILE -- AZIENDA | -- | (1,N) -- (1,1) |
| APPARTIENE | Bilancio di un'azienda | AZIENDA -- BILANCIO | -- | (0,N) -- (1,1) |
| VALORIZZA | Valore di una voce in un bilancio | BILANCIO -- VOCE_CONTABILE | valore | (0,N) -- (0,N) |
| COLLEGA_INDICATORE | Indicatore ESG su valore di bilancio | VALORE_BILANCIO -- INDICATORE_ESG | valore_indicatore, fonte, data_rilevazione | (0,N) -- (0,N) |
| REVISIONA | Assegnazione revisore a bilancio | REVISORE -- BILANCIO | -- | (0,N) -- (0,N) |
| ANNOTA | Nota del revisore su voce di bilancio | (REVISORE, BILANCIO) -- VOCE_CONTABILE | data_nota, testo | (0,N) |
| GIUDICA | Giudizio del revisore su bilancio | REVISORE -- BILANCIO | esito, data_giudizio, rilievi | (0,1) -- (0,N) |
| POSSIEDE_COMPETENZA | Competenze del revisore | REVISORE -- COMPETENZA_REVISORE | -- | (0,N) |

### 2.4 Regole di Business (Vincoli Non Esprimibili nel Diagramma E-R)

| # | Regola di Business | Implementazione |
|---|-------------------|-----------------|
| RV1 | Il livello di ogni competenza del revisore deve essere compreso tra 0 e 5 (estremi inclusi). | CHECK constraint su `competenze_revisore.livello` |
| RV2 | La rilevanza di un indicatore ESG deve essere compresa tra 0.0 e 10.0 (estremi inclusi). | CHECK constraint su `indicatori_esg.rilevanza` |
| RV3 | La ragione sociale di un'azienda deve essere univoca nell'intero sistema. | UNIQUE constraint su `aziende.ragione_sociale` |
| RV4 | Quando un revisore viene assegnato a un bilancio in stato 'bozza', lo stato del bilancio deve passare automaticamente a 'in_revisione'. | Trigger T1 (`trg_bilancio_in_revisione`) su INSERT di `revisioni` |
| RV5 | Quando tutti i revisori assegnati a un bilancio hanno emesso il proprio giudizio, lo stato del bilancio deve essere aggiornato: diventa 'respinto' se almeno un giudizio ha esito 'respingimento', altrimenti diventa 'approvato'. | Trigger T2 (`trg_bilancio_giudizio`) su INSERT di `giudizi` |
| RV6 | L'attributo `nr_bilanci` dell'entita' AZIENDA deve riflettere in ogni istante il numero effettivo di bilanci associati all'azienda. | Trigger T3 (`trg_incrementa_nr_bilanci`) su INSERT di `bilanci` e Trigger T4 (`trg_decrementa_nr_bilanci`) su DELETE di `bilanci` |
| RV7 | Un indicatore ESG di tipo 'ambientale' deve avere una corrispondente riga nella tabella `indicatori_ambientali` con il codice normativa obbligatorio. Un indicatore di tipo 'sociale' deve avere una corrispondente riga in `indicatori_sociali` con ambito e frequenza obbligatori. | Logica applicativa nella SP `sp_inserisci_indicatore_esg` |
| RV8 | Al momento della registrazione, un utente deve essere inserito nella tabella padre `utenti` e nella sotto-tabella corrispondente al proprio ruolo (`revisori` o `responsabili`), unitamente al primo indirizzo email. | Logica applicativa nella SP `sp_registra_utente` |
| RV9 | Un revisore puo' emettere al piu' un giudizio per ogni bilancio a cui e' assegnato. | PRIMARY KEY composita su `giudizi(username_revisore, id_bilancio)` |
| RV10 | La modifica dei valori delle voci contabili e il collegamento degli indicatori ESG sono consentiti solo se il bilancio si trova in stato 'bozza'. | Logica applicativa (controllo PHP prima dell'esecuzione delle SP) |

---

## 3. Progettazione Logica

### 3.1 Ristrutturazione dello Schema Concettuale

La ristrutturazione dello schema E-R per la traduzione nel modello relazionale ha comportato le seguenti decisioni progettuali:

**Gerarchia UTENTE (Totale, Esclusiva):** Si e' adottata la strategia di **mantenere la tabella padre con sotto-tabelle separate** per i sotto-tipi che hanno attributi aggiuntivi. La tabella `utenti` contiene tutti gli attributi comuni e il discriminatore `ruolo`. Le tabelle `revisori` e `responsabili` contengono esclusivamente gli attributi specifici del sotto-tipo, con chiave primaria coincidente con la chiave esterna verso `utenti`. Il ruolo *amministratore* non necessita di una sotto-tabella poiche' non possiede attributi aggiuntivi.

**Gerarchia INDICATORE_ESG (Parziale, Esclusiva):** Analogamente, la tabella `indicatori_esg` contiene tutti gli attributi comuni e il discriminatore `tipo`. Le tabelle `indicatori_ambientali` e `indicatori_sociali` contengono gli attributi specifici. Gli indicatori generici (tipo NULL) esistono solo nella tabella padre.

**Attributo multivalore EMAIL:** L'attributo multivalore email dell'utente e' stato tradotto nella tabella separata `email_utente` con chiave primaria composita (username, email).

**Relazione VALORIZZA (M:N con attributo):** La relazione molti-a-molti tra BILANCIO e VOCE_CONTABILE e' diventata la tabella `valori_bilancio` con chiave primaria composita (id_bilancio, nome_voce) e attributo `valore`.

**Relazione COLLEGA_INDICATORE (ternaria):** Il collegamento tra valori di bilancio e indicatori ESG e' diventato la tabella `voci_indicatori` con chiave primaria composita (id_bilancio, nome_voce, nome_indicatore) e attributi valore_indicatore, fonte, data_rilevazione. La foreign key (id_bilancio, nome_voce) referenzia `valori_bilancio`, garantendo che l'indicatore possa essere collegato solo a voci effettivamente valorizzate.

**Relazione REVISIONA (M:N):** Divenuta la tabella `revisioni` con chiave primaria composita (username_revisore, id_bilancio).

**Entita' NOTA_REVISIONE:** Tradotta con chiave primaria surrogata (id auto-incrementante) e foreign key composita (username_revisore, id_bilancio) che referenzia `revisioni`, garantendo che solo i revisori assegnati possano inserire note.

**Entita' GIUDIZIO:** Tradotta con chiave primaria composita (username_revisore, id_bilancio) e foreign key composita verso `revisioni`, garantendo che il giudizio possa essere emesso solo da un revisore assegnato al bilancio.

**Ridondanza nr_bilanci:** Si e' deciso di mantenere l'attributo derivato `nr_bilanci` nella tabella `aziende`, aggiornato tramite i trigger T3 e T4 su INSERT e DELETE della tabella `bilanci`. La motivazione e' illustrata nell'analisi di ridondanza seguente.

### 3.2 Analisi della Ridondanza: Attributo `nr_bilanci`

L'attributo `nr_bilanci` nella tabella `aziende` e' un dato derivato: il suo valore puo' essere calcolato in qualsiasi momento eseguendo un `COUNT(*)` sulla tabella `bilanci` raggruppato per `id_azienda`. Si analizza se e' conveniente mantenere questa ridondanza.

#### Dati di volume

- Numero di aziende: **V(aziende) = 10**
- Numero medio di bilanci per azienda: **5**
- Numero totale di bilanci: **V(bilanci) = 50**

#### Coefficienti di costo

- Costo scrittura interattiva: **w_I = 1**
- Costo scrittura batch: **w_B = 0.5**
- Coefficiente costo scrittura vs lettura: **alpha = 2** (una scrittura costa il doppio di una lettura)

#### Operazioni coinvolte

| Operazione | Descrizione | Tipo | Frequenza mensile |
|-----------|-------------|------|-------------------|
| OpA | Registrazione azienda + 3 bilanci | I | 1 |
| OpB | Conteggio bilanci per tutte le aziende | B | 3 |
| OpC | Rimozione azienda con tutti i bilanci | B | 1 |

#### Costo CON ridondanza

Con la ridondanza, l'attributo `nr_bilanci` e' mantenuto aggiornato dai trigger `trg_incrementa_nr_bilanci` (su INSERT bilanci) e `trg_decrementa_nr_bilanci` (su DELETE bilanci).

**OpA** (1 volta/mese, interattiva):

- 1 scrittura su `aziende` (INSERT azienda)
- 3 scritture su `bilanci` (INSERT bilancio)
- 3 scritture aggiuntive su `aziende` per il trigger (UPDATE nr_bilanci)
- Costo: 1 x w_I x [ 1 x alpha + 3 x alpha + 3 x alpha ] = 1 x 1 x (2 + 6 + 6) = **14**

**OpB** (3 volte/mese, batch):

- Legge `nr_bilanci` direttamente dalla tabella `aziende`: 10 letture
- Costo: 3 x w_B x [ 10 x 1 ] = 3 x 0.5 x 10 = **15**

**OpC** (1 volta/mese, batch):

- 1 DELETE su `aziende` (la cascata elimina i bilanci)
- 5 trigger su DELETE bilanci (uno per ogni bilancio dell'azienda eliminata); tuttavia l'azienda stessa viene eliminata, quindi il trigger esegue un UPDATE su una riga che sta per essere eliminata. In pratica il costo aggiuntivo e' trascurabile, ma lo conteggiamo formalmente.
- Costo: 1 x w_B x [ 1 x alpha + 5 x alpha ] = 1 x 0.5 x (2 + 10) = **6**

**Costo totale CON ridondanza = 14 + 15 + 6 = 35**

#### Costo SENZA ridondanza

Senza la ridondanza, l'attributo `nr_bilanci` non esiste e ogni volta che serve il conteggio dei bilanci di un'azienda occorre calcolarlo con una query `COUNT(*) ... GROUP BY id_azienda`.

**OpA** (1 volta/mese, interattiva):

- 1 scrittura su `aziende` (INSERT azienda)
- 3 scritture su `bilanci` (INSERT bilancio)
- Nessun trigger aggiuntivo
- Costo: 1 x w_I x [ 1 x alpha + 3 x alpha ] = 1 x 1 x (2 + 6) = **8**

**OpB** (3 volte/mese, batch):

- Deve leggere tutti i 50 record della tabella `bilanci` e raggrupparli per `id_azienda` (COUNT + GROUP BY)
- Costo: 3 x w_B x [ 50 x 1 ] = 3 x 0.5 x 50 = **75**

**OpC** (1 volta/mese, batch):

- 1 DELETE su `aziende` + cascata
- Nessun trigger
- Costo: 1 x w_B x [ 1 x alpha + 5 x alpha ] = 1 x 0.5 x (2 + 10) = **6**

**Costo totale SENZA ridondanza = 8 + 75 + 6 = 89**

#### Conclusione

| Scenario | Costo OpA | Costo OpB | Costo OpC | **Totale** |
|----------|----------|----------|----------|------------|
| CON ridondanza | 14 | 15 | 6 | **35** |
| SENZA ridondanza | 8 | 75 | 6 | **89** |

Il mantenimento della ridondanza risulta **conveniente** con un risparmio del 60.7% circa. Il beneficio principale deriva dall'operazione OpB, che con la ridondanza legge soltanto 10 righe dalla tabella `aziende` invece di 50 righe dalla tabella `bilanci`. Poiche' OpB viene eseguita 3 volte al mese, il risparmio in lettura compensa ampiamente il costo aggiuntivo in scrittura dei trigger sulle operazioni OpA e OpC.

Si decide pertanto di **mantenere la ridondanza** `nr_bilanci`, aggiornata dai trigger `trg_incrementa_nr_bilanci` e `trg_decrementa_nr_bilanci`.

### 3.3 Schema Logico: Elenco delle Tabelle

Di seguito lo schema relazionale completo con vincoli di chiave primaria (PK), chiave esterna (FK), vincoli UNIQUE e vincoli CHECK.

---

**1. utenti**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| username | VARCHAR(50) | **PK** |
| password_hash | VARCHAR(255) | NOT NULL |
| codice_fiscale | CHAR(16) | NOT NULL |
| data_nascita | DATE | NOT NULL |
| luogo_nascita | VARCHAR(100) | NOT NULL |
| ruolo | ENUM('amministratore','revisore','responsabile') | NOT NULL |

---

**2. email_utente**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| username | VARCHAR(50) | **PK** (composita), FK -> utenti(username) ON DELETE CASCADE |
| email | VARCHAR(150) | **PK** (composita) |

---

**3. revisori**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| username | VARCHAR(50) | **PK**, FK -> utenti(username) ON DELETE CASCADE |
| nr_revisioni | INT | DEFAULT 0 |
| indice_affidabilita | DECIMAL(3,2) | DEFAULT 0.00 |

---

**4. responsabili**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| username | VARCHAR(50) | **PK**, FK -> utenti(username) ON DELETE CASCADE |
| curriculum_pdf | VARCHAR(255) | DEFAULT NULL |

---

**5. competenze_revisore**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| username | VARCHAR(50) | **PK** (composita), FK -> revisori(username) ON DELETE CASCADE |
| nome_competenza | VARCHAR(100) | **PK** (composita) |
| livello | TINYINT | NOT NULL, CHECK (livello BETWEEN 0 AND 5) |

---

**6. aziende**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| id | INT AUTO_INCREMENT | **PK** |
| nome | VARCHAR(150) | NOT NULL |
| ragione_sociale | VARCHAR(200) | NOT NULL, **UNIQUE** |
| partita_iva | VARCHAR(11) | NOT NULL |
| settore | VARCHAR(100) | DEFAULT NULL |
| num_dipendenti | INT | DEFAULT NULL |
| logo | VARCHAR(255) | DEFAULT NULL |
| nr_bilanci | INT | DEFAULT 0 (ridondanza) |
| username_responsabile | VARCHAR(50) | NOT NULL, FK -> responsabili(username) ON DELETE CASCADE |

---

**7. voci_contabili**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| nome | VARCHAR(150) | **PK** |
| descrizione | TEXT | DEFAULT NULL |

---

**8. bilanci**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| id | INT AUTO_INCREMENT | **PK** |
| id_azienda | INT | NOT NULL, FK -> aziende(id) ON DELETE CASCADE |
| data_creazione | DATE | NOT NULL |
| stato | ENUM('bozza','in_revisione','approvato','respinto') | DEFAULT 'bozza' |

---

**9. valori_bilancio**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| id_bilancio | INT | **PK** (composita), FK -> bilanci(id) ON DELETE CASCADE |
| nome_voce | VARCHAR(150) | **PK** (composita), FK -> voci_contabili(nome) ON UPDATE CASCADE |
| valore | DECIMAL(15,2) | NOT NULL |

---

**10. indicatori_esg**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| nome | VARCHAR(150) | **PK** |
| immagine | VARCHAR(255) | DEFAULT NULL |
| rilevanza | DECIMAL(3,1) | DEFAULT NULL, CHECK (rilevanza BETWEEN 0 AND 10) |
| tipo | ENUM('ambientale','sociale','governance') | DEFAULT NULL |

---

**11. indicatori_ambientali**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| nome | VARCHAR(150) | **PK**, FK -> indicatori_esg(nome) ON DELETE CASCADE ON UPDATE CASCADE |
| codice_normativa | VARCHAR(100) | NOT NULL |

---

**12. indicatori_sociali**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| nome | VARCHAR(150) | **PK**, FK -> indicatori_esg(nome) ON DELETE CASCADE ON UPDATE CASCADE |
| ambito_sociale | VARCHAR(150) | NOT NULL |
| frequenza_rilevazione | VARCHAR(100) | NOT NULL |

---

**13. voci_indicatori**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| id_bilancio | INT | **PK** (composita), FK composita -> valori_bilancio(id_bilancio, nome_voce) ON DELETE CASCADE |
| nome_voce | VARCHAR(150) | **PK** (composita) |
| nome_indicatore | VARCHAR(150) | **PK** (composita), FK -> indicatori_esg(nome) ON UPDATE CASCADE |
| valore_indicatore | DECIMAL(15,2) | NOT NULL |
| fonte | VARCHAR(255) | NOT NULL |
| data_rilevazione | DATE | NOT NULL |

---

**14. revisioni**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| username_revisore | VARCHAR(50) | **PK** (composita), FK -> revisori(username) ON DELETE CASCADE |
| id_bilancio | INT | **PK** (composita), FK -> bilanci(id) ON DELETE CASCADE |

---

**15. note_revisione**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| id | INT AUTO_INCREMENT | **PK** |
| username_revisore | VARCHAR(50) | NOT NULL, FK composita -> revisioni(username_revisore, id_bilancio) ON DELETE CASCADE |
| id_bilancio | INT | NOT NULL |
| nome_voce | VARCHAR(150) | NOT NULL, FK -> voci_contabili(nome) ON UPDATE CASCADE |
| data_nota | DATE | NOT NULL |
| testo | TEXT | NOT NULL |

---

**16. giudizi**

| Attributo | Tipo | Vincoli |
|-----------|------|---------|
| username_revisore | VARCHAR(50) | **PK** (composita), FK composita -> revisioni(username_revisore, id_bilancio) ON DELETE CASCADE |
| id_bilancio | INT | **PK** (composita) |
| esito | ENUM('approvazione','approvazione_con_rilievi','respingimento') | NOT NULL |
| data_giudizio | DATE | NOT NULL |
| rilievi | TEXT | DEFAULT NULL |

---

### 3.4 Vincoli di Integrita' Referenziale (Inter-relazionali)

| # | Tabella referenziante | Attributo/i FK | Tabella referenziata | Attributo/i PK | Politica ON DELETE | Politica ON UPDATE |
|---|----------------------|----------------|---------------------|----------------|-------------------|-------------------|
| 1 | email_utente | username | utenti | username | CASCADE | -- |
| 2 | revisori | username | utenti | username | CASCADE | -- |
| 3 | responsabili | username | utenti | username | CASCADE | -- |
| 4 | competenze_revisore | username | revisori | username | CASCADE | -- |
| 5 | aziende | username_responsabile | responsabili | username | CASCADE | -- |
| 6 | bilanci | id_azienda | aziende | id | CASCADE | -- |
| 7 | valori_bilancio | id_bilancio | bilanci | id | CASCADE | -- |
| 8 | valori_bilancio | nome_voce | voci_contabili | nome | -- | CASCADE |
| 9 | indicatori_ambientali | nome | indicatori_esg | nome | CASCADE | CASCADE |
| 10 | indicatori_sociali | nome | indicatori_esg | nome | CASCADE | CASCADE |
| 11 | voci_indicatori | (id_bilancio, nome_voce) | valori_bilancio | (id_bilancio, nome_voce) | CASCADE | -- |
| 12 | voci_indicatori | nome_indicatore | indicatori_esg | nome | -- | CASCADE |
| 13 | revisioni | username_revisore | revisori | username | CASCADE | -- |
| 14 | revisioni | id_bilancio | bilanci | id | CASCADE | -- |
| 15 | note_revisione | (username_revisore, id_bilancio) | revisioni | (username_revisore, id_bilancio) | CASCADE | -- |
| 16 | note_revisione | nome_voce | voci_contabili | nome | -- | CASCADE |
| 17 | giudizi | (username_revisore, id_bilancio) | revisioni | (username_revisore, id_bilancio) | CASCADE | -- |

La politica ON DELETE CASCADE e' adottata sistematicamente per garantire la consistenza dei dati: l'eliminazione di un utente provoca l'eliminazione a cascata delle sue sotto-entita', delle sue email, delle sue aziende (se responsabile), delle sue revisioni (se revisore), e cosi' via lungo tutta la catena di dipendenze. La politica ON UPDATE CASCADE e' utilizzata per le chiavi primarie di tipo testuale (nomi di voci contabili e indicatori ESG) per consentirne la rinominazione senza violare i vincoli referenziali.

---

## 4. Normalizzazione

Si verifica che tutte le tabelle dello schema relazionale siano in **Forma Normale di Boyce-Codd (BCNF)**. Una relazione e' in BCNF se per ogni dipendenza funzionale non banale X -> Y, l'insieme X e' una superchiave.

### 4.1 Analisi per Tabella

**utenti(username, password_hash, codice_fiscale, data_nascita, luogo_nascita, ruolo)**

- Chiave primaria: {username}
- Dipendenze funzionali: username -> password_hash, codice_fiscale, data_nascita, luogo_nascita, ruolo
- Verifica BCNF: l'unico determinante e' `username`, che e' la chiave primaria. **BCNF verificata.**

**email_utente(username, email)**

- Chiave primaria: {username, email}
- Dipendenze funzionali: non vi sono dipendenze funzionali non banali in cui un sottoinsieme proprio della chiave determini altri attributi (non ci sono attributi non-chiave).
- **BCNF verificata** (la relazione contiene soltanto attributi chiave).

**revisori(username, nr_revisioni, indice_affidabilita)**

- Chiave primaria: {username}
- Dipendenze funzionali: username -> nr_revisioni, indice_affidabilita
- Verifica BCNF: il determinante `username` e' la chiave. **BCNF verificata.**

**responsabili(username, curriculum_pdf)**

- Chiave primaria: {username}
- Dipendenze funzionali: username -> curriculum_pdf
- Verifica BCNF: il determinante `username` e' la chiave. **BCNF verificata.**

**competenze_revisore(username, nome_competenza, livello)**

- Chiave primaria: {username, nome_competenza}
- Dipendenze funzionali: (username, nome_competenza) -> livello
- Verifica BCNF: l'unico determinante e' la chiave primaria. **BCNF verificata.**

**aziende(id, nome, ragione_sociale, partita_iva, settore, num_dipendenti, logo, nr_bilanci, username_responsabile)**

- Chiave primaria: {id}
- Chiave candidata: {ragione_sociale} (vincolo UNIQUE)
- Dipendenze funzionali: id -> nome, ragione_sociale, partita_iva, settore, num_dipendenti, logo, nr_bilanci, username_responsabile; ragione_sociale -> id, nome, partita_iva, settore, num_dipendenti, logo, nr_bilanci, username_responsabile
- Verifica BCNF: i determinanti sono `id` e `ragione_sociale`, entrambi superchiavi. **BCNF verificata.**

**voci_contabili(nome, descrizione)**

- Chiave primaria: {nome}
- Dipendenze funzionali: nome -> descrizione
- Verifica BCNF: il determinante `nome` e' la chiave. **BCNF verificata.**

**bilanci(id, id_azienda, data_creazione, stato)**

- Chiave primaria: {id}
- Dipendenze funzionali: id -> id_azienda, data_creazione, stato
- Verifica BCNF: il determinante `id` e' la chiave. **BCNF verificata.**

**valori_bilancio(id_bilancio, nome_voce, valore)**

- Chiave primaria: {id_bilancio, nome_voce}
- Dipendenze funzionali: (id_bilancio, nome_voce) -> valore
- Verifica BCNF: l'unico determinante e' la chiave primaria. **BCNF verificata.**

**indicatori_esg(nome, immagine, rilevanza, tipo)**

- Chiave primaria: {nome}
- Dipendenze funzionali: nome -> immagine, rilevanza, tipo
- Verifica BCNF: il determinante `nome` e' la chiave. **BCNF verificata.**

**indicatori_ambientali(nome, codice_normativa)**

- Chiave primaria: {nome}
- Dipendenze funzionali: nome -> codice_normativa
- Verifica BCNF: il determinante `nome` e' la chiave. **BCNF verificata.**

**indicatori_sociali(nome, ambito_sociale, frequenza_rilevazione)**

- Chiave primaria: {nome}
- Dipendenze funzionali: nome -> ambito_sociale, frequenza_rilevazione
- Verifica BCNF: il determinante `nome` e' la chiave. **BCNF verificata.**

**voci_indicatori(id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione)**

- Chiave primaria: {id_bilancio, nome_voce, nome_indicatore}
- Dipendenze funzionali: (id_bilancio, nome_voce, nome_indicatore) -> valore_indicatore, fonte, data_rilevazione
- Verifica BCNF: l'unico determinante e' la chiave primaria. **BCNF verificata.**

**revisioni(username_revisore, id_bilancio)**

- Chiave primaria: {username_revisore, id_bilancio}
- Dipendenze funzionali: nessuna dipendenza funzionale non banale (tutti gli attributi sono chiave).
- **BCNF verificata.**

**note_revisione(id, username_revisore, id_bilancio, nome_voce, data_nota, testo)**

- Chiave primaria: {id}
- Dipendenze funzionali: id -> username_revisore, id_bilancio, nome_voce, data_nota, testo
- Verifica BCNF: il determinante `id` e' la chiave. **BCNF verificata.**

**giudizi(username_revisore, id_bilancio, esito, data_giudizio, rilievi)**

- Chiave primaria: {username_revisore, id_bilancio}
- Dipendenze funzionali: (username_revisore, id_bilancio) -> esito, data_giudizio, rilievi
- Verifica BCNF: l'unico determinante e' la chiave primaria. **BCNF verificata.**

### 4.2 Conclusione

Tutte e 16 le tabelle dello schema relazionale sono in **BCNF**. In nessuna tabella esiste una dipendenza funzionale non banale il cui determinante non sia una superchiave. Lo schema non necessita di ulteriori decomposizioni.

---

## 5. Descrizione Funzionalita' Applicazione Web

### 5.1 Architettura Tecnologica

L'applicazione web ESG-BALANCE e' realizzata con il seguente stack tecnologico:

- **Backend:** PHP 8+ con connessione a MySQL/MariaDB tramite PDO (prepared statements per prevenire SQL injection)
- **Database relazionale:** MySQL/MariaDB, con stored procedure, trigger e viste
- **Frontend:** HTML5, Bootstrap 5, CSS personalizzato, Bootstrap Icons, JavaScript
- **Server:** XAMPP (Apache + MySQL + PHP)

L'architettura dell'applicazione segue un modello **procedurale strutturato**, con separazione tra configurazione, logica di autenticazione, funzioni di utilita' e pagine. Tutte le operazioni di manipolazione dei dati avvengono esclusivamente attraverso le **stored procedure** definite nel database, garantendo che la logica di business sia centralizzata a livello DBMS. Gli eventi significativi vengono registrati nella tabella `log_eventi` per consentire la tracciabilita' delle operazioni.

### 5.2 Struttura dei File

```
ESG-BALANCE/
  config/
    database.php          -- Connessione MySQL (singleton PDO)
  includes/
    auth.php              -- Gestione sessioni, login, controllo ruoli
    db.php                -- Funzioni wrapper: callSP(), execSP(), query(), queryOne()
    functions.php         -- Funzioni utilita': logEvent(), flash messages, redirect
    header.php            -- Header HTML comune (navbar, Bootstrap)
    footer.php            -- Footer HTML comune
  pages/
    login.php             -- Pagina di accesso (autenticazione bcrypt)
    register.php          -- Registrazione con selezione ruolo e upload CV
    dashboard.php         -- Dashboard personalizzata per ruolo
    statistiche.php       -- Statistiche aggregate (4 viste SQL)
    admin/
      template.php        -- Gestione template voci contabili
      indicatori.php      -- Gestione indicatori ESG (con sotto-tipi)
      assegna_revisore.php -- Assegnazione revisori ai bilanci
    responsabile/
      aziende.php         -- Registrazione e gestione aziende
      bilancio.php        -- Creazione bilanci e inserimento valori
      indicatori_bilancio.php -- Collegamento indicatori ESG a voci
    revisore/
      competenze.php      -- Gestione competenze del revisore
      revisione.php       -- Revisione bilanci con note
      giudizio.php        -- Emissione giudizio complessivo
  assets/
    css/style.css         -- Stili personalizzati
    js/app.js             -- Script JavaScript personalizzato
    uploads/              -- Directory per upload (loghi, CV, immagini)
  sql/
    schema.sql            -- DDL dello schema completo
    stored_procedures.sql -- Tutte le 14 stored procedure
    triggers.sql          -- Tutti i 4 trigger
    views.sql             -- Tutte le 4 viste
    seed.sql              -- Dati di popolamento per la demo
  index.php               -- Landing page (redirect a dashboard se loggato)
```

### 5.3 Funzionalita' per Ruolo

#### 5.3.1 Funzionalita' comuni a tutti gli utenti

- **Registrazione** (`pages/register.php`): l'utente sceglie username, password (con conferma, minimo 6 caratteri), codice fiscale, data e luogo di nascita, ruolo (revisore o responsabile) e indirizzo email. Se il ruolo e' responsabile, puo' allegare il curriculum in PDF. La registrazione invoca la SP `sp_registra_utente`, che gestisce l'inserimento nella tabella padre, nella sotto-tabella corrispondente al ruolo e nella tabella email in un'unica operazione a livello DBMS. Le password sono hashate con bcrypt (`password_hash()` di PHP).

- **Login** (`pages/login.php`): l'utente inserisce username e password. La SP `sp_login` restituisce l'hash della password e il ruolo. Il confronto avviene in PHP tramite `password_verify()`. In caso di successo, username e ruolo sono salvati nella sessione PHP.

- **Dashboard** (`pages/dashboard.php`): pagina principale post-login, personalizzata in base al ruolo dell'utente. Tutti gli utenti possono gestire i propri indirizzi email (aggiunta tramite `sp_aggiungi_email`).

- **Statistiche** (`pages/statistiche.php`): pagina accessibile a tutti gli utenti autenticati che visualizza le quattro statistiche aggregate calcolate dalle viste SQL:
  - V1 (`v_num_aziende`): numero totale di aziende registrate
  - V2 (`v_num_revisori`): numero totale di revisori registrati
  - V3 (`v_affidabilita_aziende`): classifica delle aziende per affidabilita', calcolata come percentuale di bilanci approvati senza rilievi (approvazione pura) sul totale dei bilanci giudicati
  - V4 (`v_classifica_bilanci_esg`): classifica dei bilanci ordinati per numero di indicatori ESG collegati

#### 5.3.2 Funzionalita' dell'Amministratore

- **Gestione template bilancio** (`pages/admin/template.php`): l'amministratore definisce le voci contabili del template condiviso (es. "Ricavi vendite", "Costo del personale"). Ogni voce ha un nome univoco e una descrizione opzionale. L'inserimento avviene tramite `sp_crea_voce_contabile`.

- **Gestione indicatori ESG** (`pages/admin/indicatori.php`): l'amministratore inserisce nuovi indicatori specificando nome, rilevanza, tipo (ambientale, sociale o generico) e opzionalmente un'immagine. In base al tipo selezionato, l'interfaccia mostra dinamicamente (tramite JavaScript) i campi specifici: codice normativa per gli ambientali, ambito sociale e frequenza di rilevazione per i sociali. L'inserimento avviene tramite `sp_inserisci_indicatore_esg` che gestisce automaticamente la gerarchia.

- **Assegnazione revisori** (`pages/admin/assegna_revisore.php`): l'amministratore associa revisori ai bilanci delle aziende. L'interfaccia presenta due menu a tendina (revisore e bilancio) e una tabella riepilogativa delle assegnazioni esistenti. L'inserimento avviene tramite `sp_associa_revisore_bilancio`, che incrementa anche il contatore `nr_revisioni` del revisore. Il trigger T1 cambia automaticamente lo stato del bilancio a 'in_revisione'.

#### 5.3.3 Funzionalita' del Responsabile Aziendale

- **Gestione aziende** (`pages/responsabile/aziende.php`): il responsabile puo' registrare nuove aziende (tramite `sp_registra_azienda`) e visualizzare l'elenco delle proprie aziende con i dati anagrafici (ragione sociale, partita IVA, settore, dipendenti) e il conteggio dei bilanci (letto dall'attributo ridondante `nr_bilanci`). E' supportato l'upload del logo aziendale (JPEG, PNG, GIF, WebP).

- **Gestione bilanci** (`pages/responsabile/bilancio.php`): interfaccia a tre livelli di navigazione. Al primo livello il responsabile seleziona un'azienda. Al secondo livello visualizza la lista dei bilanci dell'azienda selezionata con i rispettivi stati (badge colorati). Al terzo livello visualizza il dettaglio di un bilancio specifico con i valori delle voci contabili gia' inserite e puo' aggiungere nuovi valori tramite `sp_inserisci_valore_bilancio` (che utilizza ON DUPLICATE KEY UPDATE per consentire anche l'aggiornamento). La creazione di un nuovo bilancio avviene tramite `sp_crea_bilancio`, che attiva il trigger T3 per l'incremento di `nr_bilanci`. L'inserimento dei valori e' consentito solo se il bilancio e' in stato 'bozza'.

- **Collegamento indicatori ESG** (`pages/responsabile/indicatori_bilancio.php`): per ogni bilancio in stato 'bozza', il responsabile puo' collegare indicatori ESG alle voci contabili gia' valorizzate. Per ogni collegamento specifica il valore dell'indicatore, la fonte del dato e la data di rilevazione. L'inserimento avviene tramite `sp_collega_indicatore_voce`.

#### 5.3.4 Funzionalita' del Revisore ESG

- **Gestione competenze** (`pages/revisore/competenze.php`): il revisore dichiara le proprie competenze professionali specificando nome e livello di padronanza (da 0 a 5). L'inserimento avviene tramite `sp_inserisci_competenza` (che usa ON DUPLICATE KEY UPDATE per aggiornare il livello se la competenza esiste gia'). Le competenze sono visualizzate con barre di progresso grafiche.

- **Revisione bilanci** (`pages/revisore/revisione.php`): il revisore visualizza i bilanci a lui assegnati e puo' esaminarne il dettaglio: voci contabili con valori, indicatori ESG collegati e note gia' inserite. Puo' aggiungere nuove note sulle singole voci tramite `sp_inserisci_nota`, specificando la voce di riferimento e il testo della nota.

- **Emissione giudizio** (`pages/revisore/giudizio.php`): al termine dell'analisi, il revisore esprime un giudizio complessivo scegliendo l'esito (approvazione, approvazione con rilievi, respingimento) e opzionalmente aggiungendo rilievi testuali. L'inserimento avviene tramite `sp_inserisci_giudizio`, che attiva il trigger T2. Il trigger verifica se tutti i revisori assegnati al bilancio hanno votato e, in caso affermativo, determina lo stato finale del bilancio. L'interfaccia mostra anche la lista dei bilanci in attesa di giudizio e impedisce l'emissione di un giudizio duplicato.

### 5.4 Logging degli Eventi

Ogni operazione significativa dell'applicazione viene registrata nella tabella MySQL `log_eventi`. Ogni record contiene:

- `evento`: tipo di evento (es. "login", "registrazione_utente", "creazione_bilancio")
- `utente`: username dell'utente che ha eseguito l'operazione
- `dettagli`: descrizione testuale dell'operazione
- `timestamp`: data e ora dell'evento

Gli eventi registrati includono: login, registrazione_utente, aggiunta_email, creazione_voce_contabile, creazione_indicatore, registrazione_azienda, creazione_bilancio, inserimento_valore_bilancio, collegamento_indicatore, assegnazione_revisore, cambio_stato_bilancio, inserimento_competenza, inserimento_nota, inserimento_giudizio.

La funzione `logEvent()` in `includes/functions.php` gestisce l'inserimento nella tabella tramite PDO, con fallback a `error_log()` nel caso in cui l'operazione di logging fallisca (per non bloccare l'applicazione).

### 5.5 Sicurezza

- **Autenticazione:** password hashate con bcrypt; verifica tramite `password_verify()`
- **Autorizzazione:** ogni pagina ristretta invoca `requireRole()` che verifica il ruolo in sessione
- **Prevenzione SQL Injection:** tutte le query utilizzano prepared statements (parametri `?`) tramite PDO
- **Prevenzione XSS:** tutti gli output utente sono sanitizzati con `htmlspecialchars()`
- **Upload file:** verifica dell'estensione del file prima di salvarlo
- **Messaggi di errore generici:** in fase di login, il messaggio "Username o password non validi" non rivela quale dei due sia errato

---

## 6. Appendice: Codice SQL Completo

Il codice SQL completo del progetto e' suddiviso nei seguenti file nella directory `sql/`:

| File | Contenuto |
|------|-----------|
| `sql/schema.sql` | Definizione dello schema (17 tabelle con vincoli PK, FK, UNIQUE, CHECK, ENUM) |
| `sql/stored_procedures.sql` | 14 stored procedure per tutte le operazioni CRUD |
| `sql/triggers.sql` | 4 trigger per la gestione automatica degli stati e della ridondanza |
| `sql/views.sql` | 4 viste per le statistiche aggregate |
| `sql/seed.sql` | Dati di popolamento per la demo (5 utenti, 3 aziende, 6 bilanci, 6 indicatori ESG, valori, revisioni, note e giudizi) |

### 6.1 Riepilogo Stored Procedure

| # | Nome | Descrizione |
|---|------|-------------|
| SP1 | `sp_login` | Restituisce username, hash password e ruolo per la verifica delle credenziali |
| SP2 | `sp_registra_utente` | Inserisce utente, email e sotto-tabella di ruolo in un'unica operazione |
| SP3 | `sp_aggiungi_email` | Aggiunge un indirizzo email a un utente esistente |
| SP4 | `sp_crea_voce_contabile` | Crea una nuova voce nel template contabile condiviso |
| SP5 | `sp_inserisci_indicatore_esg` | Inserisce un indicatore ESG gestendo la gerarchia delle sotto-tabelle |
| SP6 | `sp_registra_azienda` | Registra una nuova azienda associandola al responsabile |
| SP7 | `sp_crea_bilancio` | Crea un bilancio in stato 'bozza' e restituisce l'ID generato |
| SP8 | `sp_inserisci_valore_bilancio` | Inserisce o aggiorna il valore di una voce contabile in un bilancio |
| SP9 | `sp_collega_indicatore_voce` | Collega un indicatore ESG a una voce contabile di un bilancio |
| SP10 | `sp_associa_revisore_bilancio` | Assegna un revisore a un bilancio e incrementa il contatore revisioni |
| SP11 | `sp_inserisci_competenza` | Inserisce o aggiorna una competenza del revisore |
| SP12 | `sp_inserisci_nota` | Inserisce una nota del revisore su una voce contabile di un bilancio |
| SP13 | `sp_inserisci_giudizio` | Registra il giudizio complessivo del revisore su un bilancio |

### 6.2 Riepilogo Trigger

| # | Nome | Evento | Descrizione |
|---|------|--------|-------------|
| T1 | `trg_bilancio_in_revisione` | AFTER INSERT su `revisioni` | Cambia lo stato del bilancio da 'bozza' a 'in_revisione' quando viene assegnato il primo revisore |
| T2 | `trg_bilancio_giudizio` | AFTER INSERT su `giudizi` | Quando tutti i revisori assegnati hanno votato, determina lo stato finale: 'respinto' se almeno un respingimento, altrimenti 'approvato' |
| T3 | `trg_incrementa_nr_bilanci` | AFTER INSERT su `bilanci` | Incrementa di 1 il contatore `nr_bilanci` dell'azienda corrispondente |
| T4 | `trg_decrementa_nr_bilanci` | AFTER DELETE su `bilanci` | Decrementa di 1 il contatore `nr_bilanci` dell'azienda corrispondente |

### 6.3 Riepilogo Viste

| # | Nome | Descrizione |
|---|------|-------------|
| V1 | `v_num_aziende` | Restituisce il numero totale di aziende registrate in piattaforma |
| V2 | `v_num_revisori` | Restituisce il numero totale di revisori ESG registrati |
| V3 | `v_affidabilita_aziende` | Classifica le aziende per percentuale di bilanci approvati "puri" (tutti i giudizi con esito 'approvazione', senza rilievi ne' respingimenti) sul totale dei bilanci giudicati |
| V4 | `v_classifica_bilanci_esg` | Classifica i bilanci in ordine decrescente per numero di indicatori ESG collegati alle voci contabili |

---

*Progetto ESG-BALANCE -- Basi di Dati, A.A. 2025/2026 -- Università di Bologna*

---

## 🎨 Architettura del Sistema

### Modello a Tre Livelli

```
┌─────────────────────────────────────────────────────────┐
│                   PRESENTATION LAYER                    │
│              (HTML5, Bootstrap 5, JS)                   │
└─────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────┐
│                   APPLICATION LAYER                      │
│         (PHP 8, PDO, Session Management)                │
│    ┌──────────────────────────────────────────┐         │
│    │  • Authentication (Bcrypt)               │         │
│    │  • Authorization (Role-based)            │         │
│    │  • Business Logic (Stored Procedures)    │         │
│    │  • Event Logging (MySQL log_eventi)     │         │
│    └──────────────────────────────────────────┘         │
└─────────────────────────────────────────────────────────┘
                            │
                            ▼
┌──────────────────────┐
│    DATA LAYER        │
│  (MySQL/MariaDB)     │
│  ┌────────────────┐  │
│  │ 17 Tables      │  │
│  │ 14 Procedures  │  │
│  │ 4 Triggers     │  │
│  │ 4 Views        │  │
│  └────────────────┘  │
└──────────────────────┘
```

### Struttura Directory

```
ESG-BALANCE/
├── 📁 config/              Configurazione database
│   ├── database.php        PDO MySQL singleton
├── 📁 includes/            Core application files
│   ├── auth.php            Authentication & session
│   ├── db.php              Database wrapper functions
│   ├── functions.php       Utility functions
│   ├── header.php          Common header template
│   └── footer.php          Common footer template
├── 📁 pages/               Application pages
│   ├── login.php           Login page
│   ├── register.php        User registration
│   ├── dashboard.php       Role-based dashboard
│   ├── statistiche.php     Analytics & reports
│   ├── 📁 admin/           Administrator functions
│   │   ├── template.php    Manage account template
│   │   ├── indicatori.php  Manage ESG indicators
│   │   └── assegna_revisore.php  Assign reviewers
│   ├── 📁 responsabile/    Manager functions
│   │   ├── aziende.php     Company management
│   │   ├── bilancio.php    Balance sheet creation
│   │   └── indicatori_bilancio.php  Link ESG indicators
│   └── 📁 revisore/        Reviewer functions
│       ├── competenze.php  Skills management
│       ├── revisione.php   Review balance sheets
│       └── giudizio.php    Submit assessment
├── 📁 assets/              Static resources
│   ├── css/style.css       Custom styles
│   ├── js/app.js           JavaScript logic
│   └── uploads/            User uploads (logos, CVs)
├── 📁 sql/                 Database scripts
│   ├── schema.sql          Complete schema DDL
│   ├── stored_procedures.sql  All 14 procedures
│   ├── triggers.sql        All 4 triggers
│   ├── views.sql           All 4 views
│   └── seed.sql            Demo data
├── index.php               Landing page
├── composer.json           PHP dependencies
└── README.md               This file
```

---

## 🔐 Funzionalità per Ruolo

### 👨‍💼 Amministratore

- ✏️ Definizione template bilancio (voci contabili)
- 🌍 Gestione catalogo indicatori ESG (Environmental, Social, Governance)
- 👥 Assegnazione revisori ai bilanci
- 📊 Accesso a tutte le statistiche di sistema

### 📊 Responsabile Aziendale

- 🏢 Registrazione e gestione aziende
- 📋 Creazione bilanci di esercizio
- 💰 Compilazione valori delle voci contabili
- 🔗 Collegamento indicatori ESG alle voci di bilancio
- 📧 Gestione profilo e contatti

### 🔍 Revisore ESG

- 🎓 Dichiarazione competenze professionali
- 📝 Revisione bilanci assegnati con annotazioni
- ⚖️ Emissione giudizi di approvazione/respingimento
- 📈 Visualizzazione indice di affidabilità personale

---

## 🗄️ Database Schema Highlights

### 16 Tabelle Normalizzate (BCNF)

- **Gerarchia Utenti** (Totale, Esclusiva)
  - `utenti` → `revisori` | `responsabili`
  
- **Gerarchia Indicatori ESG** (Parziale, Esclusiva)
  - `indicatori_esg` → `indicatori_ambientali` | `indicatori_sociali`

- **Core Entities**
  - `aziende`, `bilanci`, `voci_contabili`, `valori_bilancio`
  - `voci_indicatori`, `revisioni`, `note_revisione`, `giudizi`

### 14 Stored Procedures

| Procedura | Descrizione |
|-----------|-------------|
| `sp_login` | Autenticazione utente |
| `sp_registra_utente` | Registrazione con gestione gerarchia |
| `sp_crea_bilancio` | Creazione nuovo bilancio |
| `sp_inserisci_valore_bilancio` | Insert/Update valore voce |
| `sp_collega_indicatore_voce` | Collegamento ESG a bilancio |
| `sp_associa_revisore_bilancio` | Assegnazione revisore |
| `sp_inserisci_giudizio` | Emissione giudizio finale |
| `sp_aggiorna_indice_affidabilita` | Ricalcolo indice affidabilita' revisore |
| ... | *Vedi documentazione completa* |

### 4 Trigger Automatici

| Trigger | Evento | Funzione |
|---------|--------|----------|
| `trg_bilancio_in_revisione` | INSERT revisioni | Cambio stato bilancio → "in_revisione" |
| `trg_bilancio_giudizio` | INSERT giudizi | Determinazione stato finale + aggiornamento indice affidabilita' |
| `trg_incrementa_nr_bilanci` | INSERT bilanci | Aggiornamento contatore azienda |
| `trg_decrementa_nr_bilanci` | DELETE bilanci | Aggiornamento contatore azienda |

### 4 Viste Analytics

- `v_num_aziende` - Totale aziende registrate
- `v_num_revisori` - Totale revisori certificati
- `v_affidabilita_aziende` - Classifica per % bilanci approvati
- `v_classifica_bilanci_esg` - Ranking per indicatori ESG collegati

---

## 🔒 Sicurezza

| Area | Implementazione |
|------|----------------|
| **Password** | Bcrypt hashing (cost factor 12) |
| **SQL Injection** | PDO Prepared Statements (100% coverage) |
| **XSS Prevention** | `htmlspecialchars()` su tutti gli output |
| **Session Security** | Secure session cookies, regeneration on auth |
| **File Upload** | MIME validation, size limits, whitelist extensions |
| **Authorization** | Role-based access control (RBAC) |
| **Audit Trail** | Complete event logging su tabella MySQL `log_eventi` |

---

## 📊 Analytics & Reporting

Il sistema offre quattro viste analytics principali:

1. **Volume Aziendale**: Totale aziende registrate e bilanci gestiti
2. **Risorse Umane**: Totale revisori e loro indici di affidabilità
3. **Affidabilità Aziendale**: Ranking aziende per % approvazioni pure
4. **Compliance ESG**: Classifica bilanci per completezza indicatori sostenibilità

---

## 🧪 Testing

### Dataset di Test (seed.sql)

- ✅ 5 utenti (1 admin, 2 revisori, 2 responsabili)
- ✅ 3 aziende di settori diversi
- ✅ 6 bilanci in stati diversi (bozza, in_revisione, approvato, respinto)
- ✅ 20 voci contabili standard
- ✅ 15 indicatori ESG (7 ambientali, 5 sociali, 3 generici)
- ✅ 80+ valori di bilancio
- ✅ 30+ collegamenti indicatori-voci
- ✅ 12 revisioni con note e giudizi

---

## 🚦 Roadmap & Future Enhancements

- [ ] API RESTful per integrazione con sistemi esterni
- [ ] Export bilanci in formato PDF/Excel
- [ ] Dashboard grafici interattivi (Chart.js / D3.js)
- [ ] Sistema di notifiche email
- [ ] Autenticazione a due fattori (2FA)
- [ ] Integrazione con standard GRI (Global Reporting Initiative)
- [ ] PWA (Progressive Web App) support
- [ ] Containerizzazione (Docker)

---

## 📄 Licenza

Questo progetto è stato sviluppato a scopo didattico per il corso di **Basi di Dati** presso l'Università di Bologna.

```
MIT License

Copyright (c) 2026 ESG-BALANCE Project

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

---

## 👨‍💻 Autori

Sviluppato con ❤️ per il corso di **Basi di Dati**  
**A.A. 2025/2026** - **CdS Informatica per il Management**  
**Università di Bologna**

---

## 🤝 Contributi

I contributi sono benvenuti! Per modifiche sostanziali:

1. Fai fork del repository
2. Crea un branch per la tua feature (`git checkout -b feature/AmazingFeature`)
3. Commit delle modifiche (`git commit -m 'Add some AmazingFeature'`)
4. Push al branch (`git push origin feature/AmazingFeature`)
5. Apri una Pull Request

---

## 📧 Contatti & Supporto

- 🐛 **Segnalazione Bug**: [GitHub Issues](https://github.com/tesfaye174/ESG-BALANCE/issues)
- 💡 **Feature Request**: [GitHub Discussions](https://github.com/tesfaye174/ESG-BALANCE/discussions)
- 📖 **Documentazione**: Vedi sezioni dettagliate sotto

---

<div align="center">

### ⭐ Se questo progetto ti è stato utile, lascia una stella

[![GitHub stars](https://img.shields.io/github/stars/tesfaye174/ESG-BALANCE?style=social)](https://github.com/tesfaye174/ESG-BALANCE/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/tesfaye174/ESG-BALANCE?style=social)](https://github.com/tesfaye174/ESG-BALANCE/network/members)

**Made with 💚 for Sustainability**

</div>
