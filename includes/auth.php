
<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
