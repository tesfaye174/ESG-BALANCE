<?php
// dashboard.php - Pagina dopo il login

$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$ruolo = currentRole();
$username = currentUser();

// Gestisco l'aggiunta di un nuovo indirizzo email (funzionalita' disponibile a tutti i ruoli)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'aggiungi_email') {
    $nuova_email = trim($_POST['nuova_email'] ?? '');
    if ($nuova_email === '' || !filter_var($nuova_email, FILTER_VALIDATE_EMAIL)) {
        setFlash('danger', 'Inserisci un indirizzo email valido.');
    } else {
        try {
            execSP('sp_aggiungi_email', [$username, $nuova_email]);
            logEvent('aggiunta_email', "Email aggiunta: {$nuova_email}");
            setFlash('success', 'Email aggiunta con successo.');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                setFlash('danger', 'Questo indirizzo email e\' gia\' presente.');
            } else {
                setFlash('danger', 'Errore: ' . $e->getMessage());
            }
        }
    }
    header('Location: dashboard.php');
    exit;
}

// Prendo le email dell'utente per mostrarle nella sezione profilo
$emails = query("SELECT email FROM email_utente WHERE username = ? ORDER BY email", [$username]);

require_once __DIR__ . '/../includes/header.php';
?>

<?php renderFlash(); ?>


<div class="dashboard-header d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <span class="dashboard-icon"><i class="bi bi-speedometer2"></i></span>
        <h2 class="mb-0">Dashboard</h2>
    </div>
    <span class="fw-bold" style="color:#222;">Benvenuto, <span
            style="color:#0d6efd;"><strong><?php echo htmlspecialchars($username); ?></strong></span></span>
</div>

<!-- Sezione admin: le tre card per gestire template, indicatori e revisori -->
<?php if ($ruolo === 'amministratore'): ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="dashboard-card card h-100">
                <div class="card-body text-center">
                    <span class="dashboard-icon"><i class="bi bi-file-earmark-text"></i></span>
                    <h5 class="card-title mt-3">Template Bilancio</h5>
                    <p class="card-text">Gestisci le voci contabili del template condiviso.</p>
                    <a href="admin/template.php" class="btn dashboard-btn btn-outline-primary">Gestisci</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card card h-100">
                <div class="card-body text-center">
                    <span class="dashboard-icon"><i class="bi bi-graph-up"></i></span>
                    <h5 class="card-title mt-3">Indicatori ESG</h5>
                    <p class="card-text">Popola la lista degli indicatori ESG.</p>
                    <a href="admin/indicatori.php" class="btn dashboard-btn btn-outline-accent">Gestisci</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card card h-100">
                <div class="card-body text-center">
                    <span class="dashboard-icon"><i class="bi bi-person-check"></i></span>
                    <h5 class="card-title mt-3">Assegna Revisori</h5>
                    <p class="card-text">Assegna revisori ESG ai bilanci aziendali.</p>
                    <a href="admin/assegna_revisore.php" class="btn dashboard-btn btn-outline-accent">Gestisci</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sezione revisore: i bilanci che deve revisionare e le sue competenze -->
<?php elseif ($ruolo === 'revisore'): ?>

    <?php
    // Prendo i bilanci assegnati a questo revisore con le info dell'azienda
    $bilanci_assegnati = query(
        "SELECT r.id_bilancio, b.data_creazione, b.stato, a.nome AS azienda
         FROM revisioni r
         JOIN bilanci b ON b.id = r.id_bilancio
         JOIN aziende a ON a.id = b.id_azienda
         WHERE r.username_revisore = ?
         ORDER BY b.data_creazione DESC",
        [$username]
    );
    // Prendo le competenze del revisore per mostrarle con le barre di progresso
    $competenze = query(
        "SELECT nome_competenza, livello FROM competenze_revisore WHERE username = ?",
        [$username]
    );
    ?>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="dashboard-card card h-100">
                <div class="card-header"><i class="bi bi-journal-check"></i> Bilanci Assegnati</div>
                <div class="card-body">
                    <?php if (empty($bilanci_assegnati)): ?>
                        <p class="text-muted">Nessun bilancio assegnato.</p>
                    <?php else: ?>
                        <table class="table table-sm align-middle dashboard-table">
                            <thead>
                                <tr>
                                    <th>Azienda</th>
                                    <th>Data</th>
                                    <th>Stato</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bilanci_assegnati as $b): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($b['azienda']); ?></td>
                                        <td><?php echo $b['data_creazione']; ?></td>
                                        <td><span class="dashboard-badge badge text-uppercase px-3 py-2
                                <?php
                                    echo match ($b['stato']) {
                                        'bozza' => 'bg-secondary text-dark',
                                        'in_revisione' => 'bg-primary text-white',
                                        'approvato' => 'bg-primary text-white',
                                        'respinto' => 'bg-secondary text-dark border border-2 border-primary',
                                        default => 'bg-secondary',
                                    };
                                ?>
                                ">
                                                <?php echo $b['stato']; ?></span></td>
                                        <td>
                                            <a href="revisore/revisione.php?id=<?php echo $b['id_bilancio']; ?>"
                                                class="btn btn-sm dashboard-btn btn-outline-primary">Revisiona</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-card card h-100">
                <div class="card-header"><i class="bi bi-award"></i> Le mie Competenze</div>
                <div class="card-body">
                    <?php if (empty($competenze)): ?>
                        <p class="text-muted">Nessuna competenza inserita. <a href="revisore/competenze.php">Aggiungine una</a>.
                        </p>
                    <?php else: ?>
                        <?php foreach ($competenze as $c): ?>
                            <div class="mb-2">
                                <strong><?php echo htmlspecialchars($c['nome_competenza']); ?></strong>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-primary" style="width: <?php echo ($c['livello'] / 5) * 100; ?>%">
                                        <span style="color:#fff;font-weight:600;"><?php echo $c['livello']; ?>/5</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sezione responsabile: le aziende che gestisce e link alle statistiche -->
<?php elseif ($ruolo === 'responsabile'): ?>

    <?php
    $aziende = query(
        "SELECT id, nome, ragione_sociale, nr_bilanci FROM aziende WHERE username_responsabile = ?",
        [$username]
    );
    ?>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="dashboard-card card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-building"></i> Le mie Aziende</span>
                    <a href="responsabile/aziende.php" class="btn btn-sm dashboard-btn btn-outline-accent fw-bold"><i
                            class="bi bi-plus-circle me-1"></i>Nuova Azienda</a>
                </div>
                <div class="card-body">
                    <?php if (empty($aziende)): ?>
                        <p class="text-muted">Nessuna azienda registrata.</p>
                    <?php else: ?>
                        <table class="table align-middle dashboard-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Ragione Sociale</th>
                                    <th>Bilanci</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($aziende as $a): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($a['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($a['ragione_sociale']); ?></td>
                                        <td><span class="dashboard-badge badge bg-primary text-white px-3 py-2">
                                                <?php echo $a['nr_bilanci']; ?></span></td>
                                        <td>
                                            <a href="responsabile/bilancio.php?azienda=<?php echo $a['id']; ?>"
                                                class="btn btn-sm dashboard-btn btn-outline-primary">Bilanci</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card card h-100">
                <div class="card-body text-center">
                    <span class="dashboard-icon"><i class="bi bi-bar-chart"></i></span>
                    <h5 class="mt-3">Statistiche</h5>
                    <p>Consulta le statistiche della piattaforma.</p>
                    <a href="statistiche.php" class="btn dashboard-btn btn-outline-accent">Vai</a>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<!-- Sezione email: visibile a tutti i ruoli per aggiungere indirizzi email -->
<div class="row g-4 mt-4">
    <div class="col-md-6">
        <div class="dashboard-card card">
            <div class="card-header"><i class="bi bi-envelope"></i> I miei Indirizzi Email</div>
            <div class="card-body">
                <?php if (empty($emails)): ?>
                    <p class="text-muted">Nessuna email registrata.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($emails as $e): ?>
                            <li class="list-group-item d-flex align-items-center gap-2">
                                <i class="bi bi-envelope-fill text-primary"></i>
                                <?php echo htmlspecialchars($e['email']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form method="POST" class="d-flex gap-2">
                    <input type="hidden" name="action" value="aggiungi_email">
                    <input type="email" class="form-control" name="nuova_email" placeholder="Nuovo indirizzo email" required>
                    <button type="submit" class="btn btn-accent btn-sm text-nowrap">
                        <i class="bi bi-plus-circle"></i> Aggiungi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>