<?php
/**
 * Pagina di Registrazione
 * Gestisce la creazione di un nuovo utente standard.
 * Utilizza l'utente DB "registrator" per rispettare il principio del privilegio minimo.
 */

// Avvio della sessione (se non è già attiva)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Se l'utente è già loggato, non ha senso che si registri: lo rimandiamo alla home
if (isset($_SESSION['username'])) {
    header("Location: home.php");
    exit;
}

require 'includes/db_config.php';

// Variabili per gestire i messaggi di feedback all'utente
$errore_php = "";
$successo_php = "";

// Gestione del form al momento del Submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Pulizia e recupero dei dati in ingresso
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $indirizzo = trim($_POST['indirizzo']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $conferma_password = $_POST['conferma_password'];

    // 2. Validazione lato server (fondamentale per la sicurezza, anche se c'è JS)
    if (empty($nome) || empty($cognome) || empty($indirizzo) || empty($username) || empty($password)) {
        $errore_php = "Tutti i campi sono obbligatori.";
    } elseif ($password !== $conferma_password) {
        $errore_php = "Le password non coincidono.";
    } elseif (!preg_match('/^[a-zA-Z]/', $username)) {
        $errore_php = "L'username deve iniziare con una lettera.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,16}$/', $password)) {
        $errore_php = "La password non rispetta i requisiti minimi di sicurezza.";
    } else {
        // 3. Connessione al DB con l'utente "registrator" (solo permessi di INSERT su tabella utenti)
        $con = get_db_connection('registrator');

        // 4. Controllo che l'username non sia già in uso (Prepared Statement)
        $check_query = "SELECT id FROM utenti WHERE username = ?";
        if ($check_stmt = mysqli_prepare($con, $check_query)) {
            mysqli_stmt_bind_param($check_stmt, "s", $username);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $errore_php = "Questo username è già in uso. Scegline un altro.";
            } else {
                // 5. Hash sicuro della password prima del salvataggio
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Impostiamo l'utente come standard (is_admin = 0)
                $is_admin = 0; 

                // 6. Inserimento del nuovo utente (Prepared Statement)
                $insert_query = "INSERT INTO utenti (nome, cognome, indirizzo, username, password, is_admin) VALUES (?, ?, ?, ?, ?, ?)";
                if ($insert_stmt = mysqli_prepare($con, $insert_query)) {
                    mysqli_stmt_bind_param($insert_stmt, "sssssi", $nome, $cognome, $indirizzo, $username, $password_hash, $is_admin);
                    
                    if (mysqli_stmt_execute($insert_stmt)) {
                        $successo_php = "Registrazione completata con successo! Ora puoi accedere.";
                    } else {
                        $errore_php = "Errore durante la registrazione. Riprova più tardi.";
                    }
                    mysqli_stmt_close($insert_stmt);
                }
            }
            mysqli_stmt_close($check_stmt);
        }
        mysqli_close($con);
    }
}

// Inclusione dell'header visivo
require 'includes/header.php';
?>

<!-- Contenitore Principale: Sfrutta le classi CSS standardizzate -->
<div class="auth-wrapper">
    <div class="auth-card auth-card-large"> 
        
        <div class="auth-header">
            <h2>Crea un Account</h2>
            <p>Unisciti al Parco delle Fusa per adottare o fare volontariato.</p>
        </div>

        <?php
        // Stampa dei messaggi di feedback dal server
        if (!empty($errore_php)) {
            echo "<div class='auth-alert'>$errore_php</div>";
        }
        if (!empty($successo_php)) {
            echo "<div class='auth-alert-success'>$successo_php <br><br> <a href='login.php'>Vai al Login</a></div>";
        }
        ?>

        <!-- 
            Form Validato in Vanilla JavaScript.
            Nessun CSS inline presente: tutto viene gestito tramite l'aggiunta/rimozione
            di classi CSS (come .input-error e .testo-errore) via DOM Manipulation.
        -->
        <form action="registrazione.php" method="POST" id="form-registrazione">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome">
                    <span class="errore-js" id="err-nome"></span>
                </div>
                
                <div class="form-group">
                    <label for="cognome">Cognome</label>
                    <input type="text" id="cognome" name="cognome">
                    <span class="errore-js" id="err-cognome"></span>
                </div>
            </div>

            <div class="form-group">
                <label for="indirizzo">Indirizzo Email</label>
                <input type="text" id="indirizzo" name="indirizzo">
                <span class="errore-js" id="err-indirizzo"></span>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username">
                <!-- Mostra la regola di default con stile attenuato -->
                <span class="errore-js errore-js-info" id="err-username">Deve iniziare con una lettera.</span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <!-- Mostra la regola di default con stile attenuato -->
                <span class="errore-js errore-js-info" id="err-password">8-16 caratteri. Almeno una maiuscola, minuscola, un numero e un carattere speciale.</span>
            </div>

            <div class="form-group">
                <label for="conferma_password">Conferma Password</label>
                <input type="password" id="conferma_password" name="conferma_password">
                <span class="errore-js" id="err-conferma"></span>
            </div>

            <!-- Bottoni centrati (Sostituito il vecchio codice con le nuove classi pulite) -->
            <div class="text-center mt-2">
                <button type="submit" class="btn-solid-dark w-100">Registrati</button>
                <p class="form-switch-text">Hai già un account?</p>
                <a href="login.php" class="btn-outline-dark">Accedi qui</a>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     VALIDAZIONE VANILLA JAVASCRIPT
     ========================================== -->
<script>
document.getElementById('form-registrazione').addEventListener('submit', function(event) {
    let formValido = true;
    
    // 1. Reset degli stati di errore visivi
    const campi = ['nome', 'cognome', 'indirizzo', 'username', 'password', 'conferma_password'];
    
    campi.forEach(id => {
        const inputEl = document.getElementById(id);
        const errEl = document.getElementById('err-' + id);
        
        // Rimuove il bordo rosso
        inputEl.classList.remove('input-error');
        
        // Se non sono username o password (che hanno testi descrittivi), svuota l'errore
        if(id !== 'username' && id !== 'password') {
            errEl.textContent = "";
        } else {
            // Per username e password, rimuove solo il colore rosso acceso, tornando al grigio info
            errEl.classList.remove('testo-errore');
        }
    });

    // 2. Controllo campi base obbligatori
    ['nome', 'cognome', 'indirizzo'].forEach(id => {
        const elemento = document.getElementById(id);
        if (elemento.value.trim() === "") {
            document.getElementById('err-' + id).textContent = "Campo obbligatorio.";
            elemento.classList.add('input-error');
            formValido = false;
        }
    });

    // 3. Validazione Username (Deve iniziare con lettera alfabetica)
    const username = document.getElementById('username');
    const regexUsername = /^[a-zA-Z]/;
    if (!regexUsername.test(username.value)) {
        document.getElementById('err-username').textContent = "L'username deve iniziare con una lettera.";
        document.getElementById('err-username').classList.add('testo-errore'); // Evidenzia l'errore
        username.classList.add('input-error');
        formValido = false;
    }

    // 4. Validazione Password di sicurezza (8-16 char, mix di casi, numeri e speciali)
    const password = document.getElementById('password');
    const regexPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,16}$/;
    if (!regexPassword.test(password.value)) {
        document.getElementById('err-password').textContent = "La password non rispetta i requisiti di sicurezza.";
        document.getElementById('err-password').classList.add('testo-errore'); // Evidenzia l'errore
        password.classList.add('input-error');
        formValido = false;
    }

    // 5. Conferma Password (Verifica coincidenza)
    const conferma = document.getElementById('conferma_password');
    if (password.value !== conferma.value || conferma.value === "") {
        document.getElementById('err-conferma').textContent = "Le password non coincidono.";
        conferma.classList.add('input-error');
        formValido = false;
    }

    // 6. Blocco sottomissione se c'è un errore
    if (!formValido) {
        event.preventDefault(); // Intercetta l'evento e blocca il form come da specifiche
    }
});
</script>

<?php require 'includes/footer.php'; ?>