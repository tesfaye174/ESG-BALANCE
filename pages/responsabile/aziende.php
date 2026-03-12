<?php

$page_title = 'Le mie Aziende';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('responsabile');
$username = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        setFlash('danger', 'Richiesta non valida.');
        header('Location: aziende.php');
        exit;
    }
    $nome       = trim($_POST['nome'] ?? '');
    $rag_soc    = trim($_POST['ragione_sociale'] ?? '');
    $piva       = trim($_POST['partita_iva'] ?? '');
    $settore    = trim($_POST['settore'] ?? '');
    $num_dip    = (int)($_POST['num_dipendenti'] ?? 0);

    $logo_path = null;
    if (!empty($_FILES['logo']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $real_mime = finfo_file($finfo, $_FILES['logo']['tmp_name']);
        finfo_close($finfo);
        if (in_array($real_mime, $allowed)) {
            $logo_name = uniqid('logo_') . '_' . basename($_FILES['logo']['name']);
            $dest = __DIR__ . '/../../assets/uploads/' . $logo_name;
            move_uploaded_file($_FILES['logo']['tmp_name'], $dest);
            $logo_path = 'assets/uploads/' . $logo_name;
        } else {
            setFlash('danger', 'Formato logo non valido. Sono ammessi: JPEG, PNG, GIF, WebP.');
            header('Location: aziende.php');
            exit;
        }
    }

    if ($nome === '' || $rag_soc === '' || $piva === '') {
        setFlash('danger', 'Nome, ragione sociale e partita IVA sono obbligatori.');
    } else {
        try {
            execSP('sp_registra_azienda', [
                $nome,
                $rag_soc,
                $piva,
                $settore ?: null,
                $num_dip ?: null,
                $logo_path,
                $username
            ]);
            logEvent('registrazione_azienda', "Nuova azienda: {$rag_soc}");
            setFlash('success', 'Azienda registrata con successo.');
        } catch (PDOException $e) {
            // 23000 = ragione sociale gia' presente (vincolo UNIQUE)
            if ($e->getCode() == 23000) {
                setFlash('danger', 'Ragione sociale gia\' presente nel sistema.');
            } else {
                setFlash('danger', 'Errore: ' . $e->getMessage());
            }
        }
    }
    header('Location: aziende.php');
    exit;
}

$aziende = query(
    "SELECT * FROM aziende WHERE username_responsabile = ? ORDER BY nome",
    [$username]
);

require_once __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4"><i class="bi bi-building"></i> Le mie Aziende</h2>

<?php renderFlash(); ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-accent text-white">Registra Nuova Azienda</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Azienda *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="ragione_sociale" class="form-label">Ragione Sociale *</label>
                        <input type="text" class="form-control" id="ragione_sociale" name="ragione_sociale" required>
                    </div>
                    <div class="mb-3">
                        <label for="partita_iva" class="form-label">Partita IVA *</label>
                        <input type="text" class="form-control" id="partita_iva" name="partita_iva" maxlength="11" required>
                    </div>
                    <div class="mb-3">
                        <label for="settore" class="form-label">Settore</label>
                        <input type="text" class="form-control" id="settore" name="settore">
                    </div>
                    <div class="mb-3">
                        <label for="num_dipendenti" class="form-label">Numero Dipendenti</label>
                        <input type="number" class="form-control" id="num_dipendenti" name="num_dipendenti" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-accent w-100">Registra Azienda</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-accent text-white">
                Aziende Registrate
                <span class="badge bg-white text-accent float-end"><?php echo count($aziende); ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($aziende)): ?>
                    <p class="text-muted">Nessuna azienda registrata.</p>
                <?php else: ?>
                    <?php foreach ($aziende as $a): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex gap-3 align-items-start">
                                        <?php if ($a['logo']): ?>
                                            <img src="/ESG-BALANCE/<?php echo htmlspecialchars($a['logo']); ?>"
                                                alt="Logo" class="rounded" style="width:48px;height:48px;object-fit:cover;">
                                        <?php else: ?>
                                            <span class="d-inline-block bg-accent rounded text-white text-center" style="width:48px;height:48px;line-height:48px;font-size:1.3rem;">
                                                <i class="bi bi-building"></i>
                                            </span>
                                        <?php endif; ?>
                                        <div>
                                            <h5 class="mb-1"><?php echo htmlspecialchars($a['nome']); ?></h5>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($a['ragione_sociale']); ?></p>
                                            <small>
                                                P.IVA: <?php echo htmlspecialchars($a['partita_iva']); ?>
                                                <?php if ($a['settore']): ?> | Settore: <?php echo htmlspecialchars($a['settore']); ?><?php endif; ?>
                                                    <?php if ($a['num_dipendenti']): ?> | Dipendenti: <?php echo $a['num_dipendenti']; ?><?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-accent fs-6"><?php echo $a['nr_bilanci']; ?> bilanci</span>
                                        <br>
                                        <a href="bilancio.php?azienda=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-accent mt-1"><i class="bi bi-journal-text me-1"></i>Gestisci Bilanci</a>
                                    </div>
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