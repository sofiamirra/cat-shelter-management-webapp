<?php
/**
 * Pagina di Login.
 * Gestisce l'autenticazione tramite password cifrata (hash) e implementa
 * il cookie "Ricordami" per precompilare il campo username (durata 72 ore).
 */

// Avvio la sessione se non è già presente
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Se l'utente ha già una sessione attiva, lo blocco e lo rimando alla home
if (isset($_SESSION['username'])) {
    header("Location: home.php");
    exit;
}

require 'includes/db_config.php';

$errore_login = "";

// Elaborazione dei dati quando il form viene inviato tramite POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Pulisco gli input per evitare spazi accidentali
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    // Verifico se la spunta del "Ricordami" è stata selezionata
    $ricordami = isset($_POST['ricordami']);

    // Controllo lato server per bloccare tentativi di bypass del JS
    if (empty($username) || empty($password)) {
        $errore_login = "Inserisci sia lo username che la password.";
    } else {
        // Uso il ruolo 'lecture' poiché per il login serve solo leggere (SELECT) dal DB
        $con = get_db_connection('lecture');
        
        // Uso un prepared statement per proteggere il sistema dalle SQL Injection
        $query = "SELECT password, is_admin FROM utenti WHERE username = ?";
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            // Associo i risultati estratti dal DB a queste due variabili
            mysqli_stmt_bind_result($stmt, $hash_password_db, $is_admin);
            
            // Se trovo l'utente, fetch() restituisce true
            if (mysqli_stmt_fetch($stmt)) {
                
                // Verifico la password inserita con l'hash salvato nel database
                if (password_verify($password, $hash_password_db)) {
                    
                    // Password corretta: salvo i dati in sessione
                    $_SESSION['username'] = $username;
                    $_SESSION['is_admin'] = $is_admin; 

                    // Gestione del Cookie di 72 ore per precompilare lo username
                    if ($ricordami) {
                        // Il cookie dura 72 ore (72 * 3600 secondi)
                        setcookie("ricordami_user", $username, time() + (72 * 3600), "/");
                    } else {
                        // Se l'utente toglie la spunta, distruggo il cookie impostandolo al passato
                        setcookie("ricordami_user", "", time() - 3600, "/");
                    }

                    // Autenticazione completata, reindirizzo alla home
                    header("Location: home.php");
                    exit;
                } else {
                    $errore_login = "Credenziali non valide.";
                }
            } else {
                $errore_login = "Credenziali non valide.";
            }
            // Chiudo lo statement per liberare la memoria
            mysqli_stmt_close($stmt);
        }
        mysqli_close($con);
    }
}

// Se il cookie esiste, lo recupero per precompilare l'HTML
$valore_cookie_user = "";
if (isset($_COOKIE['ricordami_user'])) {
    $valore_cookie_user = $_COOKIE['ricordami_user'];
}

require 'includes/header.php';
?>

<div class="form-container">
    <h2>Accedi al tuo account</h2>
    
    <?php
    // Mostro eventuali errori restituiti da PHP in alto
    if (!empty($errore_login)) {
        echo "<p class='errore-php'>$errore_login</p>";
    }
    ?>

    <!-- Il modulo invia i dati a sé stesso per l'elaborazione -->
    <form action="login.php" method="POST" id="form-login">
        
        <div class="form-group">
            <label for="username">Username:</label>
            <!-- Precompilo l'attributo 'value' se ho trovato il cookie -->
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($valore_cookie_user, ENT_QUOTES, 'UTF-8'); ?>">
            <span class="errore-js" id="err-username"></span>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
            <span class="errore-js" id="err-password"></span>
        </div>

        <div class="form-group checkbox-group">
            <!-- Mantengo la spunta attiva se il cookie era presente -->
            <input type="checkbox" id="ricordami" name="ricordami" <?php if(!empty($valore_cookie_user)) echo "checked"; ?>>
            <label for="ricordami">Ricordami per 72 ore (salva solo l'username)</label>
        </div>

        <button type="submit" class="btn-primario">Accedi</button>
    </form>
</div>

<!-- Logica di validazione lato client (Vanilla JS) -->
<script>
// Intercetto l'evento di invio del form
document.getElementById('form-login').addEventListener('submit', function(event) {
    let formValido = true;

    // Recupero i riferimenti agli input e ai messaggi di errore
    const inputUsername = document.getElementById('username');
    const inputPassword = document.getElementById('password');
    const errUsername = document.getElementById('err-username');
    const errPassword = document.getElementById('err-password');

    // Resetto gli errori per ogni nuovo tentativo
    errUsername.textContent = "";
    errPassword.textContent = "";

    // Controllo che lo username non sia una stringa vuota
    if (inputUsername.value.trim() === "") {
        errUsername.textContent = "Inserisci il tuo username.";
        formValido = false;
    }

    // Controllo che la password sia stata inserita
    if (inputPassword.value.trim() === "") {
        errPassword.textContent = "Inserisci la password.";
        formValido = false;
    }

    // Se un controllo fallisce, prevengo l'invio HTTP al server
    if (!formValido) {
        event.preventDefault();
    }
});
</script>

<?php require 'includes/footer.php'; ?>