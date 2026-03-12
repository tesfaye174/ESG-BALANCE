<?php

$page_title = 'Statistiche';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$num_aziende = queryOne("SELECT * FROM v_num_aziende");
$num_revisori = queryOne("SELECT * FROM v_num_revisori");
$affidabilita = query("SELECT * FROM v_affidabilita_aziende");
$classifica_esg = query("SELECT * FROM v_classifica_bilanci_esg");

require_once __DIR__ . '/../includes/header.php';
?>


<div class="statistiche-header d-flex align-items-center gap-3 mb-4">
    <span class="statistiche-icon"><i class="bi bi-bar-chart"></i></span>
    <h2 class="mb-0">Statistiche Piattaforma</h2>
</div>

<?php renderFlash(); ?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="statistiche-card card text-center">
            <div class="card-body py-4">
                <span class="statistiche-icon"><i class="bi bi-building"></i></span>
                <h1 class="display-4 fw-bold text-accent"><?php echo $num_aziende['totale_aziende'] ?? 0; ?></h1>
                <p class="mb-0">Aziende Registrate</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="statistiche-card card text-center">
            <div class="card-body py-4">
                <span class="statistiche-icon"><i class="bi bi-person-check"></i></span>
                <h1 class="display-4 fw-bold text-accent"><?php echo $num_revisori['totale_revisori'] ?? 0; ?></h1>
                <p class="mb-0">Revisori ESG</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="statistiche-card card text-center">
            <div class="card-body py-4">
                <span class="statistiche-icon"><i class="bi bi-trophy"></i></span>
                <?php if (!empty($affidabilita)): ?>
                <h1 class="display-4 fw-bold text-accent"><?php echo htmlspecialchars($affidabilita[0]['nome']); ?></h1>
                <p class="mb-0">Azienda più affidabile
                    (<?php echo $affidabilita[0]['percentuale_affidabilita']; ?>%)</p>
                <?php else: ?>
                <h1 class="display-4 fw-bold">—</h1>
                <p class="mb-0">Nessun dato disponibile</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="row g-4">
    <div class="col-md-6">
        <div class="statistiche-card card h-100">
            <div class="card-header">
                <i class="bi bi-trophy"></i> Classifica Affidabilita' Aziende
            </div>
            <div class="card-body">
                <?php if (empty($affidabilita)): ?>
                <p class="text-muted">Nessun dato disponibile. Servono bilanci con giudizi emessi.</p>
                <?php else: ?>
                <table class="table statistiche-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Azienda</th>
                            <th>Bilanci Giudicati</th>
                            <th>Approvati Puri</th>
                            <th>Affidabilita'</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $pos = 1;
                            foreach ($affidabilita as $a): ?>
                        <tr>
                            <td>
                                <?php if ($pos === 1): ?>
                                <i class="bi bi-trophy-fill text-accent"></i>
                                <?php else: ?>
                                <?php echo $pos; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($a['nome']); ?></strong>
                                <br><small
                                    class="text-muted"><?php echo htmlspecialchars($a['ragione_sociale']); ?></small>
                            </td>
                            <td><?php echo $a['bilanci_giudicati']; ?></td>
                            <td><?php echo $a['bilanci_approvati_puri']; ?></td>
                            <td>
                                <span class="statistiche-badge"><?php echo $a['percentuale_affidabilita']; ?>%</span>
                            </td>
                        </tr>
                        <?php $pos++;
                            endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="statistiche-card card h-100">
            <div class="card-header">
                <i class="bi bi-graph-up-arrow"></i> Classifica Bilanci per Indicatori ESG
            </div>
            <div class="card-body">
                <?php if (empty($classifica_esg)): ?>
                <p class="text-muted">Nessun bilancio presente.</p>
                <?php else: ?>
                <table class="table statistiche-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Azienda</th>
                            <th>Data</th>
                            <th>Stato</th>
                            <th>Indicatori ESG</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $pos = 1;
                            foreach ($classifica_esg as $b): ?>
                        <tr>
                            <td><?php echo $pos; ?></td>
                            <td><?php echo htmlspecialchars($b['azienda']); ?></td>
                            <td><?php echo $b['data_creazione']; ?></td>
                            <td><span class="statistiche-badge text-uppercase"><?php echo $b['stato']; ?></span></td>
                            <td>
                                <span class="statistiche-badge"><?php echo $b['num_indicatori_esg']; ?></span>
                            </td>
                        </tr>
                        <?php $pos++;
                            endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
