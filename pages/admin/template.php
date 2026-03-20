<?php

$page_title = 'Template Bilancio';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('amministratore');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        setFlash('danger', 'Token di sicurezza non valido.');
        header('Location: template.php');
        exit;
    }
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
            // Errore 23000 = violazione della PRIMARY KEY (nome gia' presente)
            if ($e->getCode() == 23000) {
                setFlash('danger', 'Voce contabile gia\' esistente.');
            } else {
                error_log('ESG-BALANCE Error: ' . $e->getMessage());
                setFlash('danger', 'Errore durante l\'operazione. Riprova o contatta l\'amministratore.');
            }
        }
    }
    header('Location: template.php');
    exit;
}

$voci = query("SELECT nome, descrizione FROM voci_contabili ORDER BY nome");

require_once __DIR__ . '/../../includes/header.php';
?>


<div class="d-flex align-items-center mb-4 gap-2">
    <i class="bi bi-file-earmark-text fs-2 text-accent"></i>
    <h2 class="mb-0 fw-bold">Template Bilancio</h2>
</div>

<?php renderFlash(); ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-accent text-white">Nuova Voce Contabile</div>
            <div class="card-body">
                <form method="POST">
                    <?php csrfField(); ?>
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
                    <button type="submit" class="btn btn-accent w-100">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-accent text-white">
                Voci Contabili Esistenti
                <span class="badge bg-white text-accent float-end"><?php echo count($voci); ?></span>
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