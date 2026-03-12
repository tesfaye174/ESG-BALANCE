<?php

$page_title = 'Registrazione';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['password_confirm'] ?? '';
    $cf         = strtoupper(trim($_POST['codice_fiscale'] ?? ''));
    $data_nasc  = $_POST['data_nascita'] ?? '';
    $luogo_nasc = trim($_POST['luogo_nascita'] ?? '');
    $ruolo      = $_POST['ruolo'] ?? '';
    $email      = trim($_POST['email'] ?? '');

    if ($username === '' || $password === '' || $cf === '' || $data_nasc === '' || $luogo_nasc === '' || $ruolo === '' || $email === '') {
        $error = 'Compila tutti i campi obbligatori.';
    } elseif ($password !== $confirm) {
        $error = 'Le password non coincidono.';
    } elseif (strlen($password) < 6) {
        $error = 'La password deve avere almeno 6 caratteri.';
    } elseif (!in_array($ruolo, ['revisore', 'responsabile'])) {
        $error = 'Ruolo non valido.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $cv_path = null;
        if ($ruolo === 'responsabile' && !empty($_FILES['curriculum']['name'])) {
            $upload_dir = __DIR__ . '/../assets/uploads/';
            $cv_name = uniqid('cv_') . '_' . basename($_FILES['curriculum']['name']);
            $cv_dest = $upload_dir . $cv_name;

            // controllo che sia un PDF
            $ext = strtolower(pathinfo($_FILES['curriculum']['name'], PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                move_uploaded_file($_FILES['curriculum']['tmp_name'], $cv_dest);
                $cv_path = 'assets/uploads/' . $cv_name;
            } else {
                $error = 'Il curriculum deve essere in formato PDF.';
            }
        }

        if ($error === '') {
            try {
                execSP('sp_registra_utente', [
                    $username,
                    $hash,
                    $cf,
                    $data_nasc,
                    $luogo_nasc,
                    $ruolo,
                    $email,
                    $cv_path
                ]);
                logEvent('registrazione_utente', "Nuovo utente registrato: {$username} ({$ruolo})");
                redirectWith(BASE_URL . '/pages/login.php', 'success', 'Registrazione completata. Effettua il login.');
            } catch (PDOException $e) {
                // Codice 23000 = violazione di unicita', quindi username gia' preso
                if ($e->getCode() == 23000) {
                    $error = 'Username gia\' in uso.';
                } else {
                    $error = 'Errore durante la registrazione: ' . $e->getMessage();
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>



<script>
    document.body.classList.add('register-bg');
</script>

<section class="d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="col-12 col-md-9 col-lg-7">
        <div class="glass-card p-0 border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <span class="register-icon">
                        <i class="bi bi-person-plus"></i>
                    </span>
                    <h3 class="card-title fw-bold mt-2 mb-0">Registrazione</h3>
                    <div class="form-text mt-2">Compila tutti i campi per creare un account</div>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-danger shadow-sm"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" autocomplete="on">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="username" name="username"
                                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus placeholder="Scegli uno username">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="esempio@email.com">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6" placeholder="Minimo 6 caratteri">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirm" class="form-label">Conferma Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required placeholder="Ripeti la password">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="codice_fiscale" class="form-label">Codice Fiscale *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                <input type="text" class="form-control" id="codice_fiscale" name="codice_fiscale"
                                    maxlength="16" value="<?php echo htmlspecialchars($_POST['codice_fiscale'] ?? ''); ?>" required placeholder="16 caratteri">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="data_nascita" class="form-label">Data di Nascita *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <input type="date" class="form-control" id="data_nascita" name="data_nascita"
                                    value="<?php echo htmlspecialchars($_POST['data_nascita'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="luogo_nascita" class="form-label">Luogo di Nascita *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" class="form-control" id="luogo_nascita" name="luogo_nascita"
                                    value="<?php echo htmlspecialchars($_POST['luogo_nascita'] ?? ''); ?>" required placeholder="Città di nascita">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ruolo" class="form-label">Ruolo *</label>
                            <select class="form-select" id="ruolo" name="ruolo" required>
                                <option value="">Seleziona...</option>
                                <option value="revisore" <?php echo ($_POST['ruolo'] ?? '') === 'revisore' ? 'selected' : ''; ?>>Revisore ESG</option>
                                <option value="responsabile" <?php echo ($_POST['ruolo'] ?? '') === 'responsabile' ? 'selected' : ''; ?>>Responsabile Aziendale</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3" id="cv_group" style="display: none;">
                        <label for="curriculum" class="form-label">Curriculum Vitae (PDF)</label>
                        <input type="file" class="form-control" id="curriculum" name="curriculum" accept=".pdf">
                    </div>
                    <button type="submit" class="btn btn-accent w-100 py-2 mt-2 shadow-sm" style="font-size:1.15em;">Registrati</button>
                </form>
                <p class="text-center mt-4 mb-0">
                    <span class="form-text">Hai già un account?</span> <a class="register-link" href="login.php">Accedi</a>
                </p>
            </div>
        </div>
    </div>
</section>
<script>
    document.getElementById('ruolo').addEventListener('change', function() {
        document.getElementById('cv_group').style.display = this.value === 'responsabile' ? 'block' : 'none';
    });
    if (document.getElementById('ruolo').value === 'responsabile') {
        document.getElementById('cv_group').style.display = 'block';
    }
    document.body.classList.add('register-bg');
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>