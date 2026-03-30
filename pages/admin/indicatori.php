<?php

$page_title = 'Indicatori ESG';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('amministratore');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        setFlash('danger', 'Token di sicurezza non valido.');
        header('Location: indicatori.php');
        exit;
    }
    $nome      = trim($_POST['nome'] ?? '');
    $rilevanza = $_POST['rilevanza'] ?? '';
    $tipo      = $_POST['tipo'] ?? '';
    $cod_norm  = trim($_POST['codice_normativa'] ?? '');
    $ambito    = trim($_POST['ambito_sociale'] ?? '');
    $frequenza = trim($_POST['frequenza_rilevazione'] ?? '');

    $img_path = uploadImmagine('immagine', 'indicatori.php');

    if ($nome === '') {
        setFlash('danger', 'Il nome dell\'indicatore è obbligatorio.');
    } elseif ($rilevanza !== '' && (!ctype_digit((string)$rilevanza) || (int)$rilevanza < 0 || (int)$rilevanza > 10)) {
        setFlash('danger', 'La rilevanza deve essere un numero intero tra 0 e 10.');
    // validazione specifica per sottotipo — rispecchia la gerarchia ISA dello schema
    } elseif ($tipo === 'ambientale' && $cod_norm === '') {
        setFlash('danger', 'Il codice normativo è obbligatorio per gli indicatori ambientali.');
    } elseif ($tipo === 'sociale' && ($ambito === '' || $frequenza === '')) {
        setFlash('danger', 'Ambito sociale e frequenza di rilevazione sono obbligatori per gli indicatori sociali.');
    } else {
        try {
            execSP('sp_inserisci_indicatore_esg', [
                $nome,
                $img_path,
                ($rilevanza !== '' ? (int)$rilevanza : null),
                $tipo ?: '',
                $cod_norm ?: '',
                $ambito ?: '',
                $frequenza ?: ''
            ]);
            logEvent('creazione_indicatore', "Indicatore ESG creato: {$nome} (tipo: " . ($tipo ?: 'generico') . ")");
            setFlash('success', 'Indicatore ESG aggiunto.');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                setFlash('danger', 'Indicatore già esistente.');
            } else {
                error_log('ESG-BALANCE Error: ' . $e->getMessage());
                setFlash('danger', 'Errore nella creazione dell\'indicatore.');
            }
        }
    }
    header('Location: indicatori.php');
    exit;
}

// uso LEFT JOIN per recuperare i dettagli di tutti i sottotipi — null se non applicabile
$indicatori = query(
    "SELECT i.nome, i.rilevanza, i.tipo, i.immagine,
            ia.codice_normativa,
            isc.ambito_sociale, isc.frequenza_rilevazione
     FROM indicatori_esg i
     LEFT JOIN indicatori_ambientali ia ON ia.nome = i.nome
     LEFT JOIN indicatori_sociali isc ON isc.nome = i.nome
     ORDER BY i.nome"
);

require_once __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4"><i class="bi bi-graph-up"></i> Indicatori ESG</h2>

<?php renderFlash(); ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-accent text-white">Nuovo Indicatore</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?php csrfField(); ?>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="rilevanza" class="form-label">Rilevanza (0-10)</label>
                        <input type="number" class="form-control" id="rilevanza" name="rilevanza" min="0" max="10"
                            step="1">
                    </div>
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select" id="tipo" name="tipo">
                            <option value="">Generico (nessuna categoria)</option>
                            <option value="ambientale">Ambientale</option>
                            <option value="sociale">Sociale</option>
                            <option value="governance">Governance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="immagine" class="form-label">Immagine</label>
                        <input type="file" class="form-control" id="immagine" name="immagine" accept="image/*">
                    </div>

                    <div id="campi_ambientale" style="display:none;">
                        <div class="mb-3">
                            <label for="codice_normativa" class="form-label">Codice Normativa</label>
                            <input type="text" class="form-control" id="codice_normativa" name="codice_normativa">
                        </div>
                    </div>

                    <div id="campi_sociale" style="display:none;">
                        <div class="mb-3">
                            <label for="ambito_sociale" class="form-label">Ambito Sociale</label>
                            <input type="text" class="form-control" id="ambito_sociale" name="ambito_sociale">
                        </div>
                        <div class="mb-3">
                            <label for="frequenza_rilevazione" class="form-label">Frequenza Rilevazione</label>
                            <input type="text" class="form-control" id="frequenza_rilevazione"
                                name="frequenza_rilevazione" placeholder="Es. Annuale, Trimestrale">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent w-100">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-accent text-white">
                Indicatori ESG
                <span class="badge bg-white text-accent float-end"><?php echo count($indicatori); ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($indicatori)): ?>
                    <p class="text-muted">Nessun indicatore presente.</p>
                <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Rilevanza</th>
                                <th>Dettagli</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($indicatori as $ind): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($ind['nome']); ?></strong></td>
                                    <td>
                                        <?php if ($ind['tipo'] === 'ambientale'): ?>
                                            <span class="badge bg-success text-white">Ambientale</span>
                                        <?php elseif ($ind['tipo'] === 'sociale'): ?>
                                            <span class="badge bg-accent text-white">Sociale</span>
                                        <?php elseif ($ind['tipo'] === 'governance'): ?>
                                            <span class="badge bg-dark text-white">Governance</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary text-white">Generico</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $ind['rilevanza'] ?? '—'; ?></td>
                                    <td>
                                        <?php if ($ind['codice_normativa']): ?>
                                            Normativa: <?php echo htmlspecialchars($ind['codice_normativa']); ?>
                                        <?php elseif ($ind['ambito_sociale']): ?>
                                            <?php echo htmlspecialchars($ind['ambito_sociale']); ?>
                                            (<?php echo htmlspecialchars($ind['frequenza_rilevazione']); ?>)
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // mostro/nascondo i campi specifici per sottotipo in base alla selezione
    document.getElementById('tipo').addEventListener('change', function() {
        document.getElementById('campi_ambientale').style.display = this.value === 'ambientale' ? 'block' : 'none';
        document.getElementById('campi_sociale').style.display = this.value === 'sociale' ? 'block' : 'none';
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
