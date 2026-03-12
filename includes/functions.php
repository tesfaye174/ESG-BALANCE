<?php

require_once __DIR__ . '/../config/mongodb.php';

function logEvent(string $evento, string $dettagli, ?string $utente = null): void
{
    try {
        if (!class_exists('MongoDB\\Client')) {
            throw new \RuntimeException('MongoDB extension non installata');
        }
        $collection = getMongoCollection();
        $utcClass = 'MongoDB\\BSON\\UTCDateTime';
        $collection->insertOne([
            'evento'    => $evento,
            'utente'    => $utente ?? ($_SESSION['username'] ?? 'sistema'),
            'dettagli'  => $dettagli,
            'timestamp' => class_exists($utcClass) ? new $utcClass() : date('Y-m-d H:i:s'),
        ]);
    } catch (\Throwable $e) {
        error_log('[ESG-BALANCE] MongoDB log: ' . $e->getMessage());
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

// Protezione CSRF

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf(): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '');
}
