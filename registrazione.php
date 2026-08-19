<?php
/*
 * Pagina di registrazione
 * Gestisce la creazione di un nuovo utente standard e controlla i dati
 * sia lato server sia tramite il codice Vanilla JavaScript del form
 */

// La sessione permette anche di impedire una nuova registrazione a chi è già autenticato
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
 * La pagina di ritorno viene accettata soltanto tra quelle previste dal sito
 * In assenza di una provenienza valida il comportamento predefinito resta il ritorno alla Home
 */
$pagina_ritorno = 'home.php';
$pagine_consentite = array('ospiti.php', 'volontariato.php', 'area_personale.php');

if (isset($_GET['ritorno']) && in_array($_GET['ritorno'], $pagine_consentite, true)) {
    $pagina_ritorno = $_GET['ritorno'];
}

// Un utente già autenticato non deve creare un secondo account durante la stessa sessione
if (isset($_SESSION['username'])) {
    header('Location: ' . $pagina_ritorno);
    exit;
}

require 'includes/db_config.php';

$errore_php = '';
$successo_php = '';

// I valori testuali vengono inizializzati per poterli mantenere nel form in caso di errore
$nome = '';
$cognome = '';
$indirizzo = '';
$username = '';

// Il parametro di ritorno viene mantenuto anche dopo l'invio del form e nel collegamento al login
$azione_form = 'registrazione.php';
$link_login = 'login.php';

if ($pagina_ritorno !== 'home.php') {
    $azione_form .= '?ritorno=' . $pagina_ritorno;
    $link_login .= '?ritorno=' . $pagina_ritorno;
}

/*
 * Il server ricontrolla sempre i dati ricevuti
 * La validazione JavaScript migliora l'interazione ma non sostituisce quella lato server
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $cognome = isset($_POST['cognome']) ? trim($_POST['cognome']) : '';
    $indirizzo = isset($_POST['indirizzo']) ? trim($_POST['indirizzo']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $conferma_password = isset($_POST['conferma_password']) ? $_POST['conferma_password'] : '';

    // I controlli lato server ripetono i vincoli principali applicati dal JavaScript
    if (empty($nome) || empty($cognome) || empty($indirizzo) || empty($username) || empty($password)) {
        $errore_php = 'Tutti i campi sono obbligatori.';
    } elseif ($password !== $conferma_password) {
        $errore_php = 'Le password non coincidono.';
    } elseif (!preg_match('/^[a-zA-Z]/', $username)) {
        $errore_php = "L'username deve iniziare con una lettera.";
    } elseif (
        strlen($password) < 8 ||
        strlen($password) > 16 ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9\s]/', $password)
    ) {
        $errore_php = 'La password non rispetta i requisiti minimi di sicurezza.';
    } else {
        /*
         * Prima dell'inserimento viene verificata la disponibilità dello username
         * Questa operazione richiede soltanto una SELECT e utilizza quindi l'utente di sola lettura
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
                // Il dettaglio tecnico resta nel log mentre all'utente viene mostrato un messaggio generico
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
         * Solo se lo username è disponibile viene aperta una connessione con registrator
         * Questo utente è utilizzato esclusivamente per l'inserimento dei nuovi account
         */
        if (empty($errore_php)) {

            // La password viene memorizzata come hash e non viene mai salvata in chiaro nel database
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Gli account creati tramite il form sono sempre utenti standard
            $is_admin = 0;

            $con_registrazione = get_db_connection('registrator');
            $insert_query = 'INSERT INTO utenti (nome, cognome, indirizzo, username, password, is_admin) VALUES (?, ?, ?, ?, ?, ?)';
            $insert_stmt = mysqli_prepare($con_registrazione, $insert_query);

            if ($insert_stmt) {
                mysqli_stmt_bind_param(
                    $insert_stmt,
                    'sssssi',
                    $nome,
                    $cognome,
                    $indirizzo,
                    $username,
                    $password_hash,
                    $is_admin
                );

                if (mysqli_stmt_execute($insert_stmt)) {
                    $successo_php = 'Registrazione completata con successo! Ora puoi accedere.';

                    // Dopo una registrazione riuscita i campi testuali vengono nuovamente svuotati
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

            <!-- Titolo principale della pagina di registrazione -->
            <h1>Crea un Account</h1>

            <p>Unisciti al Parco delle Fusa per adottare o fare volontariato.</p>
        </div>

        <?php if (!empty($errore_php)) { ?>
            <div class="alert-danger" role="alert">
                <?php echo htmlspecialchars($errore_php, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php } ?>

        <?php if (!empty($successo_php)) { ?>
            <div class="alert-success" role="status">
                <?php echo htmlspecialchars($successo_php, ENT_QUOTES, 'UTF-8'); ?>
                <br><br>
                <a href="<?php echo htmlspecialchars($link_login, ENT_QUOTES, 'UTF-8'); ?>">Vai al Login</a>
            </div>
        <?php } ?>

        <!-- novalidate lascia la validazione lato client al codice Vanilla JavaScript -->
        <form
            action="<?php echo htmlspecialchars($azione_form, ENT_QUOTES, 'UTF-8'); ?>"
            method="POST"
            id="form-registrazione"
            novalidate
        >
            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        value="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>"
                        aria-describedby="err-nome"
                    >
                    <span class="errore-js" id="err-nome"></span>
                </div>

                <div class="form-group">
                    <label for="cognome">Cognome</label>
                    <input
                        type="text"
                        id="cognome"
                        name="cognome"
                        value="<?php echo htmlspecialchars($cognome, ENT_QUOTES, 'UTF-8'); ?>"
                        aria-describedby="err-cognome"
                    >
                    <span class="errore-js" id="err-cognome"></span>
                </div>
            </div>

            <div class="form-group">
                <label for="indirizzo">Indirizzo</label>
                <input
                    type="text"
                    id="indirizzo"
                    name="indirizzo"
                    value="<?php echo htmlspecialchars($indirizzo, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="Es. Via Roma 10, Torino"
                    aria-describedby="err-indirizzo"
                >
                <span class="errore-js" id="err-indirizzo"></span>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
                    aria-describedby="err-username"
                >

                <!-- La regola rimane disponibile anche prima dell'eventuale errore -->
                <span class="errore-js errore-js-info" id="err-username">
                    Deve iniziare con una lettera.
                </span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    aria-describedby="err-password"
                >

                <!-- La password non viene mai reinserita automaticamente dopo un invio -->
                <span class="errore-js errore-js-info" id="err-password">
                    8-16 caratteri. Almeno una maiuscola, minuscola, un numero e un carattere speciale.
                </span>
            </div>

            <div class="form-group">
                <label for="conferma_password">Conferma Password</label>
                <input
                    type="password"
                    id="conferma_password"
                    name="conferma_password"
                    aria-describedby="err-conferma_password"
                >
                <span class="errore-js" id="err-conferma_password"></span>
            </div>

            <div class="auth-actions">
                <button type="submit" id="btn-registrazione" class="btn-solid-dark">Registrati</button>

                <p class="form-switch-text">Hai già un account?</p>

                <a
                    href="<?php echo htmlspecialchars($link_login, ENT_QUOTES, 'UTF-8'); ?>"
                    class="btn-outline-dark"
                >
                    Accedi qui
                </a>
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
    // let viene utilizzato perché il valore cambia se almeno uno dei controlli fallisce
    let formValido = true;

    // L'elenco dei campi resta costante durante tutta la validazione
    const campi = ['nome', 'cognome', 'indirizzo', 'username', 'password', 'conferma_password'];

    /*
     * Prima di ogni nuovo tentativo vengono rimossi gli errori precedenti
     * Per username e password vengono invece ripristinate le indicazioni informative iniziali
     */
    campi.forEach(function(id) {
        const input = document.getElementById(id);
        const errore = document.getElementById('err-' + id);

        input.classList.remove('input-error');
        errore.classList.remove('testo-errore');

        if (id === 'username') {
            errore.textContent = 'Deve iniziare con una lettera.';
        } else if (id === 'password') {
            errore.textContent = '8-16 caratteri. Almeno una maiuscola, minuscola, un numero e un carattere speciale.';
        } else {
            errore.textContent = '';
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

    const passwordValida =
        password.value.length >= 8 &&
        password.value.length <= 16 &&
        /[a-z]/.test(password.value) &&
        /[A-Z]/.test(password.value) &&
        /[0-9]/.test(password.value) &&
        /[^A-Za-z0-9\s]/.test(password.value)

    if (!passwordValida) {
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