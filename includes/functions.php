<?php

require_once __DIR__ . '/../config/database.php';

// salva un evento nel log (tabella log_eventi)
function logEvent(string $evento, string $dettagli, ?string $utente = null): void
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO log_eventi (evento, utente, dettagli, timestamp) VALUES (?, ?, ?, NOW())");
        $stmt->execute([
            $evento,
            $utente ?? ($_SESSION['username'] ?? 'sistema'),
            $dettagli,
        ]);
    } catch (\Throwable $e) {
        // se il log fallisce non blocco l'applicazione
        error_log('[ESG-BALANCE] Log evento fallito: ' . $e->getMessage());
    }
}
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash(): void
{
    $flash = getFlash();
    if ($flash) {
        $type = htmlspecialchars($flash['type']);
        $msg  = htmlspecialchars($flash['message']);
        echo "<div class=\"alert alert-{$type} alert-dismissible fade show\" role=\"alert\">";
        echo $msg;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}

function redirectWith(string $url, string $type, string $message): void
{
    setFlash($type, $message);
    header('Location: ' . $url);
    exit;
}

// Badge stato bilancio (usato in piu' pagine)
function statoBadgeClass(string $stato): string
{
    return match ($stato) {
        'bozza' => 'bg-secondary text-dark',
        'in_revisione' => 'bg-accent text-white',
        'approvato' => 'bg-primary text-white',
        'respinto' => 'bg-secondary text-dark border border-2 border-primary',
        default => 'bg-secondary',
    };
}
