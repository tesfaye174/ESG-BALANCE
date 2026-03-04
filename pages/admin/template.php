<?php
/*
 * template.php - Gestione delle voci contabili (solo admin)
 * Il template e' una lista di voci contabili condivise tra tutte le aziende
 * (tipo "Ricavi vendite", "Costo del personale", ecc.).
 * Da qui l'admin puo' aggiungere nuove voci e vedere quelle esistenti.
 */
$page_title = 'Template Bilancio';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('amministratore');

// Gestisco l'inserimento di una nuova voce contabile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descrizione = trim($_POST['descrizione'] ?? '');

    if ($nome === '') {
        setFlash('danger', 'Il nome della voce contabile e\' obbligatorio.');
    } else {
        try {
            execSP('sp_crea_voce_contabile', [$nome, $descrizione]);
            logEvent('creazione_voce_contabile', "Voce contabile creata: {$nome}");
            setFlash('success', 'Voce contabile aggiunta.');
        } catch (PDOException $e) {
            // 23000 = voce gia' esistente (PK duplicata)
            if ($e->getCode() == 23000) {
                setFlash('danger', 'Voce contabile gia\' esistente.');
            } else {
                setFlash('danger', 'Errore: ' . $e->getMessage());
            }
        }
    }
    header('Location: template.php');
    exit;
}

// Prendo tutte le voci contabili per mostrarle nella tabella
$voci = query("SELECT nome, descrizione FROM voci_contabili ORDER BY nome");

require_once __DIR__ . '/../../includes/header.php';
?>


<div class="d-flex align-items-center mb-4 gap-2">
    <i class="bi bi-file-earmark-text fs-2 text-primary"></i>
    <h2 class="mb-0 fw-bold text-primary">Template Bilancio</h2>
</div>

<?php renderFlash(); ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-primary text-white fw-bold">Nuova Voce Contabile</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome voce *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required
                            placeholder="Es. Ricavi vendite">
                    </div>
                    <div class="mb-3">
                        <label for="descrizione" class="form-label">Descrizione</label>
                        <textarea class="form-control" id="descrizione" name="descrizione" rows="3"
                            placeholder="Descrizione della voce contabile"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-accent text-white">
                Voci Contabili Esistenti
                <span class="badge bg-secondary text-accent float-end"><?php echo count($voci); ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($voci)): ?>
                <p class="text-muted">Nessuna voce contabile presente.</p>
                <?php else: ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrizione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($voci as $v): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($v['nome']); ?></strong></td>
                            <td><?php echo htmlspecialchars($v['descrizione'] ?? '—'); ?></td>
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