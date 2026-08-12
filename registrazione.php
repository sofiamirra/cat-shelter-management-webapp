<?php
/*
 * Pagina di registrazione
 * Gestisce la creazione di un nuovo utente standard e controlla i dati
 * sia lato server sia tramite il codice Vanilla JavaScript del form
 */

// La sessione serve anche per impedire una nuova registrazione a chi è già autenticato
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// La pagina di ritorno viene accettata solo tra quelle previste dal sito
// In assenza di una provenienza specifica, il comportamento resta il ritorno alla home
$pagina_ritorno = 'home.php';
$pagine_consentite = array('ospiti.php', 'volontariato.php', 'area_personale.php');

if (isset($_GET['ritorno']) && in_array($_GET['ritorno'], $pagine_consentite, true)) {
    $pagina_ritorno = $_GET['ritorno'];
}

// Un utente già autenticato non deve creare un secondo account
if (isset($_SESSION['username'])) {
    header('Location: ' . $pagina_ritorno);
    exit;
}

require 'includes/db_config.php';

// Messaggi restituiti dal controllo lato server
$errore_php = '';
$successo_php = '';

// I valori testuali vengono inizializzati per poterli mantenere nel form in caso di errore
$nome = '';
$cognome = '';
$indirizzo = '';
$username = '';

// Il parametro di ritorno viene mantenuto anche dopo l'invio del form
$azione_form = 'registrazione.php';
$link_login = 'login.php';

if ($pagina_ritorno !== 'home.php') {
    $azione_form .= '?ritorno=' . $pagina_ritorno;
    $link_login .= '?ritorno=' . $pagina_ritorno;
}

// Il server ricontrolla sempre i dati ricevuti, indipendentemente dalla validazione JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $cognome = isset($_POST['cognome']) ? trim($_POST['cognome']) : '';
    $indirizzo = isset($_POST['indirizzo']) ? trim($_POST['indirizzo']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $conferma_password = isset($_POST['conferma_password']) ? $_POST['conferma_password'] : '';

    // I controlli lato server ripetono i vincoli applicati dal form sul browser
    if (empty($nome) || empty($cognome) || empty($indirizzo) || empty($username) || empty($password)) {
        $errore_php = 'Tutti i campi sono obbligatori.';
    } elseif ($password !== $conferma_password) {
        $errore_php = 'Le password non coincidono.';
    } elseif (!preg_match('/^[a-zA-Z]/', $username)) {
        $errore_php = "L'username deve iniziare con una lettera.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s])\S{8,16}$/', $password)) {
        $errore_php = 'La password non rispetta i requisiti minimi di sicurezza.';
    } else {
        /*
         * Il controllo dello username richiede una SELECT
         * Viene quindi utilizzato l'utente di sola lettura e non il registrator
         */
        $con_lettura = get_db_connection('lecture');
        $check_query = 'SELECT id FROM utenti WHERE username = ?';
        $check_stmt = mysqli_prepare($con_lettura, $check_query);

        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, 's', $username);

            if (mysqli_stmt_execute($check_stmt)) {
                mysqli_stmt_store_result($check_stmt);

                if (mysqli_stmt_num_rows($check_stmt) > 0) {
                    $errore_php = 'Questo username è già in uso. Scegline un altro.';
                }
            } else {
                // Il dettaglio tecnico rimane nel log mentre all'utente viene mostrato un messaggio generico
                error_log('Errore durante il controllo dello username: ' . mysqli_stmt_error($check_stmt));
                $errore_php = 'Errore durante la registrazione. Riprova più tardi.';
            }

            mysqli_stmt_close($check_stmt);
        } else {
            error_log('Errore nella preparazione del controllo username: ' . mysqli_error($con_lettura));
            $errore_php = 'Errore durante la registrazione. Riprova più tardi.';
        }

        mysqli_close($con_lettura);

        /*
         * Solo se lo username è disponibile viene aperta una nuova connessione
         * con registrator, utilizzato esclusivamente per l'inserimento in utenti
         */
        if (empty($errore_php)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $is_admin = 0;

            $con_registrazione = get_db_connection('registrator');
            $insert_query = 'INSERT INTO utenti (nome, cognome, indirizzo, username, password, is_admin) VALUES (?, ?, ?, ?, ?, ?)';
            $insert_stmt = mysqli_prepare($con_registrazione, $insert_query);

            if ($insert_stmt) {
                mysqli_stmt_bind_param($insert_stmt, 'sssssi', $nome, $cognome, $indirizzo, $username, $password_hash, $is_admin);

                if (mysqli_stmt_execute($insert_stmt)) {
                    $successo_php = 'Registrazione completata con successo! Ora puoi accedere.';

                    // Dopo una registrazione riuscita i campi testuali tornano vuoti
                    $nome = '';
                    $cognome = '';
                    $indirizzo = '';
                    $username = '';
                } else {
                    error_log('Errore durante l\'inserimento del nuovo utente: ' . mysqli_stmt_error($insert_stmt));
                    $errore_php = 'Errore durante la registrazione. Riprova più tardi.';
                }

                mysqli_stmt_close($insert_stmt);
            } else {
                error_log('Errore nella preparazione della registrazione: ' . mysqli_error($con_registrazione));
                $errore_php = 'Errore durante la registrazione. Riprova più tardi.';
            }

            mysqli_close($con_registrazione);
        }
    }
}

require 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card auth-card-large">
        <div class="auth-header">
            <h2>Crea un Account</h2>
            <p>Unisciti al Parco delle Fusa per adottare o fare volontariato.</p>
        </div>

        <?php if (!empty($errore_php)) { ?>
            <div class="auth-alert-danger"><?php echo htmlspecialchars($errore_php, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>

        <?php if (!empty($successo_php)) { ?>
            <div class="auth-alert-success"><?php echo htmlspecialchars($successo_php, ENT_QUOTES, 'UTF-8'); ?><br><br><a href="<?php echo htmlspecialchars($link_login, ENT_QUOTES, 'UTF-8'); ?>">Vai al Login</a></div>
        <?php } ?>

        <!-- Il form viene validato dal codice Vanilla JavaScript prima dell'invio al server -->
        <form action="<?php echo htmlspecialchars($azione_form, ENT_QUOTES, 'UTF-8'); ?>" method="POST" id="form-registrazione">
            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="errore-js" id="err-nome"></span>
                </div>

                <div class="form-group">
                    <label for="cognome">Cognome</label>
                    <input type="text" id="cognome" name="cognome" value="<?php echo htmlspecialchars($cognome, ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="errore-js" id="err-cognome"></span>
                </div>
            </div>

            <div class="form-group">
                <label for="indirizzo">Indirizzo Email</label>
                <input type="text" id="indirizzo" name="indirizzo" value="<?php echo htmlspecialchars($indirizzo, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="errore-js" id="err-indirizzo"></span>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                <!-- La regola rimane visibile anche prima dell'eventuale errore -->
                <span class="errore-js errore-js-info" id="err-username">Deve iniziare con una lettera.</span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <!-- La password non viene mai reinserita automaticamente dopo un invio -->
                <span class="errore-js errore-js-info" id="err-password">8-16 caratteri. Almeno una maiuscola, minuscola, un numero e un carattere speciale.</span>
            </div>

            <div class="form-group">
                <label for="conferma_password">Conferma Password</label>
                <input type="password" id="conferma_password" name="conferma_password">
                <span class="errore-js" id="err-conferma_password"></span>
            </div>

            <div class="text-center mt-2">
                <button type="submit" class="btn-solid-dark w-100">Registrati</button>
                <p class="form-switch-text">Hai già un account?</p>
                <a href="<?php echo htmlspecialchars($link_login, ENT_QUOTES, 'UTF-8'); ?>" class="btn-outline-dark">Accedi qui</a>
            </div>
        </form>
    </div>
</div>

<script>
/*
 * Validazione lato client del form di registrazione
 * Gli stessi vincoli principali vengono ricontrollati dal PHP dopo l'invio
 */
document.getElementById('form-registrazione').addEventListener('submit', function(event) {
    let formValido = true;
    const campi = ['nome', 'cognome', 'indirizzo', 'username', 'password', 'conferma_password'];

    // Prima di ogni controllo vengono eliminati gli eventuali errori del tentativo precedente
    campi.forEach(function(id) {
        const input = document.getElementById(id);
        const errore = document.getElementById('err-' + id);

        input.classList.remove('input-error');

        if (id !== 'username' && id !== 'password') {
            errore.textContent = '';
        } else {
            errore.classList.remove('testo-errore');
        }
    });

    // Nome, cognome e indirizzo non possono essere inviati vuoti
    ['nome', 'cognome', 'indirizzo'].forEach(function(id) {
        const input = document.getElementById(id);

        if (input.value.trim() === '') {
            document.getElementById('err-' + id).textContent = 'Campo obbligatorio.';
            input.classList.add('input-error');
            formValido = false;
        }
    });

    // Lo username deve iniziare con un carattere alfabetico
    const username = document.getElementById('username');
    const regexUsername = /^[a-zA-Z]/;

    if (!regexUsername.test(username.value)) {
        document.getElementById('err-username').textContent = "L'username deve iniziare con una lettera.";
        document.getElementById('err-username').classList.add('testo-errore');
        username.classList.add('input-error');
        formValido = false;
    }

    // La password deve rispettare lunghezza e composizione richieste dalla consegna
    const password = document.getElementById('password');
    const regexPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s])\S{8,16}$/;

    if (!regexPassword.test(password.value)) {
        document.getElementById('err-password').textContent = 'La password non rispetta i requisiti di sicurezza.';
        document.getElementById('err-password').classList.add('testo-errore');
        password.classList.add('input-error');
        formValido = false;
    }

    // Il secondo inserimento della password deve essere presente e coincidere con il primo
    const conferma = document.getElementById('conferma_password');

    if (password.value !== conferma.value || conferma.value === '') {
        document.getElementById('err-conferma_password').textContent = 'Le password non coincidono.';
        conferma.classList.add('input-error');
        formValido = false;
    }

    // Se almeno un controllo fallisce il browser non invia il form al PHP
    if (!formValido) {
        event.preventDefault();
    }
});
</script>

<?php require 'includes/footer.php'; ?>