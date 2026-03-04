<?php
// index.php
session_start();
if (!empty($_SESSION['username'])) {
    header('Location: /ESG-BALANCE/pages/dashboard.php');
    exit;
}
$page_title = 'benvenuto';
require_once __DIR__ . '/includes/header.php';
?>
<script>
    document.body.classList.add('landing-bg');
</script>

<section class="hero-section text-center py-5">
    <h1 class="display-2 fw-bold mb-3 text-primary" style="letter-spacing:1px;">ESG-BALANCE</h1>
    <span class="badge badge-animated mb-3 fs-5 px-4 py-2">Gestione integrata bilanci &amp; sostenibilità ESG</span>
    <div class="divider"></div>
    <p class="lead text-muted mb-4">
        <span class="fw-bold text-primary">La piattaforma universitaria</span> per la gestione dei bilanci aziendali<br>
        e degli indicatori ESG (Environmental, Social, Governance)
    </p>
    <p class="mb-5 text-primary fs-5">Unisci performance economica e responsabilità sociale.<br>Scopri la trasparenza,
        la sostenibilità e la digitalizzazione dei processi aziendali.</p>

    <div class="row justify-content-center g-4 mt-4">
        <div class="col-md-4 col-lg-3">
            <div class="card card-modern h-100 text-center">
                <div class="card-body py-4">
                    <i class="bi bi-file-earmark-bar-graph display-4 mb-2 icon-animated text-primary"></i>
                    <h5 class="mt-2 mb-1">Bilanci Integrati</h5>
                    <p class="text-muted small">Gestisci i bilanci di esercizio con dati finanziari e indicatori di
                        sostenibilità.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-3">
            <div class="card card-modern h-100 text-center">
                <div class="card-body py-4">
                    <i class="bi bi-globe-americas display-4 mb-2 icon-animated text-primary"></i>
                    <h5 class="mt-2 mb-1">Indicatori ESG</h5>
                    <p class="text-muted small">Monitora l'impatto ambientale e sociale collegato alle voci contabili.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-3">
            <div class="card card-modern h-100 text-center">
                <div class="card-body py-4">
                    <i class="bi bi-shield-check display-4 mb-2 icon-animated text-primary"></i>
                    <h5 class="mt-2 mb-1">Revisione ESG</h5>
                    <p class="text-muted small">Processo di verifica trasparente con note e giudizi dei revisori.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>