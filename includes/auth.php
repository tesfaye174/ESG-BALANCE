
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,   // JavaScript non può leggere il cookie (protezione XSS)
        'samesite' => 'Strict', // il cookie non viene inviato in richieste cross-site
    ]);
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/ESG-BALANCE');
}

// timeout sessione: se l'utente è inattivo per più di 1 ora lo disconnetto
$session_timeout = 3600;
if (!empty($_SESSION['username']) && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $session_timeout) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Sessione scaduta per inattivita\'. Effettua nuovamente il login.'];
        header('Location: ' . BASE_URL . '/pages/login.php');
        exit;
    }
}
// aggiorno il timestamp di ultima attività ad ogni richiesta
if (!empty($_SESSION['username'])) {
    $_SESSION['last_activity'] = time();
}

// reindirizza al login se l'utente non è autenticato
function requireLogin(): void
{
    if (empty($_SESSION['username'])) {
        header('Location: ' . BASE_URL . '/pages/login.php');
        exit;
    }
}

// controlla che l'utente abbia esattamente il ruolo richiesto
// se ha un ruolo diverso viene rimandato alla dashboard (non alla pagina richiesta)
function requireRole(string $ruolo): void
{
    requireLogin();
    if (($_SESSION['ruolo'] ?? '') !== $ruolo) {
        $utente = $_SESSION['username'] ?? 'anonimo';
        error_log("accesso negato: utente=$utente ha tentato di accedere come $ruolo");
        // loggo il tentativo nel DB solo se functions.php è già stato caricato
        if (function_exists('logEvent')) {
            logEvent('accesso_non_autorizzato', 'Tentativo di accesso a ruolo: ' . $ruolo, $utente);
        }
        header('Location: ' . BASE_URL . '/pages/dashboard.php');
        exit;
    }
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['username']);
}

function currentRole(): ?string
{
    return $_SESSION['ruolo'] ?? null;
}

function currentUser(): ?string
{
    return $_SESSION['username'] ?? null;
}

// genera il token CSRF la prima volta e poi lo riusa dalla sessione
// bin2hex(random_bytes(32)) è crittograficamente sicuro
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// stampa il campo hidden con il token da inserire nei form
function csrfField(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

// hash_equals fa un confronto a tempo costante per evitare timing attacks
function verifyCsrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
