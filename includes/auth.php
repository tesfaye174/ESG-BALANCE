
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/ESG-BALANCE');
}

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
if (!empty($_SESSION['username'])) {
    $_SESSION['last_activity'] = time();
}

function requireLogin(): void
{
    if (empty($_SESSION['username'])) {
        header('Location: ' . BASE_URL . '/pages/login.php');
        exit;
    }
}

function requireRole(string $ruolo): void
{
    requireLogin();
    if (($_SESSION['ruolo'] ?? '') !== $ruolo) {
        $utente = $_SESSION['username'] ?? 'anonimo';
        error_log("accesso negato: utente=$utente ha tentato di accedere come $ruolo");
        // Se logEvent è disponibile (functions.php già incluso), lo usiamo
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

function verifyCsrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
