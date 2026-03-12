<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_user = $_SESSION['username'] ?? null;
$current_role = $_SESSION['ruolo'] ?? null;
$base_url = defined('BASE_URL') ? BASE_URL : '/ESG-BALANCE';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESG-BALANCE<?php echo isset($page_title) ? ' — ' . htmlspecialchars($page_title) : ''; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>/assets/css/style.css" rel="stylesheet">
</head>

<body>


    <?php
    function nav_active($path)
    {
        $req = $_SERVER['REQUEST_URI'] ?? '';
        return (strpos($req, $path) !== false) ? 'active' : '';
    }
    ?>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?php echo $base_url; ?>/">
                <i class="bi bi-globe-americas fs-3"></i> <span>ESG-BALANCE</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if ($current_user): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo nav_active('/dashboard.php'); ?>"
                            href="<?php echo $base_url; ?>/pages/dashboard.php"><i
                                class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo nav_active('/statistiche.php'); ?>"
                            href="<?php echo $base_url; ?>/pages/statistiche.php"><i
                                class="bi bi-bar-chart-line me-1"></i>Statistiche</a>
                    </li>

                    <?php if ($current_role === 'amministratore'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo nav_active('/admin/'); ?>" href="#"
                            id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
                        <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/pages/admin/template.php"><i
                                        class="bi bi-file-earmark-text me-2"></i>Template Bilancio</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/pages/admin/indicatori.php"><i
                                        class="bi bi-graph-up me-2"></i>Indicatori ESG</a></li>
                            <li><a class="dropdown-item"
                                    href="<?php echo $base_url; ?>/pages/admin/assegna_revisore.php"><i
                                        class="bi bi-person-check me-2"></i>Assegna Revisore</a></li>
                        </ul>
                    </li>
                    <?php elseif ($current_role === 'revisore'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo nav_active('/revisore/'); ?>" href="#"
                            id="revDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Revisore</a>
                        <ul class="dropdown-menu" aria-labelledby="revDropdown">
                            <li><a class="dropdown-item"
                                    href="<?php echo $base_url; ?>/pages/revisore/competenze.php"><i
                                        class="bi bi-award me-2"></i>Competenze</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/pages/revisore/revisione.php"><i
                                        class="bi bi-journal-check me-2"></i>Revisioni</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/pages/revisore/giudizio.php"><i
                                        class="bi bi-clipboard-check me-2"></i>Giudizi</a></li>
                        </ul>
                    </li>
                    <?php elseif ($current_role === 'responsabile'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo nav_active('/responsabile/'); ?>" href="#"
                            id="respDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Azienda</a>
                        <ul class="dropdown-menu" aria-labelledby="respDropdown">
                            <li><a class="dropdown-item"
                                    href="<?php echo $base_url; ?>/pages/responsabile/aziende.php"><i
                                        class="bi bi-building me-2"></i>Le mie Aziende</a></li>
                            <li><a class="dropdown-item"
                                    href="<?php echo $base_url; ?>/pages/responsabile/bilancio.php"><i
                                        class="bi bi-journal-text me-2"></i>Bilanci</a></li>
                            <li><a class="dropdown-item"
                                    href="<?php echo $base_url; ?>/pages/responsabile/indicatori_bilancio.php"><i
                                        class="bi bi-graph-up-arrow me-2"></i>Indicatori Bilancio</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if ($current_user): ?>
                    <li class="nav-item d-flex align-items-center">
                        <span class="nav-link user-select-none">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($current_user); ?>
                            <span class="badge bg-accent ms-2 text-uppercase">
                                <?php echo htmlspecialchars($current_role); ?>
                            </span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="<?php echo $base_url; ?>/pages/login.php?logout=1">
                            <i class="bi bi-box-arrow-right"></i> Esci
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo nav_active('/login.php'); ?>"
                            href="<?php echo $base_url; ?>/pages/login.php"><i
                                class="bi bi-box-arrow-in-right me-1"></i>Accedi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo nav_active('/register.php'); ?>"
                            href="<?php echo $base_url; ?>/pages/register.php"><i
                                class="bi bi-person-plus me-1"></i>Registrati</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">