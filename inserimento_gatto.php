<?php
/**
 * Pagina di Inserimento Nuovi Gatti (Area Riservata Amministratori).
 * Permette agli operatori autorizzati di registrare nuovi felini nel database.
 * Utilizza l'utente 'modifier' per rispettare i privilegi minimi.
 */

// Avvio della sessione per verificare i permessi di accesso
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Controllo degli accessi: blocco gli utenti non loggati o non amministratori
if (!isset($_SESSION['username']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

require 'includes/db_config.php';

$messaggio_server = "";
$errore_server = false;

// Elaborazione dei dati al momento dell'invio del form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Acquisizione e sanificazione dei dati in ingresso, usando i nomi esatti del form
    $nome = trim($_POST['nome']);
    $eta = (int)$_POST['eta'];
    $sesso = trim($_POST['sesso']);
    $peso = (float)$_POST['peso'];
    $colore_mantello = trim($_POST['colore_mantello']);
    $lunghezza_pelo = trim($_POST['lunghezza_pelo']);
    $razza = trim($_POST['razza']);
    $colore_occhi = trim($_POST['colore_occhi']);
    $data_arrivo = trim($_POST['data_arrivo']);
    $descrizione = trim($_POST['descrizione']);

    // Validazione backend fondamentale
    if (empty($nome) || empty($sesso) || empty($data_arrivo)) {
        $errore_server = true;
        $messaggio_server = "Per favore, compila tutti i campi obbligatori.";
    } else {
        // Connessione al database tramite il ruolo con privilegi di scrittura (modifier)
        $con = get_db_connection('modifier');

        // Query parametrizzata allineata ESATTAMENTE alle colonne del database mostrate in DBeaver
        $query = "INSERT INTO gatti (nome, descrizione, peso, colore_mantello, lunghezza_pelo, razza, colore_occhi, eta, sesso, data_arrivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($con, $query)) {
            // Binding dei parametri: ssdssssiss 
            // (s=stringa, s=stringa, d=double/float, s=stringa, s=stringa, s=stringa, s=stringa, i=intero, s=stringa, s=stringa)
            mysqli_stmt_bind_param($stmt, "ssdssssiss", $nome, $descrizione, $peso, $colore_mantello, $lunghezza_pelo, $razza, $colore_occhi, $eta, $sesso, $data_arrivo);
            
            if (mysqli_stmt_execute($stmt)) {
                $messaggio_server = "Scheda del felino registrata con successo nel sistema.";
                $errore_server = false;
            } else {
                $errore_server = true;
                $messaggio_server = "Errore di persistenza durante il salvataggio nel database.";
            }
            mysqli_stmt_close($stmt);
        } else {
            // Se c'è un errore nella sintassi SQL, lo mostriamo per il debug
            $errore_server = true;
            $messaggio_server = "Errore nella preparazione della query: " . mysqli_error($con);
        }
        // Rilascio delle risorse di connessione
        mysqli_close($con);
    }
}

require 'includes/header.php';
?>

<div class="form-container">
    <h2>Inserimento Nuovo Ospite (Pannello Admin)</h2>
    
    <?php
    // Feedback visivo delle operazioni lato server
    if (!empty($messaggio_server)) {
        $classe_messaggio = $errore_server ? 'errore-php' : 'successo-php';
        echo "<p class='$classe_messaggio'>$messaggio_server</p>";
    }
    ?>

    <form action="inserimento_gatto.php" method="POST" id="form-inserimento">
        
        <div class="form-group">
            <label for="nome">Nome del gatto *:</label>
            <input type="text" id="nome" name="nome">
            <span class="errore-js" id="err-nome"></span>
        </div>

        <div class="form-group">
            <label for="eta">Età stimata (in anni):</label>
            <input type="number" id="eta" name="eta" min="0" max="25">
        </div>

        <div class="form-group">
            <label for="sesso">Sesso *:</label>
            <select id="sesso" name="sesso">
                <option value="">-- Seleziona --</option>
                <option value="M">Maschio</option>
                <option value="F">Femmina</option>
            </select>
            <span class="errore-js" id="err-sesso"></span>
        </div>

        <div class="form-group">
            <label for="peso">Peso (kg):</label>
            <input type="number" step="0.1" id="peso" name="peso" min="0.1" max="15">
        </div>

        <div class="form-group">
            <label for="colore_mantello">Colore del mantello:</label>
            <input type="text" id="colore_mantello" name="colore_mantello">
        </div>

        <div class="form-group">
            <label for="lunghezza_pelo">Lunghezza del pelo:</label>
            <select id="lunghezza_pelo" name="lunghezza_pelo">
                <option value="">-- Seleziona --</option>
                <option value="Corto">Corto</option>
                <option value="Semilungo">Semilungo</option>
                <option value="Lungo">Lungo</option>
            </select>
        </div>

        <div class="form-group">
            <label for="razza">Razza (se nota):</label>
            <input type="text" id="razza" name="razza">
        </div>

        <div class="form-group">
            <label for="colore_occhi">Colore degli occhi:</label>
            <input type="text" id="colore_occhi" name="colore_occhi">
        </div>

        <div class="form-group">
            <label for="data_arrivo">Data di arrivo in struttura *:</label>
            <input type="date" id="data_arrivo" name="data_arrivo">
            <span class="errore-js" id="err-data"></span>
        </div>

        <div class="form-group">
            <label for="descrizione">Descrizione e note caratteriali:</label>
            <textarea id="descrizione" name="descrizione" rows="4"></textarea>
        </div>

        <button type="submit" class="btn-primario">Registra Felino</button>
    </form>
</div>

<script>
// Validazione lato client per i campi obbligatori prima dell'invio al server
document.getElementById('form-inserimento').addEventListener('submit', function(event) {
    let formValido = true;

    // Acquisizione riferimenti elementi del DOM
    const inputNome = document.getElementById('nome');
    const inputSesso = document.getElementById('sesso');
    const inputData = document.getElementById('data_arrivo');
    
    const errNome = document.getElementById('err-nome');
    const errSesso = document.getElementById('err-sesso');
    const errData = document.getElementById('err-data');

    // Reset visivo degli errori
    errNome.textContent = "";
    errSesso.textContent = "";
    errData.textContent = "";

    // Controllo compilazione campi essenziali
    if (inputNome.value.trim() === "") {
        errNome.textContent = "Inserisci il nome del gatto.";
        formValido = false;
    }

    if (inputSesso.value === "") {
        errSesso.textContent = "Specifica il sesso.";
        formValido = false;
    }

    if (inputData.value === "") {
        errData.textContent = "Indica la data di arrivo.";
        formValido = false;
    }

    // Interruzione del submit in caso di violazione dei vincoli
    if (!formValido) {
        event.preventDefault();
    }
});
</script>

<?php require 'includes/footer.php'; ?>