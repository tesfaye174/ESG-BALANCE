<?php

$page_title = 'Assegna Revisore';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('amministratore');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $revisore   = $_POST['revisore'] ?? '';
    $bilancio   = (int)($_POST['bilancio'] ?? 0);

    if ($revisore === '' || $bilancio === 0) {
        setFlash('danger', 'Seleziona un revisore e un bilancio.');
    } else {
        try {
            execSP('sp_associa_revisore_bilancio', [$revisore, $bilancio]);
            logEvent('assegnazione_revisore', "Revisore {$revisore} assegnato al bilancio #{$bilancio}");

            $bil_stato = queryOne("SELECT stato FROM bilanci WHERE id = ?", [$bilancio]);
            if ($bil_stato && $bil_stato['stato'] === 'in_revisione') {
                logEvent('cambio_stato_bilancio', "Bilancio #{$bilancio}: stato cambiato a 'in_revisione'");
            }

            setFlash('success', 'Revisore assegnato al bilancio.');
        } catch (PDOException $e) {
            // 23000 = questo revisore e' gia' assegnato a questo bilancio (PK duplicata)
            if ($e->getCode() == 23000) {
                setFlash('danger', 'Questo revisore e\' gia\' assegnato a questo bilancio.');
            } else {
                setFlash('danger', 'Errore: ' . $e->getMessage());
            }
        }
    }
    header('Location: assegna_revisore.php');
    exit;
}

$revisori = query("SELECT r.username, u.luogo_nascita, r.nr_revisioni, r.indice_affidabilita
                    FROM revisori r JOIN utenti u ON u.username = r.username
                    ORDER BY r.username");

$bilanci = query("SELECT b.id, b.data_creazione, b.stato, a.nome AS azienda
                   FROM bilanci b JOIN aziende a ON a.id = b.id_azienda
                   ORDER BY b.data_creazione DESC");

$assegnazioni = query(
    "SELECT r.username_revisore, r.id_bilancio, b.data_creazione, b.stato, a.nome AS azienda
     FROM revisioni r
     JOIN bilanci b ON b.id = r.id_bilancio
     JOIN aziende a ON a.id = b.id_azienda
     ORDER BY r.id_bilancio DESC"
);

require_once __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4"><i class="bi bi-person-check"></i> Assegna Revisore a Bilancio</h2>

<?php renderFlash(); ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-accent text-white">Nuova Assegnazione</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="revisore" class="form-label">Revisore ESG *</label>
                        <select class="form-select" id="revisore" name="revisore" required>
                            <option value="">Seleziona revisore...</option>
                            <?php foreach ($revisori as $r): ?>
                                <option value="<?php echo htmlspecialchars($r['username']); ?>">
                                    <?php echo htmlspecialchars($r['username']); ?>
                                    (revisioni: <?php echo $r['nr_revisioni']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bilancio" class="form-label">Bilancio *</label>
                        <select class="form-select" id="bilancio" name="bilancio" required>
                            <option value="">Seleziona bilancio...</option>
                            <?php foreach ($bilanci as $b): ?>
                                <option value="<?php echo $b['id']; ?>">
                                    #<?php echo $b['id']; ?> — <?php echo htmlspecialchars($b['azienda']); ?>
                                    (<?php echo $b['data_creazione']; ?>) [<?php echo $b['stato']; ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-accent w-100">Assegna</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-accent text-white">Assegnazioni Attuali</div>
            <div class="card-body">
                <?php if (empty($assegnazioni)): ?>
                    <p class="text-muted">Nessuna assegnazione.</p>
                <?php else: ?>
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Revisore</th>
                                <th>Bilancio</th>
                                <th>Azienda</th>
                                <th>Stato</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assegnazioni as $ass): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ass['username_revisore']); ?></td>
                                    <td>#<?php echo $ass['id_bilancio']; ?> (<?php echo $ass['data_creazione']; ?>)</td>
                                    <td><?php echo htmlspecialchars($ass['azienda']); ?></td>
                                    <td><span class="badge text-uppercase px-3 py-2
                                    <?php echo statoBadgeClass($ass['stato']); ?>">
                                            <?php echo $ass['stato']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>