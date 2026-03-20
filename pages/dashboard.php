<?php

$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$ruolo    = currentRole();
$username = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'aggiungi_email') {
    if (!verifyCsrf()) {
        setFlash('danger', 'Token di sicurezza non valido.');
        header('Location: dashboard.php');
        exit;
    }
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
                error_log('ESG-BALANCE Error: ' . $e->getMessage());
                setFlash('danger', 'Errore durante l\'operazione. Riprova o contatta l\'amministratore.');
            }
        }
    }
    header('Location: dashboard.php');
    exit;
}

$emails = query("SELECT email FROM email_utente WHERE username = ? ORDER BY email", [$username]);

// ── Query e dati per i grafici (per ruolo) ────────────────────────────────────
$bilanci_assegnati = [];
$competenze        = [];
$aziende           = [];

$_statiLbls = []; $_statiData = []; $_statiClrs = [];
$_tipiLbls  = []; $_tipiData  = []; $_tipiClrs  = [];
$_revLbls   = []; $_revData   = []; $_revPct    = [];
$_revStLbls = []; $_revStData = []; $_revStClrs = [];
$_compLbls  = []; $_compData  = [];
$_azLbls    = []; $_azData    = [];
$_respStLbls= []; $_respStData= []; $_respStClrs= [];

$_STATI_COLORS = [
    'bozza'        => '#6c757d',
    'in_revisione' => '#f59e0b',
    'approvato'    => '#10b981',
    'respinto'     => '#ef4444',
];

if ($ruolo === 'amministratore') {

    $chart_stati = query("SELECT stato, COUNT(*) AS n FROM bilanci GROUP BY stato");
    $chart_tipi  = query("SELECT COALESCE(tipo,'generico') AS tipo, COUNT(*) AS n FROM indicatori_esg GROUP BY tipo ORDER BY n DESC");
    $chart_rev   = query("SELECT username, nr_revisioni, ROUND(indice_affidabilita*100) AS pct FROM revisori ORDER BY nr_revisioni DESC");

    foreach ($chart_stati as $r) {
        $_statiLbls[] = str_replace('_', ' ', ucfirst($r['stato']));
        $_statiData[] = (int)$r['n'];
        $_statiClrs[] = $_STATI_COLORS[$r['stato']] ?? '#adb5bd';
    }

    $_tipiMap = ['ambientale' => '#10b981', 'sociale' => '#3b82f6', 'governance' => '#8b5cf6', 'generico' => '#6c757d'];
    foreach ($chart_tipi as $r) {
        $_tipiLbls[] = ucfirst($r['tipo']);
        $_tipiData[] = (int)$r['n'];
        $_tipiClrs[] = $_tipiMap[$r['tipo']] ?? '#adb5bd';
    }

    foreach ($chart_rev as $r) {
        $_revLbls[] = $r['username'];
        $_revData[] = (int)$r['nr_revisioni'];
        $_revPct[]  = (int)$r['pct'];
    }

} elseif ($ruolo === 'revisore') {

    $bilanci_assegnati = query(
        "SELECT r.id_bilancio, b.data_creazione, b.stato, a.nome AS azienda
         FROM revisioni r
         JOIN bilanci b ON b.id = r.id_bilancio
         JOIN aziende a ON a.id = b.id_azienda
         WHERE r.username_revisore = ?
         ORDER BY b.data_creazione DESC",
        [$username]
    );
    $competenze = query(
        "SELECT nome_competenza, livello FROM competenze_revisore WHERE username = ?",
        [$username]
    );

    $statiCnt = [];
    foreach ($bilanci_assegnati as $b) {
        $statiCnt[$b['stato']] = ($statiCnt[$b['stato']] ?? 0) + 1;
    }
    foreach ($statiCnt as $stato => $cnt) {
        $_revStLbls[] = str_replace('_', ' ', ucfirst($stato));
        $_revStData[] = $cnt;
        $_revStClrs[] = $_STATI_COLORS[$stato] ?? '#adb5bd';
    }

    foreach ($competenze as $c) {
        $_compLbls[] = $c['nome_competenza'];
        $_compData[] = (int)$c['livello'];
    }

} elseif ($ruolo === 'responsabile') {

    $aziende = query(
        "SELECT id, nome, ragione_sociale, nr_bilanci FROM aziende WHERE username_responsabile = ?",
        [$username]
    );
    $chart_stati_resp = query(
        "SELECT b.stato, COUNT(*) AS n FROM bilanci b
         JOIN aziende a ON a.id = b.id_azienda
         WHERE a.username_responsabile = ? GROUP BY b.stato",
        [$username]
    );

    foreach ($chart_stati_resp as $r) {
        $_respStLbls[] = str_replace('_', ' ', ucfirst($r['stato']));
        $_respStData[] = (int)$r['n'];
        $_respStClrs[] = $_STATI_COLORS[$r['stato']] ?? '#adb5bd';
    }
    foreach ($aziende as $a) {
        $_azLbls[] = $a['nome'];
        $_azData[] = (int)$a['nr_bilanci'];
    }
}
// ── Fine preparazione dati ────────────────────────────────────────────────────

require_once __DIR__ . '/../includes/header.php';
?>

<?php renderFlash(); ?>

<div class="dashboard-header d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <span class="dashboard-icon"><i class="bi bi-speedometer2"></i></span>
        <h2 class="mb-0">Dashboard</h2>
    </div>
    <span class="fw-bold">Benvenuto, <strong class="text-accent"><?php echo htmlspecialchars($username); ?></strong></span>
</div>

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

    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="dashboard-card card h-100">
                <div class="card-header"><i class="bi bi-pie-chart-fill me-2 text-accent"></i>Stato Bilanci</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="position:relative;height:250px;width:100%;">
                        <canvas id="chartStati"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card card h-100">
                <div class="card-header"><i class="bi bi-pie-chart me-2 text-accent"></i>Indicatori ESG per Tipo</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="position:relative;height:250px;width:100%;">
                        <canvas id="chartTipi"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card card h-100">
                <div class="card-header"><i class="bi bi-bar-chart-fill me-2 text-accent"></i>Revisori</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="position:relative;height:250px;width:100%;">
                        <canvas id="chartRevisori"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($ruolo === 'revisore'): ?>

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
                                        <td><span class="dashboard-badge badge text-uppercase px-3 py-2 <?php echo statoBadgeClass($b['stato']); ?>">
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
                        <p class="text-muted">Nessuna competenza inserita. <a href="revisore/competenze.php">Aggiungine una</a>.</p>
                    <?php else: ?>
                        <?php foreach ($competenze as $c): ?>
                            <div class="mb-2">
                                <strong><?php echo htmlspecialchars($c['nome_competenza']); ?></strong>
                                <div class="progress" style="height:20px;">
                                    <div class="progress-bar bg-accent" style="width:<?php echo ($c['livello'] / 5) * 100; ?>%">
                                        <?php echo $c['livello']; ?>/5
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="dashboard-card card h-100">
                <div class="card-header"><i class="bi bi-pie-chart-fill me-2 text-accent"></i>Bilanci Assegnati per Stato</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="position:relative;height:250px;width:100%;">
                        <canvas id="chartRevStati"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-card card h-100">
                <div class="card-header"><i class="bi bi-bar-chart-fill me-2 text-accent"></i>Livello Competenze</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="position:relative;height:250px;width:100%;">
                        <canvas id="chartComp"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($ruolo === 'responsabile'): ?>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="dashboard-card card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-building"></i> Le mie Aziende</span>
                    <a href="responsabile/aziende.php" class="btn btn-sm dashboard-btn btn-outline-accent fw-bold">
                        <i class="bi bi-plus-circle me-1"></i>Nuova Azienda</a>
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
                                        <td><span class="dashboard-badge badge bg-accent text-white px-3 py-2">
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

    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="dashboard-card card h-100">
                <div class="card-header"><i class="bi bi-bar-chart-fill me-2 text-accent"></i>Bilanci per Azienda</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="position:relative;height:250px;width:100%;">
                        <canvas id="chartAziende"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-card card h-100">
                <div class="card-header"><i class="bi bi-pie-chart-fill me-2 text-accent"></i>Stato dei Bilanci</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="position:relative;height:250px;width:100%;">
                        <canvas id="chartRespStati"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

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
                                <i class="bi bi-envelope-fill text-accent"></i>
                                <?php echo htmlspecialchars($e['email']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form method="POST" class="d-flex gap-2">
                    <?php csrfField(); ?>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size   = 13;

const _empty = (el, msg) => {
    const p = document.createElement('p');
    p.className = 'text-muted text-center pt-4';
    p.textContent = msg;
    el.replaceWith(p);
};

document.addEventListener('DOMContentLoaded', function () {

<?php if ($ruolo === 'amministratore'): ?>

    // Stato bilanci — donut
    (function () {
        const data = <?= json_encode($_statiData) ?>;
        const el   = document.getElementById('chartStati');
        if (!data.length) { _empty(el, 'Nessun bilancio presente.'); return; }
        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($_statiLbls) ?>,
                datasets: [{ data, backgroundColor: <?= json_encode($_statiClrs) ?>, borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { padding: 14, boxWidth: 12 } } }
            }
        });
    })();

    // Indicatori per tipo — donut
    (function () {
        const data = <?= json_encode($_tipiData) ?>;
        const el   = document.getElementById('chartTipi');
        if (!data.length) { _empty(el, 'Nessun indicatore presente.'); return; }
        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($_tipiLbls) ?>,
                datasets: [{ data, backgroundColor: <?= json_encode($_tipiClrs) ?>, borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { padding: 14, boxWidth: 12 } } }
            }
        });
    })();

    // Revisori — barre raggruppate (nr_revisioni + affidabilità%)
    (function () {
        const labels = <?= json_encode($_revLbls) ?>;
        const el     = document.getElementById('chartRevisori');
        if (!labels.length) { _empty(el, 'Nessun revisore registrato.'); return; }
        new Chart(el, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Nr. Revisioni',
                        data: <?= json_encode($_revData) ?>,
                        backgroundColor: '#2563eb',
                        borderRadius: 4,
                        barPercentage: 0.45
                    },
                    {
                        label: 'Affidabilità (%)',
                        data: <?= json_encode($_revPct) ?>,
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                        barPercentage: 0.45
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { padding: 14, boxWidth: 12 } } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.06)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    })();

<?php elseif ($ruolo === 'revisore'): ?>

    // Bilanci per stato — donut
    (function () {
        const data = <?= json_encode($_revStData) ?>;
        const el   = document.getElementById('chartRevStati');
        if (!data.length) { _empty(el, 'Nessun bilancio assegnato.'); return; }
        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($_revStLbls) ?>,
                datasets: [{ data, backgroundColor: <?= json_encode($_revStClrs) ?>, borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { padding: 14, boxWidth: 12 } } }
            }
        });
    })();

    // Competenze — barre orizzontali
    (function () {
        const labels = <?= json_encode($_compLbls) ?>;
        const el     = document.getElementById('chartComp');
        if (!labels.length) { _empty(el, 'Nessuna competenza inserita.'); return; }
        new Chart(el, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Livello',
                    data: <?= json_encode($_compData) ?>,
                    backgroundColor: '#2563eb',
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { min: 0, max: 5, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.06)' } },
                    y: { grid: { display: false } }
                }
            }
        });
    })();

<?php elseif ($ruolo === 'responsabile'): ?>

    // Bilanci per azienda — barre verticali
    (function () {
        const labels = <?= json_encode($_azLbls) ?>;
        const el     = document.getElementById('chartAziende');
        if (!labels.length) { _empty(el, 'Nessuna azienda registrata.'); return; }
        new Chart(el, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Nr. Bilanci',
                    data: <?= json_encode($_azData) ?>,
                    backgroundColor: '#2563eb',
                    borderRadius: 4,
                    barPercentage: 0.5
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.06)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    })();

    // Stato bilanci — donut
    (function () {
        const data = <?= json_encode($_respStData) ?>;
        const el   = document.getElementById('chartRespStati');
        if (!data.length) { _empty(el, 'Nessun bilancio presente.'); return; }
        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($_respStLbls) ?>,
                datasets: [{ data, backgroundColor: <?= json_encode($_respStClrs) ?>, borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { padding: 14, boxWidth: 12 } } }
            }
        });
    })();

<?php endif; ?>

});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
