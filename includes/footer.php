</main>

<?php // footer con copyright e link al progetto — Bootstrap JS e app.js caricati qui per non bloccare il rendering ?>
<footer>
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-globe-americas text-accent"></i>
                <span class="fw-bold text-accent">ESG-BALANCE</span>
                <span class="text-muted">&copy; <?php echo date('Y'); ?> Tesfaye Venieri</span>
            </div>
            <div class="text-muted small">
                Progetto Basi di Dati &mdash; Unibo
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $base_url; ?>/assets/js/app.js"></script>
</body>

</html>