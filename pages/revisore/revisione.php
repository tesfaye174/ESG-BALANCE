<?php
/*
 * revisione.php - Revisione dei bilanci assegnati al revisore
 * Mostra la lista dei bilanci assegnati. Se ne seleziono uno, vedo
 * le voci contabili con i valori, gli indicatori ESG collegati e le note.
 * Da qui posso aggiungere note sulle singole voci e poi andare
 * alla pagina del giudizio per esprimere il mio parere complessivo.
 */
$page_title = 'Revisione Bilancio';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('revisore');
$username = currentUser();

$id_bilancio = (int)($_GET['id'] ?? 0);

// Gestisco l'inserimento di una nuova nota su una voce
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'nota') {
    $bil_id   = (int)($_POST['id_bilancio'] ?? 0);
    $voce     = $_POST['nome_voce'] ?? '';
    $testo    = trim($_POST['testo'] ?? '');

    if ($testo === '' || $voce === '') {
        setFlash('danger', 'Compila tutti i campi della nota.');
    } else {
        try {
            execSP('sp_inserisci_nota', [$username, $bil_id, $voce, $testo]);
            logEvent('inserimento_nota', "Nota inserita su bilancio #{$bil_id}, voce: {$voce}");
            setFlash('success', 'Nota aggiunta.');
        } catch (PDOException $e) {
            setFlash('danger', 'Errore: ' . $e->getMessage());
        }
    }
    header("Location: revisione.php?id={$bil_id}");
    exit;
}

// Prendo tutti i bilanci assegnati a questo revisore
$bilanci_assegnati = query(
    "SELECT r.id_bilancio, b.data_creazione, b.stato, a.nome AS azienda
     FROM revisioni r
     JOIN bilanci b ON b.id = r.id_bilancio
     JOIN aziende a ON a.id = b.id_azienda
     WHERE r.username_revisore = ?
     ORDER BY b.data_creazione DESC",
    [$username]
);

// Se un bilancio e' selezionato, carico tutti i suoi dettagli
$bilancio_detail = null;
$valori = [];
$note = [];
$indicatori_voci = [];

if ($id_bilancio > 0) {
    // Verifico che il revisore sia effettivamente assegnato a questo bilancio
    $bilancio_detail = queryOne(
        "SELECT b.*, a.nome AS azienda, a.ragione_sociale
         FROM bilanci b
         JOIN aziende a ON a.id = b.id_azienda
         JOIN revisioni r ON r.id_bilancio = b.id
         WHERE b.id = ? AND r.username_revisore = ?",
        [$id_bilancio, $username]
    );

    // Valori delle voci contabili del bilancio
    $valori = query(
        "SELECT vb.nome_voce, vb.valore FROM valori_bilancio vb WHERE vb.id_bilancio = ? ORDER BY vb.nome_voce",
        [$id_bilancio]
    );

    // Le note che ho gia' scritto su questo bilancio
    $note = query(
        "SELECT nr.nome_voce, nr.testo, nr.data_nota
         FROM note_revisione nr
         WHERE nr.username_revisore = ? AND nr.id_bilancio = ?
         ORDER BY nr.data_nota DESC",
        [$username, $id_bilancio]
    );

    // Gli indicatori ESG collegati alle voci di questo bilancio
    $indicatori_voci = query(
        "SELECT vi.nome_voce, vi.nome_indicatore, vi.valore_indicatore, vi.fonte, vi.data_rilevazione
         FROM voci_indicatori vi WHERE vi.id_bilancio = ?
         ORDER BY vi.nome_voce, vi.nome_indicatore",
        [$id_bilancio]
    );
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4"><i class="bi bi-journal-check"></i> Revisione Bilanci</h2>

<?php renderFlash(); ?>

<?php if (!$bilancio_detail): ?>

<!-- Se non ho selezionato un bilancio, mostro la lista di quelli assegnati -->
<div class="card">
    <div class="card-header bg-accent text-white">Bilanci Assegnati</div>
    <div class="card-body">
        <?php if (empty($bilanci_assegnati)): ?>
        <p class="text-muted">Nessun bilancio assegnato.</p>
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
                <?php foreach ($bilanci_assegnati as $b): ?>
                <tr>
                    <td><?php echo $b['id_bilancio']; ?></td>
                    <td><?php echo htmlspecialchars($b['azienda']); ?></td>
                    <td><?php echo $b['data_creazione']; ?></td>
                    <td><span class="badge text-uppercase px-3 py-2
                                <?php
                                echo match ($b['stato']) {
                                    'bozza' => 'bg-secondary text-dark',
                                    'in_revisione' => 'bg-accent text-white',
                                    'approvato' => 'bg-primary text-white',
                                    'respinto' => 'bg-secondary text-dark border border-2 border-primary',
                                    default => 'bg-secondary',
                                };
                                ?>" style="font-size:0.95em;letter-spacing:0.5px;">
                            <?php echo $b['stato']; ?></span></td>
                    <td>
                        <a href="revisione.php?id=<?php echo $b['id_bilancio']; ?>"
                            class="btn btn-sm btn-outline-accent">Apri</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>

<!-- Dettaglio del bilancio selezionato con voci, indicatori e note -->
<a href="revisione.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Torna alla lista</a>

<div class="card mb-4">
    <div class="card-header bg-accent text-white">
        Bilancio #<?php echo $bilancio_detail['id']; ?> —
        <?php echo htmlspecialchars($bilancio_detail['azienda']); ?>
        (<?php echo $bilancio_detail['data_creazione']; ?>)
        <span class="badge text-uppercase px-3 py-2
                <?php
                echo match ($bilancio_detail['stato']) {
                    'bozza' => 'bg-secondary text-dark',
                    'in_revisione' => 'bg-accent text-white',
                    'approvato' => 'bg-primary text-white',
                    'respinto' => 'bg-secondary text-dark border border-2 border-primary',
                    default => 'bg-secondary',
                };
                ?>" style="font-size:0.95em;letter-spacing:0.5px; float:right;">
            <?php echo $bilancio_detail['stato']; ?></span>
    </div>
    <div class="card-body">
        <h5>Voci Contabili</h5>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Voce</th>
                    <th class="text-end">Valore</th>
                    <th>Indicatori ESG</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($valori as $v): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($v['nome_voce']); ?></strong></td>
                    <td class="text-end">&euro; <?php echo number_format($v['valore'], 2, ',', '.'); ?></td>
                    <td>
                        <?php
                                // Filtro gli indicatori per questa voce specifica
                                $ind_voce = array_filter($indicatori_voci, fn($i) => $i['nome_voce'] === $v['nome_voce']);
                                if (empty($ind_voce)): ?>
                        <span class="text-muted">—</span>
                        <?php else:
                                    foreach ($ind_voce as $iv): ?>
                        <span class="badge bg-accent"><?php echo htmlspecialchars($iv['nome_indicatore']); ?>:
                            <?php echo $iv['valore_indicatore']; ?></span>
                        <?php endforeach;
                                endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Le note che ho gia' scritto su questo bilancio -->
<?php if (!empty($note)): ?>
<div class="card mb-4">
    <div class="card-header">Le mie Note su questo bilancio</div>
    <div class="card-body">
        <?php foreach ($note as $n): ?>
        <div class="border-bottom pb-2 mb-2">
            <strong><?php echo htmlspecialchars($n['nome_voce']); ?></strong>
            <small class="text-muted">(<?php echo $n['data_nota']; ?>)</small>
            <p class="mb-0"><?php echo htmlspecialchars($n['testo']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Form per aggiungere una nuova nota su una voce -->
<div class="card">
    <div class="card-header bg-accent text-white">Aggiungi Nota</div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="nota">
            <input type="hidden" name="id_bilancio" value="<?php echo $id_bilancio; ?>">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="nome_voce" class="form-label">Voce Contabile</label>
                    <select class="form-select" id="nome_voce" name="nome_voce" required>
                        <option value="">Seleziona voce...</option>
                        <?php foreach ($valori as $v): ?>
                        <option value="<?php echo htmlspecialchars($v['nome_voce']); ?>">
                            <?php echo htmlspecialchars($v['nome_voce']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8 mb-3">
                    <label for="testo" class="form-label">Testo Nota</label>
                    <textarea class="form-control" id="testo" name="testo" rows="2" required></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-accent">Aggiungi Nota</button>
        </form>
    </div>
</div>

<!-- Bottone per andare alla pagina del giudizio complessivo -->
<div class="mt-3">
    <a href="giudizio.php?id=<?php echo $id_bilancio; ?>" class="btn btn-accent">
        <i class="bi bi-check-circle"></i> Emetti Giudizio
    </a>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>