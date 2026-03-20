<?php

$page_title = 'Accedi';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// gestione logout: il pulsante logout invia un POST con il campo 'logout'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    if (verifyCsrf()) {
        session_unset();
        session_destroy();
    }
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}

// se già loggato non ha senso stare qui
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!verifyCsrf()) {
        $error = 'Token di sicurezza non valido. Riprova.';
    } elseif ($username === '' || $password === '') {
        $error = 'Compila tutti i campi.';
    } else {
        // sp_login restituisce i dati dell'utente se esiste (senza controllare la password)
        $rows = callSP('sp_login', [$username]);

        if (empty($rows)) {
            // messaggio generico intenzionale: non diciamo se è sbagliato username o password
            // altrimenti un attaccante può capire quali username esistono (user enumeration)
            $error = 'Username o password non validi.';
        } else {
            $user = $rows[0];

            if (password_verify($password, $user['password_hash'])) {
                // rigenerare il session ID dopo il login previene il session fixation attack
                session_regenerate_id(true);
                $_SESSION['username'] = $user['username'];
                $_SESSION['ruolo']    = $user['ruolo'];
                $_SESSION['last_activity'] = time();
                logEvent('login', "Accesso utente: {$user['username']}");
                header('Location: ' . BASE_URL . '/pages/dashboard.php');
                exit;
            } else {
                $error = 'Username o password non validi.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<script>
    document.body.classList.add('login-bg');
</script>

<section class="d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="glass-card p-0 border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <span class="login-icon">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </span>
                    <h3 class="card-title fw-bold mt-2 mb-0 text-accent">Accedi</h3>
                    <div class="form-text mt-2">Inserisci le tue credenziali per accedere alla piattaforma</div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger shadow-sm"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php renderFlash(); ?>

                <form method="POST" autocomplete="on">
                    <?php csrfField(); ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                required autofocus placeholder="Inserisci username">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password"
                                required placeholder="Inserisci password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-accent w-100 py-2 mt-2 shadow-sm"
                        style="font-size:1.15em;">Accedi</button>
                </form>

                <p class="text-center mt-4 mb-0">
                    <span class="form-text">Non hai un account?</span>
                    <a class="login-link" href="register.php">Registrati</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>