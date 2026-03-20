<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongodb.php';

// logEvent: salva un evento nel log. Prova prima MongoDB, poi cade su MySQL.
// In questo modo il progetto dimostra l'uso di due DBMS diversi come richiesto.
function logEvent(string $evento, string $dettagli, ?string $utente = null): void
{
    // se non viene passato l'utente, lo prendo dalla sessione
    $utente = $utente ?? ($_SESSION['username'] ?? 'sistema');

    try {
        $mongo = getMongoCollection();
        if ($mongo !== null) {
            // UTCDateTime è il tipo data nativo di MongoDB
            $ts = class_exists('MongoDB\BSON\UTCDateTime') ? new MongoDB\BSON\UTCDateTime() : time();
            $mongo->insertOne([
                'evento'    => $evento,
                'utente'    => $utente,
                'dettagli'  => $dettagli,
                'timestamp' => $ts,
            ]);
            return; // se MongoDB ha funzionato non scrivo su MySQL
        }
    } catch (Throwable $e) {
        // MongoDB disponibile ma scrittura fallita (es. permessi, rete)
        error_log('MongoDB log fallito: ' . $e->getMessage());
    }

    // fallback su MySQL: stessa struttura, usa la tabella log_eventi
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO log_eventi (evento, utente, dettagli, timestamp) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$evento, $utente, $dettagli]);
    } catch (Throwable $e) {
        error_log('Log evento fallito: ' . $e->getMessage());
    }
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// legge il flash message e lo cancella dalla sessione: così appare solo una volta
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
        $allowed_types = ['success', 'danger', 'warning', 'info'];
        $type = in_array($flash['type'], $allowed_types, true) ? $flash['type'] : 'info';
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

// restituisce la classe CSS del badge Bootstrap in base allo stato del bilancio
function statoBadgeClass(string $stato): string
{
    return match ($stato) {
        'bozza'        => 'bg-secondary text-white',
        'in_revisione' => 'bg-accent text-white',
        'approvato'    => 'bg-success text-white',
        'respinto'     => 'bg-danger text-white',
        default        => 'bg-secondary text-white',
    };
}
