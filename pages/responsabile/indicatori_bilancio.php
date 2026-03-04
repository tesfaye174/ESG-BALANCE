<?php
/*
 * indicatori_bilancio.php - Collegamento indicatori ESG alle voci di bilancio
 * Per ogni coppia (voce contabile, indicatore ESG) salvo il valore misurato,
 * la fonte da cui proviene il dato e la data di rilevazione.
 * Prima di tutto verifico che il bilancio appartenga al responsabile loggato.
 */
$page_title = 'Indicatori ESG Bilancio';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('responsabile');
$username = currentUser();

$id_bilancio = (int)($_GET['bilancio'] ?? 0);
$id_azienda  = (int)($_GET['azienda'] ?? 0);

// Controllo che il bilancio esista e appartenga a un'azienda di questo responsabile
$bilancio = null;
if ($id_bilancio > 0 && $id_azienda > 0) {
    $bilancio = queryOne(
        "SELECT b.*, a.nome AS azienda, a.ragione_sociale
         FROM bilanci b
         JOIN aziende a ON a.id = b.id_azienda
         WHERE b.id = ? AND a.id = ? AND a.username_responsabile = ?",
        [$id_bilancio, $id_azienda, $username]
    );
}

if (!$bilancio) {
    redirectWith('/ESG-BALANCE/pages/responsabile/bilancio.php', 'danger', 'Bilancio non trovato.');
}

// Gestisco l'inserimento di un nuovo collegamento indicatore-voce
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Non posso collegare indicatori se il bilancio non e' piu' in bozza
    if ($bilancio['stato'] !== 'bozza') {
        setFlash('danger', 'Non puoi modificare un bilancio che non e\' piu\' in bozza.');
        header("Location: indicatori_bilancio.php?bilancio={$id_bilancio}&azienda={$id_azienda}");
        exit;
    }

    $nome_voce       = $_POST['nome_voce'] ?? '';
    $nome_indicatore = $_POST['nome_indicatore'] ?? '';
    $valore_ind      = $_POST['valore_indicatore'] ?? '';
    $fonte           = trim($_POST['fonte'] ?? '');
    $data_rilev      = $_POST['data_rilevazione'] ?? '';

    if ($nome_voce === '' || $nome_indicatore === '' || $valore_ind === '' || $fonte === '' || $data_rilev === '') {
        setFlash('danger', 'Compila tutti i campi.');
    } else {
        try {
            execSP('sp_collega_indicatore_voce', [
                $id_bilancio,
                $nome_voce,
                $nome_indicatore,
                $valore_ind,
                $fonte,
                $data_rilev
            ]);
            logEvent(
                'collegamento_indicatore',
                "Indicatore '{$nome_indicatore}' collegato a voce '{$nome_voce}' del bilancio #{$id_bilancio}"
            );
            setFlash('success', 'Indicatore ESG collegato alla voce contabile.');
        } catch (PDOException $e) {
            setFlash('danger', 'Errore: ' . $e->getMessage());
        }
    }
    header("Location: indicatori_bilancio.php?bilancio={$id_bilancio}&azienda={$id_azienda}");
    exit;
}

// Voci contabili gia' valorizzate in questo bilancio (servono per il form)
$valori = query(
    "SELECT nome_voce, valore FROM valori_bilancio WHERE id_bilancio = ? ORDER BY nome_voce",
    [$id_bilancio]
);

// Indicatori gia' collegati a questo bilancio
$collegati = query(
    "SELECT vi.*, ie.tipo
     FROM voci_indicatori vi
     JOIN indicatori_esg ie ON ie.nome = vi.nome_indicatore
     WHERE vi.id_bilancio = ?
     ORDER BY vi.nome_voce, vi.nome_indicatore",
    [$id_bilancio]
);

// Tutti gli indicatori disponibili per il select del form
$indicatori = query("SELECT nome, tipo, rilevanza FROM indicatori_esg ORDER BY nome");

require_once __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4"><i class="bi bi-graph-up"></i> Indicatori ESG — Bilancio #<?php echo $id_bilancio; ?></h2>

<p class="text-muted">
    <?php echo htmlspecialchars($bilancio['azienda']); ?> — <?php echo $bilancio['data_creazione']; ?>
    <span class="badge bg-<?php
                            echo match ($bilancio['stato']) {
                                'bozza' => 'secondary',
                                'in_revisione' => 'warning',
                                'approvato' => 'success',
                                'respinto' => 'danger',
                                default => 'secondary',
                            };
                            ?>"><?php echo $bilancio['stato']; ?></span>
</p>

<a href="bilancio.php?azienda=<?php echo $id_azienda; ?>&bilancio=<?php echo $id_bilancio; ?>"
    class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left"></i> Torna al Bilancio</a>

<?php renderFlash(); ?>

<div class="row g-4">
    <!-- Tabella con gli indicatori gia' collegati -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-accent text-white">
                Indicatori Collegati
                <span class="badge bg-secondary text-accent float-end"><?php echo count($collegati); ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($collegati)): ?>
                    <p class="text-muted">Nessun indicatore collegato.</p>
                <?php else: ?>
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Voce</th>
                                <th>Indicatore</th>
                                <th>Valore</th>
                                <th>Fonte</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($collegati as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['nome_voce']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($c['nome_indicatore']); ?>
                                        <?php if ($c['tipo'] === 'ambientale'): ?>
                                            <span class="badge bg-accent">Amb</span>
                                        <?php elseif ($c['tipo'] === 'sociale'): ?>
                                            <span class="badge bg-primary">Soc</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo number_format($c['valore_indicatore'], 2, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($c['fonte']); ?></td>
                                    <td><?php echo $c['data_rilevazione']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Form di collegamento: visibile solo se il bilancio e' in bozza -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-accent text-white">Collega Indicatore ESG</div>
            <div class="card-body">
                <?php if ($bilancio['stato'] !== 'bozza'): ?>
                    <p class="text-muted">Il bilancio non e' piu' in bozza, non puoi collegare nuovi indicatori.</p>
                <?php elseif (empty($valori)): ?>
                    <p class="text-muted">Inserisci prima i valori delle voci contabili nel bilancio.</p>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nome_voce" class="form-label">Voce Contabile *</label>
                            <select class="form-select" id="nome_voce" name="nome_voce" required>
                                <option value="">Seleziona...</option>
                                <?php foreach ($valori as $v): ?>
                                    <option value="<?php echo htmlspecialchars($v['nome_voce']); ?>">
                                        <?php echo htmlspecialchars($v['nome_voce']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nome_indicatore" class="form-label">Indicatore ESG *</label>
                            <select class="form-select" id="nome_indicatore" name="nome_indicatore" required>
                                <option value="">Seleziona...</option>
                                <?php foreach ($indicatori as $ind): ?>
                                    <option value="<?php echo htmlspecialchars($ind['nome']); ?>">
                                        <?php echo htmlspecialchars($ind['nome']); ?>
                                        (<?php echo $ind['tipo'] ?? 'generico'; ?> — rilevanza: <?php echo $ind['rilevanza']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="valore_indicatore" class="form-label">Valore Indicatore *</label>
                            <input type="number" class="form-control" id="valore_indicatore" name="valore_indicatore"
                                step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="fonte" class="form-label">Fonte *</label>
                            <input type="text" class="form-control" id="fonte" name="fonte" required
                                placeholder="Es. Report interno, INAIL, Audit ambientale">
                        </div>
                        <div class="mb-3">
                            <label for="data_rilevazione" class="form-label">Data Rilevazione *</label>
                            <input type="date" class="form-control" id="data_rilevazione" name="data_rilevazione" required>
                        </div>
                        <button type="submit" class="btn btn-accent w-100">Collega</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>