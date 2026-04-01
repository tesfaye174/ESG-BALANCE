<?php

// sessione e autenticazione — incluso in ogni pagina
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Strict',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/ESG-BALANCE');
}

// scado la sessione dopo un'ora di inattività
if (!empty($_SESSION['username']) && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > 3600) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Sessione scaduta per inattività. Effettua nuovamente il login.'];
        header('Location: ' . BASE_URL . '/pages/login.php');
        exit;
    }
}

// aggiorno il timestamp di ultima attività ad ogni richiesta autenticata
if (!empty($_SESSION['username'])) {
    $_SESSION['last_activity'] = time();
}

// blocca l'accesso alle pagine protette se l'utente non è loggato
function requireLogin(): void
{
    if (empty($_SESSION['username'])) {
        header('Location: ' . BASE_URL . '/pages/login.php');
        exit;
    }
}

// come requireLogin, ma verifica anche il ruolo — se non corrisponde rimanda alla dashboard
function requireRole(string $ruolo): void
{
    requireLogin();
    if (($_SESSION['ruolo'] ?? '') !== $ruolo) {
        error_log("accesso negato: utente=" . ($_SESSION['username'] ?? 'anonimo') . " ha tentato di accedere come {$ruolo}");
        if (function_exists('logEvent')) {
            logEvent('accesso_non_autorizzato', 'Tentativo di accesso a ruolo: ' . $ruolo);
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

// genera il token CSRF e lo tiene in sessione
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

// confronto sicuro token
function verifyCsrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
