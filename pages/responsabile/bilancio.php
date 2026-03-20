<?php

$page_title = 'Bilanci di Esercizio';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('responsabile');
$username = currentUser();

$id_azienda  = (int)($_GET['azienda'] ?? 0);
$id_bilancio = (int)($_GET['bilancio'] ?? 0);

$azienda = null;
if ($id_azienda > 0) {
    $azienda = queryOne(
        "SELECT * FROM aziende WHERE id = ? AND username_responsabile = ?",
        [$id_azienda, $username]
    );
    if (!$azienda) {
        setFlash('danger', 'Accesso non autorizzato.');
        header('Location: ' . BASE_URL . '/pages/responsabile/bilancio.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrf()) {
        setFlash('danger', 'Token di sicurezza non valido.');
        header("Location: bilancio.php?azienda={$id_azienda}");
        exit;
    }
    if ($_POST['action'] === 'crea_bilancio' && $azienda) {
        $anno = (int)($_POST['anno'] ?? 0);
        if ($anno < 2000 || $anno > 2099) {
            setFlash('danger', 'Anno non valido.');
        } else {
            try {
                $result = callSP('sp_crea_bilancio', [$id_azienda, $anno]);
                $new_id = $result[0]['id_bilancio'] ?? 0;
                if ($new_id > 0) {
                    logEvent('creazione_bilancio', "Bilancio #{$new_id} (anno {$anno}) creato per {$azienda['ragione_sociale']}");
                    setFlash('success', "Bilancio #{$new_id} (anno {$anno}) creato.");
                } else {
                    setFlash('danger', 'Errore nella creazione del bilancio.');
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    setFlash('danger', "Esiste gia' un bilancio per l'anno {$anno}.");
                } else {
                    error_log('ESG-BALANCE Error: ' . $e->getMessage());
                    setFlash('danger', 'Errore durante l\'operazione. Riprova o contatta l\'amministratore.');
                }
            }
        }
        header("Location: bilancio.php?azienda={$id_azienda}");
        exit;
    }

    if ($_POST['action'] === 'inserisci_valore' && $azienda) {
        $bil_id    = (int)($_POST['id_bilancio'] ?? 0);
        $nome_voce = $_POST['nome_voce'] ?? '';
        $valore    = $_POST['valore'] ?? '';

        $bil_check = queryOne(
            "SELECT id, stato FROM bilanci WHERE id = ? AND id_azienda = ?",
            [$bil_id, $id_azienda]
        );

        if (!$bil_check) {
            setFlash('danger', 'Bilancio non trovato.');
        } elseif ($bil_check['stato'] !== 'bozza') {
            setFlash('danger', 'Non puoi modificare un bilancio che non e\' piu\' in bozza.');
        } elseif ($nome_voce === '' || $valore === '') {
            setFlash('danger', 'Seleziona una voce e inserisci un valore.');
        } elseif (!is_numeric($valore)) {
            setFlash('danger', 'Il valore deve essere un numero valido.');
        } else {
            try {
                execSP('sp_inserisci_valore_bilancio', [$bil_id, $nome_voce, $valore]);
                logEvent('inserimento_valore_bilancio', "Valore inserito: {$nome_voce} = {$valore} nel bilancio #{$bil_id}");
                setFlash('success', 'Valore inserito.');
            } catch (PDOException $e) {
                error_log('ESG-BALANCE Error: ' . $e->getMessage());
                setFlash('danger', 'Errore durante l\'operazione. Riprova o contatta l\'amministratore.');
            }
        }
        header("Location: bilancio.php?azienda={$id_azienda}&bilancio={$bil_id}");
        exit;
    }
}

$aziende = query(
    "SELECT id, nome, ragione_sociale FROM aziende WHERE username_responsabile = ? ORDER BY nome",
    [$username]
);

$bilanci = [];
if ($azienda) {
    $bilanci = query(
        "SELECT * FROM bilanci WHERE id_azienda = ? ORDER BY data_creazione DESC",
        [$id_azienda]
    );
}

$valori_bilancio = [];
$bilancio_sel = null;
if ($id_bilancio > 0) {
    $bilancio_sel = queryOne("SELECT * FROM bilanci WHERE id = ? AND id_azienda = ?", [$id_bilancio, $id_azienda]);
    if ($bilancio_sel) {
        $valori_bilancio = query(
            "SELECT vb.nome_voce, vb.valore FROM valori_bilancio vb WHERE vb.id_bilancio = ? ORDER BY vb.nome_voce",
            [$id_bilancio]
        );
    }
}

$voci = query("SELECT nome FROM voci_contabili ORDER BY nome");

require_once __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4"><i class="bi bi-file-earmark-spreadsheet"></i> Bilanci di Esercizio</h2>

<?php renderFlash(); ?>

<?php if (!$azienda): ?>
    <div class="card">
        <div class="card-header bg-accent text-white">Seleziona Azienda</div>
        <div class="card-body">
            <?php if (empty($aziende)): ?>
                <p class="text-muted">Nessuna azienda registrata. <a href="aziende.php">Registrane una</a>.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($aziende as $a): ?>
                        <a href="bilancio.php?azienda=<?php echo $a['id']; ?>" class="list-group-item list-group-item-action">
                            <strong><?php echo htmlspecialchars($a['nome']); ?></strong>
                            <small class="text-muted">— <?php echo htmlspecialchars($a['ragione_sociale']); ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="bilancio.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Cambia Azienda</a>
            <span class="ms-2 fw-bold"><?php echo htmlspecialchars($azienda['nome']); ?></span>
            <small class="text-muted">(<?php echo htmlspecialchars($azienda['ragione_sociale']); ?>)</small>
        </div>
        <form method="POST" class="d-inline d-flex align-items-center gap-2">
            <?php csrfField(); ?>
            <input type="hidden" name="action" value="crea_bilancio">
            <input type="number" name="anno" class="form-control form-control-sm" style="width:100px"
                placeholder="Anno" min="2000" max="2099" value="<?php echo date('Y'); ?>" required>
            <button type="submit" class="btn btn-accent btn-sm"><i class="bi bi-plus-circle"></i> Nuovo Bilancio</button>
        </form>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-accent text-white">Bilanci</div>
                <div class="card-body p-0">
                    <?php if (empty($bilanci)): ?>
                        <p class="text-muted p-3">Nessun bilancio.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($bilanci as $b): ?>
                                <a href="bilancio.php?azienda=<?php echo $id_azienda; ?>&bilancio=<?php echo $b['id']; ?>"
                                    class="list-group-item list-group-item-action <?php echo $b['id'] == $id_bilancio ? 'active' : ''; ?>">
                                    #<?php echo $b['id']; ?> — Anno <?php echo $b['anno']; ?> (<?php echo $b['data_creazione']; ?>)
                                    <span class="badge <?php echo statoBadgeClass($b['stato']); ?> float-end"><?php echo $b['stato']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <?php if ($bilancio_sel): ?>
                <div class="card mb-3">
                    <div class="card-header bg-accent text-white">
                        Bilancio #<?php echo $bilancio_sel['id']; ?> (<?php echo $bilancio_sel['data_creazione']; ?>)
                        <span class="badge text-uppercase px-3 py-2
                            <?php echo statoBadgeClass($bilancio_sel['stato']); ?> float-end">
                            <?php echo $bilancio_sel['stato']; ?></span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($valori_bilancio)): ?>
                            <p class="text-muted">Nessuna voce compilata.</p>
                        <?php else: ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Voce Contabile</th>
                                        <th class="text-end">Valore</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($valori_bilancio as $vb): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($vb['nome_voce']); ?></td>
                                            <td class="text-end">&euro; <?php echo number_format($vb['valore'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>

                        <a href="indicatori_bilancio.php?bilancio=<?php echo $bilancio_sel['id']; ?>&azienda=<?php echo $id_azienda; ?>"
                            class="btn btn-outline-info btn-sm">
                            <i class="bi bi-graph-up"></i> Gestisci Indicatori ESG
                        </a>
                    </div>
                </div>

                <?php if ($bilancio_sel['stato'] === 'bozza'): ?>
                    <div class="card">
                        <div class="card-header bg-accent text-white">Inserisci Valore</div>
                        <div class="card-body">
                            <form method="POST">
                                <?php csrfField(); ?>
                                <input type="hidden" name="action" value="inserisci_valore">
                                <input type="hidden" name="id_bilancio" value="<?php echo $bilancio_sel['id']; ?>">
                                <div class="row">
                                    <div class="col-md-5 mb-3">
                                        <label for="nome_voce" class="form-label">Voce Contabile</label>
                                        <select class="form-select" id="nome_voce" name="nome_voce" required>
                                            <option value="">Seleziona...</option>
                                            <?php foreach ($voci as $v): ?>
                                                <option value="<?php echo htmlspecialchars($v['nome']); ?>">
                                                    <?php echo htmlspecialchars($v['nome']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="valore" class="form-label">Valore (&euro;)</label>
                                        <input type="number" class="form-control" id="valore" name="valore"
                                            step="0.01" required>
                                    </div>
                                    <div class="col-md-3 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-accent w-100">Inserisci</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-arrow-left-circle display-4"></i>
                        <p class="mt-3">Seleziona un bilancio dalla lista.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>