<?php
/*
 * Pagina di inserimento dei nuovi gatti
 * È accessibile esclusivamente agli amministratori e utilizza
 * l'utente modifier per registrare i dati nella tabella gatti
 */

// La pagina non include ancora l'header comune e inizializza direttamente la sessione
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
 * Il controllo amministratore viene effettuato lato server
 * Anche conoscendo direttamente l'indirizzo della pagina un utente normale non può accedervi
 */
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

/*
 * Il server controlla nuovamente tutti i dati ricevuti
 * La validazione JavaScript migliora l'interazione ma non viene considerata sufficiente per l'inserimento
 */
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

    // Le versioni numeriche vengono utilizzate nei controlli e successivamente nel prepared statement
    $eta = $eta_input !== '' ? (int) $eta_input : 0;
    $peso = $peso_input !== '' ? (float) $peso_input : 0.0;

    // Tutti i dati previsti dalla scheda devono essere presenti prima dell'inserimento
    if (
        $nome === '' ||
        $eta_input === '' ||
        $sesso === '' ||
        $peso_input === '' ||
        $colore_mantello === '' ||
        $lunghezza_pelo === '' ||
        $razza === '' ||
        $colore_occhi === '' ||
        $data_arrivo === '' ||
        $descrizione === ''
    ) {
        $errore_server = true;
        $messaggio_server = 'Per favore, compila tutti i campi.';
    }

    // Vengono accettati solamente i due valori previsti dal menu del form
    if (!$errore_server && $sesso !== 'M' && $sesso !== 'F') {
        $errore_server = true;
        $messaggio_server = 'Il valore selezionato per il sesso non è valido.';
    }

    // L'età deve essere un numero intero maggiore o uguale a zero
    if (
        !$errore_server &&
        (!preg_match('/^\d+$/', $eta_input) || $eta < 0)
    ) {
        $errore_server = true;
        $messaggio_server = 'L\'età deve essere un numero intero maggiore o uguale a 0.';
    }

    // Il peso deve essere numerico e maggiore di zero
    if (
        !$errore_server &&
        (!is_numeric($peso_input) || $peso < 0.1)
    ) {
        $errore_server = true;
        $messaggio_server = 'Il peso deve essere almeno 0.1 kg.';
    }

    // La lunghezza del pelo deve corrispondere a uno dei valori realmente presenti nel form
    $lunghezze_consentite = array('Corto', 'Semilungo', 'Lungo');

    if (!$errore_server && !in_array($lunghezza_pelo, $lunghezze_consentite, true)) {
        $errore_server = true;
        $messaggio_server = 'Seleziona una lunghezza del pelo valida.';
    }

    /*
     * La data deve rispettare il formato YYYY-MM-DD prodotto dal campo date
     * checkdate verifica inoltre che giorno, mese e anno costituiscano una data reale
     */
    if (!$errore_server && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_arrivo)) {
        $parti_data = explode('-', $data_arrivo);

        $data_valida = checkdate(
            (int) $parti_data[1],
            (int) $parti_data[2],
            (int) $parti_data[0]
        );

        if (!$data_valida) {
            $errore_server = true;
            $messaggio_server = 'La data di arrivo inserita non è valida.';
        }
    } elseif (!$errore_server) {
        $errore_server = true;
        $messaggio_server = 'La data di arrivo inserita non è valida.';
    }

    // Soltanto dati che hanno superato tutti i controlli raggiungono l'operazione di scrittura
    if (!$errore_server) {

        // L'inserimento modifica il database e richiede quindi l'utente modifier
        $con = get_db_connection('modifier');

        $query = 'INSERT INTO gatti
                  (nome, descrizione, peso, colore_mantello, lunghezza_pelo, razza, colore_occhi, eta, sesso, data_arrivo)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        // Lo statement viene preparato mantenendo separata la query dai valori ricevuti dal form
        $stmt = mysqli_prepare($con, $query);

        if ($stmt) {
            /*
             * s = stringa, d = numero decimale, i = intero
             * I tipi e l'ordine dei parametri devono corrispondere ai placeholder presenti nella query
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

            // execute effettua l'INSERT utilizzando i valori precedentemente associati
            if (mysqli_stmt_execute($stmt)) {
                $messaggio_server = 'Scheda del felino registrata con successo nel sistema.';
                $errore_server = false;

                // Dopo il salvataggio riuscito il form viene riportato allo stato iniziale
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

                // Il dettaglio tecnico dell'errore resta nel log e non viene mostrato direttamente all'utente
                error_log('Errore durante l\'inserimento del gatto: ' . mysqli_stmt_error($stmt));
                $errore_server = true;
                $messaggio_server = 'Errore durante il salvataggio. Riprova più tardi.';
            }

            mysqli_stmt_close($stmt);
        } else {
            error_log('Errore nella preparazione dell\'inserimento gatto: ' . mysqli_error($con));
            $errore_server = true;
            $messaggio_server = 'Errore durante il salvataggio. Riprova più tardi.';
        }

        mysqli_close($con);
    }
}

require 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="prenotazione-wrapper inserimento-gatto-wrapper">

        <a href="admin.php" class="back-admin-link">
            <span aria-hidden="true">←</span>
            Torna al pannello
        </a>

        <!-- Titolo principale della pagina amministrativa di inserimento -->
        <h1 class="text-center mb-2">Inserimento Nuovo Ospite</h1>

        <!-- Il messaggio comunica l'esito del controllo e dell'inserimento lato server -->
        <?php if (!empty($messaggio_server)): ?>
            <div
                class="<?php echo $errore_server ? 'auth-alert-danger' : 'auth-alert-success'; ?>"
                role="<?php echo $errore_server ? 'alert' : 'status'; ?>"
            >
                <?php echo htmlspecialchars($messaggio_server, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- novalidate lascia la validazione lato client al codice Vanilla JavaScript -->
        <form action="inserimento_gatto.php" method="POST" id="form-inserimento" novalidate>

            <div class="form-group">
                <label for="nome">Nome del gatto:</label>
                <input
                    type="text"
                    id="nome"
                    name="nome"
                    class="input-data-large"
                    value="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>"
                    aria-describedby="err-nome"
                    required
                >
                <span class="errore-js" id="err-nome"></span>
            </div>

            <div class="form-group">
                <label for="eta">Età stimata (in anni):</label>
                <input
                    type="number"
                    id="eta"
                    name="eta"
                    class="input-data-large"
                    min="0"
                    step="1"
                    value="<?php echo htmlspecialchars($eta_input, ENT_QUOTES, 'UTF-8'); ?>"
                    aria-describedby="err-eta"
                    required
                >
                <span class="errore-js" id="err-eta"></span>
            </div>

            <div class="form-group">
                <label for="sesso">Sesso:</label>
                <select
                    id="sesso"
                    name="sesso"
                    class="input-data-large"
                    aria-describedby="err-sesso"
                    required
                >
                    <option value="">-- Seleziona --</option>
                    <option value="M" <?php if ($sesso === 'M') { echo 'selected'; } ?>>Maschio</option>
                    <option value="F" <?php if ($sesso === 'F') { echo 'selected'; } ?>>Femmina</option>
                </select>
                <span class="errore-js" id="err-sesso"></span>
            </div>

            <div class="form-group">
                <label for="peso">Peso (kg):</label>
                <input
                    type="number"
                    id="peso"
                    name="peso"
                    class="input-data-large"
                    min="0.1"
                    step="0.1"
                    value="<?php echo htmlspecialchars($peso_input, ENT_QUOTES, 'UTF-8'); ?>"
                    aria-describedby="err-peso"
                    required
                >
                <span class="errore-js" id="err-peso"></span>
            </div>

            <div class="form-group">
                <label for="colore_mantello">Colore del mantello:</label>
                <input
                    type="text"
                    id="colore_mantello"
                    name="colore_mantello"
                    class="input-data-large"
                    value="<?php echo htmlspecialchars($colore_mantello, ENT_QUOTES, 'UTF-8'); ?>"
                    aria-describedby="err-mantello"
                    required
                >
                <span class="errore-js" id="err-mantello"></span>
            </div>

            <div class="form-group">
                <label for="lunghezza_pelo">Lunghezza del pelo:</label>
                <select
                    id="lunghezza_pelo"
                    name="lunghezza_pelo"
                    class="input-data-large"
                    aria-describedby="err-pelo"
                    required
                >
                    <option value="">-- Seleziona --</option>
                    <option value="Corto" <?php if ($lunghezza_pelo === 'Corto') { echo 'selected'; } ?>>Corto</option>
                    <option value="Semilungo" <?php if ($lunghezza_pelo === 'Semilungo') { echo 'selected'; } ?>>Semilungo</option>
                    <option value="Lungo" <?php if ($lunghezza_pelo === 'Lungo') { echo 'selected'; } ?>>Lungo</option>
                </select>
                <span class="errore-js" id="err-pelo"></span>
            </div>

            <div class="form-group">
                <label for="razza">Razza:</label>
                <input
                    type="text"
                    id="razza"
                    name="razza"
                    class="input-data-large"
                    value="<?php echo htmlspecialchars($razza, ENT_QUOTES, 'UTF-8'); ?>"
                    aria-describedby="err-razza"
                    required
                >
                <span class="errore-js" id="err-razza"></span>
            </div>

            <div class="form-group">
                <label for="colore_occhi">Colore degli occhi:</label>
                <input
                    type="text"
                    id="colore_occhi"
                    name="colore_occhi"
                    class="input-data-large"
                    value="<?php echo htmlspecialchars($colore_occhi, ENT_QUOTES, 'UTF-8'); ?>"
                    aria-describedby="err-occhi"
                    required
                >
                <span class="errore-js" id="err-occhi"></span>
            </div>

            <div class="form-group">
                <label for="data_arrivo">Data di arrivo in struttura:</label>
                <input
                    type="date"
                    id="data_arrivo"
                    name="data_arrivo"
                    class="input-data-large"
                    value="<?php echo htmlspecialchars($data_arrivo, ENT_QUOTES, 'UTF-8'); ?>"
                    aria-describedby="err-data"
                    required
                >
                <span class="errore-js" id="err-data"></span>
            </div>

            <div class="form-group">
                <label for="descrizione">Descrizione e note caratteriali:</label>
                <textarea
                    id="descrizione"
                    name="descrizione"
                    rows="4"
                    class="input-data-large"
                    aria-describedby="err-descrizione"
                    required
                ><?php echo htmlspecialchars($descrizione, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <span class="errore-js" id="err-descrizione"></span>
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
    // let viene utilizzato perché cambia se almeno uno dei controlli fallisce
    let formValido = true;

    // I riferimenti agli elementi restano invariati durante tutta la validazione
    const inputNome = document.getElementById('nome');
    const inputEta = document.getElementById('eta');
    const inputSesso = document.getElementById('sesso');
    const inputPeso = document.getElementById('peso');
    const inputMantello = document.getElementById('colore_mantello');
    const inputPelo = document.getElementById('lunghezza_pelo');
    const inputRazza = document.getElementById('razza');
    const inputOcchi = document.getElementById('colore_occhi');
    const inputData = document.getElementById('data_arrivo');
    const inputDescrizione = document.getElementById('descrizione');

    const errNome = document.getElementById('err-nome');
    const errEta = document.getElementById('err-eta');
    const errSesso = document.getElementById('err-sesso');
    const errPeso = document.getElementById('err-peso');
    const errMantello = document.getElementById('err-mantello');
    const errPelo = document.getElementById('err-pelo');
    const errRazza = document.getElementById('err-razza');
    const errOcchi = document.getElementById('err-occhi');
    const errData = document.getElementById('err-data');
    const errDescrizione = document.getElementById('err-descrizione');

    // Prima di ogni nuovo tentativo vengono rimossi gli errori prodotti dalla validazione precedente
    [
        inputNome,
        inputEta,
        inputSesso,
        inputPeso,
        inputMantello,
        inputPelo,
        inputRazza,
        inputOcchi,
        inputData,
        inputDescrizione
    ].forEach(function(input) {
        input.classList.remove('input-error');
    });

    [
        errNome,
        errEta,
        errSesso,
        errPeso,
        errMantello,
        errPelo,
        errRazza,
        errOcchi,
        errData,
        errDescrizione
    ].forEach(function(errore) {
        errore.textContent = '';
    });

    // I campi testuali principali non possono essere inviati vuoti
    if (inputNome.value.trim() === '') {
        errNome.textContent = 'Inserisci il nome del gatto.';
        inputNome.classList.add('input-error');
        formValido = false;
    }

    // L'età deve essere un intero maggiore o uguale a zero
    if (inputEta.value === '') {
        errEta.textContent = 'Inserisci l\'età stimata.';
        inputEta.classList.add('input-error');
        formValido = false;
    } else if (
        !Number.isInteger(Number(inputEta.value)) ||
        Number(inputEta.value) < 0
    ) {
        errEta.textContent = 'L\'età deve essere un numero intero maggiore o uguale a 0.';
        inputEta.classList.add('input-error');
        formValido = false;
    }

    if (inputSesso.value === '') {
        errSesso.textContent = 'Specifica il sesso.';
        inputSesso.classList.add('input-error');
        formValido = false;
    }

    // Il peso viene convertito in numero prima di controllarne il valore
    if (inputPeso.value === '') {
        errPeso.textContent = 'Inserisci il peso.';
        inputPeso.classList.add('input-error');
        formValido = false;
    } else if (Number(inputPeso.value) < 0.1) {
        errPeso.textContent = 'Il peso deve essere almeno 0.1 kg.';
        inputPeso.classList.add('input-error');
        formValido = false;
    }

    if (inputMantello.value.trim() === '') {
        errMantello.textContent = 'Inserisci il colore del mantello.';
        inputMantello.classList.add('input-error');
        formValido = false;
    }

    if (inputPelo.value === '') {
        errPelo.textContent = 'Seleziona la lunghezza del pelo.';
        inputPelo.classList.add('input-error');
        formValido = false;
    }

    if (inputRazza.value.trim() === '') {
        errRazza.textContent = 'Inserisci la razza.';
        inputRazza.classList.add('input-error');
        formValido = false;
    }

    if (inputOcchi.value.trim() === '') {
        errOcchi.textContent = 'Inserisci il colore degli occhi.';
        inputOcchi.classList.add('input-error');
        formValido = false;
    }

    if (inputData.value === '') {
        errData.textContent = 'Indica la data di arrivo.';
        inputData.classList.add('input-error');
        formValido = false;
    }

    if (inputDescrizione.value.trim() === '') {
        errDescrizione.textContent = 'Inserisci una descrizione del gatto.';
        inputDescrizione.classList.add('input-error');
        formValido = false;
    }

    // Il form raggiunge il PHP soltanto se tutti i controlli lato client sono superati
    if (!formValido) {
        event.preventDefault();
    }
});
</script>

<?php require 'includes/footer.php'; ?>