
<?php
// functions.php
require_once __DIR__ . '/../config/mongodb.php';
function logEvent(string $evento, string $dettagli, ?string $utente = null): void
{
    try {
        if (!class_exists('MongoDB\\Client')) {
            throw new \RuntimeException('MongoDB extension non installata');
        }
        $collection = getMongoCollection();
        $collection->insertOne([
            'evento'    => $evento,
            'utente'    => $utente ?? ($_SESSION['username'] ?? 'sistema'),
            'dettagli'  => $dettagli,
            'timestamp' => class_exists('MongoDB\\BSON\\UTCDateTime')
                ? new \MongoDB\BSON\UTCDateTime()
                : date('Y-m-d H:i:s'),
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
