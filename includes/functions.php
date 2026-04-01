<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mongodb.php';

// log su MongoDB, fallback MySQL
function logEvent(string $evento, string $dettagli, ?string $utente = null): void
{
    $utente = $utente ?? ($_SESSION['username'] ?? 'sistema');

    try {
        $mongo = getMongoCollection();
        if ($mongo !== null) {
            $ts = new MongoDB\BSON\UTCDateTime();
            $mongo->insertOne([
                'evento'    => $evento,
                'utente'    => $utente,
                'dettagli'  => $dettagli,
                'timestamp' => $ts,
            ]);
            return;
        }
    } catch (Throwable $e) {
        error_log('MongoDB log fallito: ' . $e->getMessage());
    }

    // fallback mysql
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO log_eventi (evento, utente, dettagli, timestamp) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$evento, $utente, $dettagli]);
    } catch (Throwable $e) {
        error_log('Log evento fallito: ' . $e->getMessage());
    }
}

// flash messages — mostrati una volta dopo il redirect
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

// stampa il messaggio flash come alert Bootstrap
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

// flash + redirect combinati, utile dopo i POST
function redirectWith(string $url, string $type, string $message): void
{
    setFlash($type, $message);
    header('Location: ' . $url);
    exit;
}

// mappa lo stato del bilancio alla classe CSS Bootstrap del badge corrispondente
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

// upload immagine: valida estensione e MIME, nome random
function uploadImmagine(string $field, string $redirect_url): ?string
{
    if (empty($_FILES[$field]['name'])) {
        return null;
    }

    $allowed_ext  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $ext   = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES[$field]['tmp_name']);

    if (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) {
        setFlash('danger', 'Formato immagine non valido. Sono ammessi: JPEG, PNG, GIF, WebP.');
        header('Location: ' . $redirect_url);
        exit;
    }

    $upload_dir = realpath(__DIR__ . '/../assets/uploads') . DIRECTORY_SEPARATOR;
    $img_name   = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest       = $upload_dir . $img_name;

    if (strpos(realpath(dirname($dest)) . DIRECTORY_SEPARATOR, $upload_dir) !== 0) {
        setFlash('danger', 'Percorso di upload non valido.');
        header('Location: ' . $redirect_url);
        exit;
    }

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
        setFlash('danger', 'Errore durante il salvataggio del file.');
        header('Location: ' . $redirect_url);
        exit;
    }
    return 'assets/uploads/' . $img_name;
}
