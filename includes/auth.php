
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timeout di sessione: 1 ora di inattivita'
$session_timeout = 3600;
if (!empty($_SESSION['username']) && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $session_timeout) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Sessione scaduta per inattivita\'. Effettua nuovamente il login.'];
        header('Location: /ESG-BALANCE/pages/login.php');
        exit;
    }
}
if (!empty($_SESSION['username'])) {
    $_SESSION['last_activity'] = time();
}

function requireLogin(): void
{
    if (empty($_SESSION['username'])) {
        header('Location: /ESG-BALANCE/pages/login.php');
        exit;
    }
}

function requireRole(string $ruolo): void
{
    requireLogin();
    if (($_SESSION['ruolo'] ?? '') !== $ruolo) {
        header('Location: /ESG-BALANCE/pages/dashboard.php');
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
