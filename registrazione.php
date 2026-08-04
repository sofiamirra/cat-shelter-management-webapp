<?php
/**
 * Pagina di Registrazione.
 * Gestisce la creazione di un nuovo account utente includendo i campi anagrafici.
 * Implementa la validazione dei requisiti di sicurezza per la password,
 * l'hashing crittografico e l'uso differenziato dei privilegi del database.
 */

// Avvio della sessione per verificare lo stato di autenticazione corrente
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Reindirizzamento alla home se l'utente risulta già autenticato
if (isset($_SESSION['username'])) {
    header("Location: home.php");
    exit;
}

require 'includes/db_config.php';

$errore_php = "";

// Elaborazione della richiesta di registrazione
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanificazione di base per tutti i campi ricevuti
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $indirizzo = trim($_POST['indirizzo']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $conferma_password = $_POST['conferma_password'];

    // Validazione iniziale della presenza dei dati (Backend)
    if (empty($nome) || empty($cognome) || empty($indirizzo) || empty($username) || empty($password) || empty($conferma_password)) {
        $errore_php = "Per favore, compila tutti i campi obbligatori.";
    } elseif ($password !== $conferma_password) {
        $errore_php = "Le password non coincidono.";
    } else {
        
        // Fase 1: Verifica disponibilità username
        // Connessione con privilegi di sola lettura per ispezionare la tabella utenti
        $con_lettura = get_db_connection('lecture');
        $utente_esiste = false;

        $query_check = "SELECT username FROM utenti WHERE username = ?";
        if ($stmt_check = mysqli_prepare($con_lettura, $query_check)) {
            mysqli_stmt_bind_param($stmt_check, "s", $username);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            // Se la query restituisce almeno una riga, lo username è già impegnato
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $utente_esiste = true;
                $errore_php = "Questo username è già in uso. Scegline un altro.";
            }
            mysqli_stmt_close($stmt_check);
        }
        // Chiusura esplicita della connessione di lettura
        mysqli_close($con_lettura);

        // Fase 2: Registrazione
        // Procedura di inserimento subordinata alla disponibilità dello username
        if (!$utente_esiste) {
            // Connessione con privilegi di scrittura dedicati alla tabella utenti
            $con_scrittura = get_db_connection('registrator');
            
            // Cifratura irreversibile della password secondo gli standard di sicurezza attuali
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $is_admin = 0; // Impostazione predefinita per i nuovi iscritti (utente base)

            // Query aggiornata per includere nome, cognome e indirizzo
            $query_insert = "INSERT INTO utenti (nome, cognome, indirizzo, username, password, is_admin) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt_insert = mysqli_prepare($con_scrittura, $query_insert)) {
                // Binding dei parametri: sssssi -> 5 stringhe, 1 intero
                mysqli_stmt_bind_param($stmt_insert, "sssssi", $nome, $cognome, $indirizzo, $username, $hash_password, $is_admin);
                
                if (mysqli_stmt_execute($stmt_insert)) {
                    // Completamento della registrazione e reindirizzamento al form di login
                    header("Location: login.php");
                    exit;
                } else {
                    $errore_php = "Errore di sistema durante la registrazione.";
                }
                mysqli_stmt_close($stmt_insert);
            }
            // Chiusura esplicita della connessione di scrittura
            mysqli_close($con_scrittura);
        }
    }
}

require 'includes/header.php';
?>

<!-- Struttura Card Centrata (Coerente con la pagina di Login) -->
<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 500px;">
        
        <div class="auth-header">
            <h2>Crea un nuovo account</h2>
            <p>Unisciti al Parco delle Fusa.</p>
        </div>
        
        <?php
        // Blocco per la visualizzazione di eventuali messaggi di errore gestiti dal server
        if (!empty($errore_php)) {
            echo "<div class='auth-alert'>$errore_php</div>";
        }
        ?>

        <form action="registrazione.php" method="POST" id="form-registrazione">
            
            <!-- Nuovi campi anagrafici richiesti dal DB -->
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome">
                <span class="errore-js" id="err-nome"></span>
            </div>

            <div class="form-group">
                <label for="cognome">Cognome:</label>
                <input type="text" id="cognome" name="cognome">
                <span class="errore-js" id="err-cognome"></span>
            </div>

            <div class="form-group">
                <label for="indirizzo">Indirizzo:</label>
                <input type="text" id="indirizzo" name="indirizzo">
                <span class="errore-js" id="err-indirizzo"></span>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username">
                <span class="errore-js" id="err-username"></span>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
                <span class="errore-js" id="err-password"></span>
                <small style="display: block; color: #666; margin-top: 5px; font-size: 0.75rem; line-height: 1.4;">
                    La password deve avere tra 8 e 16 caratteri, contenere almeno una lettera maiuscola, una minuscola, un numero e un carattere speciale.
                </small>
            </div>

            <div class="form-group">
                <label for="conferma_password">Conferma Password:</label>
                <input type="password" id="conferma_password" name="conferma_password">
                <span class="errore-js" id="err-conferma"></span>
            </div>

            <button type="submit" class="btn-solid-dark w-100" style="margin-top: 1rem;">Registrati</button>
            
            <div class="auth-footer">
                <p>Hai già un account? <a href="login.php">Accedi qui</a></p>
            </div>
        </form>
    </div>
</div>

<script>
// Validazione asincrona lato client per bloccare invii non conformi ed evitare ricaricamenti inutili
document.getElementById('form-registrazione').addEventListener('submit', function(event) {
    let formValido = true;

    // Acquisizione riferimenti agli elementi del DOM
    const inputNome = document.getElementById('nome');
    const inputCognome = document.getElementById('cognome');
    const inputIndirizzo = document.getElementById('indirizzo');
    const inputUsername = document.getElementById('username');
    const inputPassword = document.getElementById('password');
    const inputConferma = document.getElementById('conferma_password');
    
    const errNome = document.getElementById('err-nome');
    const errCognome = document.getElementById('err-cognome');
    const errIndirizzo = document.getElementById('err-indirizzo');
    const errUsername = document.getElementById('err-username');
    const errPassword = document.getElementById('err-password');
    const errConferma = document.getElementById('err-conferma');

    // Reset dello stato visivo degli errori
    errNome.textContent = "";
    errCognome.textContent = "";
    errIndirizzo.textContent = "";
    errUsername.textContent = "";
    errPassword.textContent = "";
    errConferma.textContent = "";

    // Controllo compilazione campi anagrafici
    if (inputNome.value.trim() === "") {
        errNome.textContent = "Il nome è obbligatorio.";
        formValido = false;
    }
    if (inputCognome.value.trim() === "") {
        errCognome.textContent = "Il cognome è obbligatorio.";
        formValido = false;
    }
    if (inputIndirizzo.value.trim() === "") {
        errIndirizzo.textContent = "L'indirizzo è obbligatorio.";
        formValido = false;
    }
    if (inputUsername.value.trim() === "") {
        errUsername.textContent = "Lo username è obbligatorio.";
        formValido = false;
    }

    /**
     * Validazione robustezza password tramite espressione regolare (RegExp).
     * Requisiti imposti:
     * - Almeno una lettera minuscola: (?=.*[a-z])
     * - Almeno una lettera maiuscola: (?=.*[A-Z])
     * - Almeno una cifra numerica: (?=.*\d)
     * - Almeno un carattere non alfanumerico (speciale): (?=.*[\W_])
     * - Lunghezza stringa vincolata: .{8,16}
     */
    const regexPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/;
    
    if (!regexPassword.test(inputPassword.value)) {
        errPassword.textContent = "La password non rispetta i requisiti di sicurezza richiesti.";
        formValido = false;
    }

    // Verifica coincidenza delle password inserite nei due campi
    if (inputConferma.value === "") {
        errConferma.textContent = "Conferma la tua password.";
        formValido = false;
    } else if (inputPassword.value !== inputConferma.value) {
        errConferma.textContent = "Le password non coincidono.";
        formValido = false;
    }

    // Interruzione dell'azione predefinita di submit in presenza di errori di validazione
    if (!formValido) {
        event.preventDefault();
    }
});
</script>

<?php require 'includes/footer.php'; ?>