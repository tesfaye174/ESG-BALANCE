<?php

$page_title = 'Emetti Giudizio';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('revisore');
$username = currentUser();

$id_bilancio = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        setFlash('danger', 'Token di sicurezza non valido.');
        header('Location: giudizio.php');
        exit;
    }
    $bil_id  = (int)($_POST['id_bilancio'] ?? 0);
    $esito   = $_POST['esito'] ?? '';
    $rilievi = trim($_POST['rilievi'] ?? '');

    // controllo manuale degli esiti validi invece di fidarmi solo del SELECT HTML
    // un utente potrebbe modificare il valore con inspect element o con una richiesta diretta
    $esiti_validi = ['approvazione', 'approvazione_con_rilievi', 'respingimento'];

    if (!in_array($esito, $esiti_validi)) {
        setFlash('danger', 'Esito non valido.');
        header("Location: giudizio.php?id={$bil_id}");
        exit;
    } else {
        try {
            execSP('sp_inserisci_giudizio', [
                $username,
                $bil_id,
                $esito,
                $rilievi ?: null
            ]);
            logEvent('inserimento_giudizio', "Giudizio emesso su bilancio #{$bil_id}: {$esito}");

            // controllo se il bilancio ha cambiato stato dopo il giudizio
            // la SP aggiorna lo stato solo quando tutti i revisori hanno giudicato
            $bil_stato = queryOne("SELECT stato FROM bilanci WHERE id = ?", [$bil_id]);
            if ($bil_stato && in_array($bil_stato['stato'], ['approvato', 'respinto'])) {
                logEvent('cambio_stato_bilancio', "Bilancio #{$bil_id}: stato cambiato a '{$bil_stato['stato']}'");
            }

            redirectWith(BASE_URL . '/pages/revisore/revisione.php', 'success', 'Giudizio emesso con successo.');
        } catch (PDOException $e) {
            // 23000 = giudizio gia' emesso per questo bilancio (PK duplicata)
            if ($e->getCode() == 23000) {
                setFlash('danger', 'Hai gia\' emesso un giudizio per questo bilancio.');
            } else {
                error_log('ESG-BALANCE Error: ' . $e->getMessage());
                setFlash('danger', 'Errore durante l\'operazione. Riprova o contatta l\'amministratore.');
            }
            header("Location: giudizio.php?id={$bil_id}");
            exit;
        }
    }
}

$bilancio = null;
$giudizio_esistente = null;

if ($id_bilancio > 0) {
    $bilancio = queryOne(
        "SELECT b.*, a.nome AS azienda
         FROM bilanci b
         JOIN aziende a ON a.id = b.id_azienda
         JOIN revisioni r ON r.id_bilancio = b.id
         WHERE b.id = ? AND r.username_revisore = ?",
        [$id_bilancio, $username]
    );

    $giudizio_esistente = queryOne(
        "SELECT * FROM giudizi WHERE username_revisore = ? AND id_bilancio = ?",
        [$username, $id_bilancio]
    );
}

// LEFT JOIN con giudizi: se g.esito IS NULL il revisore non ha ancora giudicato quel bilancio
// (se avesse già giudicato la JOIN troverebbe la riga e il WHERE la escluderebbe)
$bilanci_da_giudicare = query(
    "SELECT r.id_bilancio, b.data_creazione, b.stato, a.nome AS azienda
     FROM revisioni r
     JOIN bilanci b ON b.id = r.id_bilancio
     JOIN aziende a ON a.id = b.id_azienda
     LEFT JOIN giudizi g ON g.username_revisore = r.username_revisore AND g.id_bilancio = r.id_bilancio
     WHERE r.username_revisore = ? AND g.esito IS NULL
     ORDER BY b.data_creazione DESC",
    [$username]
);

require_once __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4"><i class="bi bi-check-circle"></i> Giudizio Complessivo</h2>

<?php renderFlash(); ?>

<?php if ($bilancio && !$giudizio_esistente): ?>

    <a href="giudizio.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Torna alla lista</a>

    <div class="card">
        <div class="card-header bg-accent text-white">
            Giudizio su Bilancio #<?php echo $bilancio['id']; ?> —
            <?php echo htmlspecialchars($bilancio['azienda']); ?>
            (<?php echo $bilancio['data_creazione']; ?>)
        </div>
        <div class="card-body">
            <form method="POST">
                <?php csrfField(); ?>
                <input type="hidden" name="id_bilancio" value="<?php echo $id_bilancio; ?>">
                <div class="mb-3">
                    <label for="esito" class="form-label">Esito *</label>
                    <select class="form-select" id="esito" name="esito" required>
                        <option value="">Seleziona esito...</option>
                        <option value="approvazione">Approvazione</option>
                        <option value="approvazione_con_rilievi">Approvazione con Rilievi</option>
                        <option value="respingimento">Respingimento</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="rilievi" class="form-label">Rilievi (opzionale)</label>
                    <textarea class="form-control" id="rilievi" name="rilievi" rows="4"
                        placeholder="Inserisci eventuali rilievi o motivazioni..."></textarea>
                </div>
                <button type="submit" class="btn btn-accent">Conferma Giudizio</button>
                <a href="revisione.php?id=<?php echo $id_bilancio; ?>" class="btn btn-outline-secondary">Torna alla Revisione</a>
            </form>
        </div>
    </div>

<?php elseif ($bilancio && $giudizio_esistente): ?>

    <div class="alert alert-info">
        Hai gia' emesso un giudizio per questo bilancio:
        <strong><?php echo str_replace('_', ' ', $giudizio_esistente['esito']); ?></strong>
        (<?php echo $giudizio_esistente['data_giudizio']; ?>)
        <?php if ($giudizio_esistente['rilievi']): ?>
            <br>Rilievi: <?php echo htmlspecialchars($giudizio_esistente['rilievi']); ?>
        <?php endif; ?>
    </div>
    <a href="giudizio.php" class="btn btn-outline-secondary">Torna alla lista</a>

<?php else: ?>

    <div class="card">
        <div class="card-header bg-accent text-white">Bilanci da Giudicare</div>
        <div class="card-body">
            <?php if (empty($bilanci_da_giudicare)): ?>
                <p class="text-muted">Non ci sono bilanci in attesa di giudizio.</p>
            <?php else: ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Azienda</th>
                            <th>Data</th>
                            <th>Stato</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bilanci_da_giudicare as $b): ?>
                            <tr>
                                <td><?php echo $b['id_bilancio']; ?></td>
                                <td><?php echo htmlspecialchars($b['azienda']); ?></td>
                                <td><?php echo $b['data_creazione']; ?></td>
                                <td><span class="badge text-uppercase px-3 py-2
                                <?php echo statoBadgeClass($b['stato']); ?>">
                                        <?php echo $b['stato']; ?></span></td>
                                <td>
                                    <a href="giudizio.php?id=<?php echo $b['id_bilancio']; ?>" class="btn btn-sm btn-accent">Emetti Giudizio</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>