<?php
/*
 * Pagina di inserimento dei nuovi gatti
 * È accessibile esclusivamente agli amministratori e utilizza
 * l'utente modifier per registrare i dati nella tabella gatti
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Anche conoscendo direttamente l'indirizzo della pagina un utente normale non può accedervi
$is_admin = isset($_SESSION['username'])
    && isset($_SESSION['is_admin'])
    && (int) $_SESSION['is_admin'] === 1;

if (!$is_admin) {
    header('Location: home.php');
    exit;
}

require 'includes/db_config.php';

$messaggio_server = '';
$errore_server = false;

// I campi vengono inizializzati per poter mantenere i valori in caso di errore
$nome = '';
$eta_input = '';
$sesso = '';
$peso_input = '';
$colore_mantello = '';
$lunghezza_pelo = '';
$razza = '';
$colore_occhi = '';
$data_arrivo = '';
$descrizione = '';

// Il server controlla nuovamente i dati ricevuti dopo la validazione JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $eta_input = isset($_POST['eta']) ? trim($_POST['eta']) : '';
    $sesso = isset($_POST['sesso']) ? trim($_POST['sesso']) : '';
    $peso_input = isset($_POST['peso']) ? trim($_POST['peso']) : '';
    $colore_mantello = isset($_POST['colore_mantello']) ? trim($_POST['colore_mantello']) : '';
    $lunghezza_pelo = isset($_POST['lunghezza_pelo']) ? trim($_POST['lunghezza_pelo']) : '';
    $razza = isset($_POST['razza']) ? trim($_POST['razza']) : '';
    $colore_occhi = isset($_POST['colore_occhi']) ? trim($_POST['colore_occhi']) : '';
    $data_arrivo = isset($_POST['data_arrivo']) ? trim($_POST['data_arrivo']) : '';
    $descrizione = isset($_POST['descrizione']) ? trim($_POST['descrizione']) : '';

    $eta = $eta_input !== '' ? (int) $eta_input : 0;
    $peso = $peso_input !== '' ? (float) $peso_input : 0.0;

    // I tre campi contrassegnati nel form devono essere sempre presenti
    if ($nome === '' || $sesso === '' || $data_arrivo === '') {
        $errore_server = true;
        $messaggio_server = 'Per favore, compila tutti i campi obbligatori.';
    }

    // Vengono accettati solamente i due valori previsti dal form
    if (!$errore_server && $sesso !== 'M' && $sesso !== 'F') {
        $errore_server = true;
        $messaggio_server = 'Per favore, compila tutti i campi obbligatori.';
    }

    // Se specificate, età e peso devono rispettare gli intervalli mostrati nel form
    if (!$errore_server && $eta_input !== '' && ($eta < 0 || $eta > 25)) {
        $errore_server = true;
        $messaggio_server = 'Per favore, compila tutti i campi obbligatori.';
    }

    if (!$errore_server && $peso_input !== '' && (!is_numeric($peso_input) || $peso < 0.1 || $peso > 15)) {
        $errore_server = true;
        $messaggio_server = 'Per favore, compila tutti i campi obbligatori.';
    }

    // La lunghezza del pelo può essere vuota oppure uno dei valori previsti dal menu
    $lunghezze_consentite = array('', 'Corto', 'Semilungo', 'Lungo');

    if (!$errore_server && !in_array($lunghezza_pelo, $lunghezze_consentite, true)) {
        $errore_server = true;
        $messaggio_server = 'Per favore, compila tutti i campi obbligatori.';
    }

    // Controllo elementare della data ricevuta
    if (!$errore_server && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_arrivo)) {
        $parti_data = explode('-', $data_arrivo);
        $data_valida = checkdate((int) $parti_data[1], (int) $parti_data[2], (int) $parti_data[0]);

        if (!$data_valida) {
            $errore_server = true;
            $messaggio_server = 'Per favore, compila tutti i campi obbligatori.';
        }
    } elseif (!$errore_server) {
        $errore_server = true;
        $messaggio_server = 'Per favore, compila tutti i campi obbligatori.';
    }

    if (!$errore_server) {
        // L'inserimento richiede il ruolo MySQL con privilegi di modifica
        $con = get_db_connection('modifier');

        $query = 'INSERT INTO gatti
                  (nome, descrizione, peso, colore_mantello, lunghezza_pelo, razza, colore_occhi, eta, sesso, data_arrivo)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = mysqli_prepare($con, $query);

        if ($stmt) {
            /*
             * s = stringa, d = numero decimale, i = intero
             * L'ordine dei parametri corrisponde alle colonne dichiarate nella query
             */
            mysqli_stmt_bind_param(
                $stmt,
                'ssdssssiss',
                $nome,
                $descrizione,
                $peso,
                $colore_mantello,
                $lunghezza_pelo,
                $razza,
                $colore_occhi,
                $eta,
                $sesso,
                $data_arrivo
            );

            if (mysqli_stmt_execute($stmt)) {
                $messaggio_server = 'Scheda del felino registrata con successo nel sistema.';
                $errore_server = false;

                // Dopo il salvataggio il form viene ripulito
                $nome = '';
                $eta_input = '';
                $sesso = '';
                $peso_input = '';
                $colore_mantello = '';
                $lunghezza_pelo = '';
                $razza = '';
                $colore_occhi = '';
                $data_arrivo = '';
                $descrizione = '';
            } else {
                error_log('Errore durante l\'inserimento del gatto: ' . mysqli_stmt_error($stmt));
                $errore_server = true;
                $messaggio_server = 'Errore di persistenza durante il salvataggio nel database.';
            }

            mysqli_stmt_close($stmt);
        } else {
            // Il dettaglio della query non viene mostrato all'utente ma resta nel log del server
            error_log('Errore nella preparazione dell\'inserimento gatto: ' . mysqli_error($con));
            $errore_server = true;
            $messaggio_server = 'Errore di persistenza durante il salvataggio nel database.';
        }

        mysqli_close($con);
    }
}

require 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="prenotazione-wrapper">
        <h2 class="text-center mb-2">Inserimento Nuovo Ospite (Pannello Admin)</h2>

        <!-- Il messaggio comunica l'esito del controllo e dell'inserimento lato server -->
        <?php if (!empty($messaggio_server)): ?>
            <div class="<?php echo $errore_server ? 'auth-alert-danger' : 'auth-alert-success'; ?>">
                <?php echo htmlspecialchars($messaggio_server, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- La validazione lato client viene effettuata in Vanilla JavaScript -->
        <form action="inserimento_gatto.php" method="POST" id="form-inserimento" novalidate>
            <div class="form-group">
                <label for="nome">Nome del gatto *:</label>
                <input type="text" id="nome" name="nome" class="input-data-large" value="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="errore-js" id="err-nome"></span>
            </div>

            <div class="form-group">
                <label for="eta">Età stimata (in anni):</label>
                <input type="number" id="eta" name="eta" class="input-data-large" min="0" max="25" value="<?php echo htmlspecialchars($eta_input, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="errore-js" id="err-eta"></span>
            </div>

            <div class="form-group">
                <label for="sesso">Sesso *:</label>
                <select id="sesso" name="sesso" class="input-data-large">
                    <option value="">-- Seleziona --</option>
                    <option value="M" <?php if ($sesso === 'M') { echo 'selected'; } ?>>Maschio</option>
                    <option value="F" <?php if ($sesso === 'F') { echo 'selected'; } ?>>Femmina</option>
                </select>
                <span class="errore-js" id="err-sesso"></span>
            </div>

            <div class="form-group">
                <label for="peso">Peso (kg):</label>
                <input type="number" step="0.1" id="peso" name="peso" class="input-data-large" min="0.1" max="15" value="<?php echo htmlspecialchars($peso_input, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="errore-js" id="err-peso"></span>
            </div>

            <div class="form-group">
                <label for="colore_mantello">Colore del mantello:</label>
                <input type="text" id="colore_mantello" name="colore_mantello" class="input-data-large" value="<?php echo htmlspecialchars($colore_mantello, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="lunghezza_pelo">Lunghezza del pelo:</label>
                <select id="lunghezza_pelo" name="lunghezza_pelo" class="input-data-large">
                    <option value="">-- Seleziona --</option>
                    <option value="Corto" <?php if ($lunghezza_pelo === 'Corto') { echo 'selected'; } ?>>Corto</option>
                    <option value="Semilungo" <?php if ($lunghezza_pelo === 'Semilungo') { echo 'selected'; } ?>>Semilungo</option>
                    <option value="Lungo" <?php if ($lunghezza_pelo === 'Lungo') { echo 'selected'; } ?>>Lungo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="razza">Razza (se nota):</label>
                <input type="text" id="razza" name="razza" class="input-data-large" value="<?php echo htmlspecialchars($razza, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="colore_occhi">Colore degli occhi:</label>
                <input type="text" id="colore_occhi" name="colore_occhi" class="input-data-large" value="<?php echo htmlspecialchars($colore_occhi, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="data_arrivo">Data di arrivo in struttura *:</label>
                <input type="date" id="data_arrivo" name="data_arrivo" class="input-data-large" value="<?php echo htmlspecialchars($data_arrivo, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="errore-js" id="err-data"></span>
            </div>

            <div class="form-group">
                <label for="descrizione">Descrizione e note caratteriali:</label>
                <textarea id="descrizione" name="descrizione" rows="4" class="input-data-large"><?php echo htmlspecialchars($descrizione, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <button type="submit" class="btn-solid-dark w-100">Registra Felino</button>
        </form>
    </div>
</div>

<script>
/*
 * Validazione Vanilla JavaScript del form amministrativo
 * I controlli fondamentali vengono ripetuti anche dal PHP dopo il submit
 */
document.getElementById('form-inserimento').addEventListener('submit', function(event) {
    let formValido = true;

    const inputNome = document.getElementById('nome');
    const inputEta = document.getElementById('eta');
    const inputSesso = document.getElementById('sesso');
    const inputPeso = document.getElementById('peso');
    const inputData = document.getElementById('data_arrivo');

    const errNome = document.getElementById('err-nome');
    const errEta = document.getElementById('err-eta');
    const errSesso = document.getElementById('err-sesso');
    const errPeso = document.getElementById('err-peso');
    const errData = document.getElementById('err-data');

    // Prima di ogni controllo vengono eliminati gli errori del tentativo precedente
    [inputNome, inputEta, inputSesso, inputPeso, inputData].forEach(function(input) {
        input.classList.remove('input-error');
    });

    errNome.textContent = '';
    errEta.textContent = '';
    errSesso.textContent = '';
    errPeso.textContent = '';
    errData.textContent = '';

    // I campi contrassegnati con l'asterisco sono obbligatori
    if (inputNome.value.trim() === '') {
        errNome.textContent = 'Inserisci il nome del gatto.';
        inputNome.classList.add('input-error');
        formValido = false;
    }

    if (inputSesso.value === '') {
        errSesso.textContent = 'Specifica il sesso.';
        inputSesso.classList.add('input-error');
        formValido = false;
    }

    if (inputData.value === '') {
        errData.textContent = 'Indica la data di arrivo.';
        inputData.classList.add('input-error');
        formValido = false;
    }

    // Età e peso sono facoltativi ma, se compilati, devono rispettare i limiti del form
    if (inputEta.value !== '' && (Number(inputEta.value) < 0 || Number(inputEta.value) > 25)) {
        errEta.textContent = "L'età deve essere compresa tra 0 e 25 anni.";
        inputEta.classList.add('input-error');
        formValido = false;
    }

    if (inputPeso.value !== '' && (Number(inputPeso.value) < 0.1 || Number(inputPeso.value) > 15)) {
        errPeso.textContent = 'Il peso deve essere compreso tra 0.1 e 15 kg.';
        inputPeso.classList.add('input-error');
        formValido = false;
    }

    // Il form raggiunge il PHP soltanto se tutti i controlli lato client sono superati
    if (!formValido) {
        event.preventDefault();
    }
});
</script>

<?php require 'includes/footer.php'; ?>