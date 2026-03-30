<?php

$page_title = 'Le mie Competenze';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('revisore');
$username = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        setFlash('danger', 'Token di sicurezza non valido.');
        header('Location: competenze.php');
        exit;
    }
    $nome_comp = trim($_POST['nome_competenza'] ?? '');
    $livello   = (int)($_POST['livello'] ?? -1);

    if ($nome_comp === '') {
        setFlash('danger', 'Il nome della competenza è obbligatorio.');
    } elseif ($livello < 0 || $livello > 5) {
        setFlash('danger', 'Il livello deve essere tra 0 e 5.');
    } else {
        try {
            // salvo competenza
            execSP('sp_inserisci_competenza', [$username, $nome_comp, $livello]);
            logEvent('inserimento_competenza', "Competenza aggiunta: {$nome_comp} (livello {$livello})");
            setFlash('success', 'Competenza salvata con successo.');
        } catch (PDOException $e) {
            error_log('ESG-BALANCE Error: ' . $e->getMessage());
            setFlash('danger', 'Errore nel salvataggio della competenza.');
        }
    }
    header('Location: competenze.php');
    exit;
}

// carico le competenze del revisore loggato, ordinate alfabeticamente
$competenze = query(
    "SELECT nome_competenza, livello FROM competenze_revisore WHERE username = ? ORDER BY nome_competenza",
    [$username]
);

require_once __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4"><i class="bi bi-award"></i> Le mie Competenze</h2>

<?php renderFlash(); ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-accent text-white">Aggiungi / Aggiorna Competenza</div>
            <div class="card-body">
                <form method="POST">
                    <?php csrfField(); ?>
                    <div class="mb-3">
                        <label for="nome_competenza" class="form-label">Nome Competenza *</label>
                        <input type="text" class="form-control" id="nome_competenza" name="nome_competenza"
                            required placeholder="Ad es. Valutazione del rischio">
                    </div>
                    <div class="mb-3">
                        <label for="livello" class="form-label">Livello (0-5) *</label>
                        <input type="number" class="form-control" id="livello" name="livello"
                            min="0" max="5" value="3" required>
                    </div>
                    <button type="submit" class="btn btn-accent w-100">Salva</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-accent text-white">Competenze Attuali</div>
            <div class="card-body">
                <?php if (empty($competenze)): ?>
                    <p class="text-muted">Nessuna competenza inserita.</p>
                <?php else: ?>
                    <?php foreach ($competenze as $c): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo htmlspecialchars($c['nome_competenza']); ?></strong>
                                <span class="text-muted"><?php echo $c['livello']; ?>/5</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-accent" style="width: <?php echo ($c['livello'] / 5) * 100; ?>%">
                                    Livello <?php echo $c['livello']; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
