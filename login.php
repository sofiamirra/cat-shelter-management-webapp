<?php
/*
 * Pagina di login
 * Gestisce l'autenticazione degli utenti, la sessione PHP e il cookie "Ricordami"
 * Il cookie conserva solamente lo username dell'ultimo accesso riuscito per 72 ore
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
 * Il parametro ritorno permette di tornare alla pagina da cui è stato richiesto l'accesso
 * Vengono accettate solamente destinazioni previste dal sito
 */
$pagina_ritorno = 'home.php';
$pagine_consentite = array('ospiti.php', 'volontariato.php', 'area_personale.php');

if (isset($_GET['ritorno']) && in_array($_GET['ritorno'], $pagine_consentite, true)) {
    $pagina_ritorno = $_GET['ritorno'];
}

// Un utente già autenticato viene inviato direttamente alla pagina prevista
if (isset($_SESSION['username'])) {
    header('Location: ' . $pagina_ritorno);
    exit;
}

require 'includes/db_config.php';

$errore_php = '';

/*
 * Se il cookie esiste lo username viene utilizzato per precompilare il campo
 * La password non viene invece mai memorizzata né reinserita automaticamente
 */
$username_precompilato = isset($_COOKIE['remember_user']) ? $_COOKIE['remember_user'] : '';

// Il parametro di ritorno viene mantenuto sia nel form sia nel collegamento alla registrazione
$azione_form = 'login.php';
$link_registrazione = 'registrazione.php';

if ($pagina_ritorno !== 'home.php') {
    $azione_form .= '?ritorno=' . $pagina_ritorno;
    $link_registrazione .= '?ritorno=' . $pagina_ritorno;
}

/*
 * Il server controlla nuovamente i dati ricevuti
 * La validazione JavaScript impedisce soltanto l'invio immediato di campi vuoti
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $ricordami = isset($_POST['ricordami']);

    // In caso di errore viene mantenuto nel campo lo username appena digitato
    $username_precompilato = $username;

    if (empty($username) || empty($password)) {
        $errore_php = 'Compila entrambi i campi per accedere.';
    } else {

        // L'autenticazione richiede soltanto la lettura dei dati dell'utente
        $con = get_db_connection('lecture');

        /*
         * La query recupera l'identificativo, l'hash della password e il ruolo dell'utente
         * Lo username proviene dal form, quindi la query usa un prepared statement
         */
        $query = 'SELECT id, password, is_admin FROM utenti WHERE username = ?';
        $stmt = mysqli_prepare($con, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_bind_result($stmt, $id_db, $hash_db, $is_admin_db);

                // La password viene verificata confrontandola con l'hash salvato nel database
                if (mysqli_stmt_fetch($stmt) && password_verify($password, (string) $hash_db)) {

                    // Dopo il login viene rigenerato l'ID prima di salvare i dati dell'utente in sessione
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = (int) $id_db;
                    $_SESSION['username'] = $username;
                    $_SESSION['is_admin'] = (int) $is_admin_db;

                    /*
                     * Se l'utente ha scelto "Ricordami", lo username viene conservato per 72 ore
                     * Un nuovo accesso riuscito rinnova la scadenza mentre la password resta sempre esclusa
                     */
                    if ($ricordami) {
                        setcookie(
                            'remember_user',
                            $username,
                            time() + (72 * 3600),
                            '/'
                        );
                    } elseif (isset($_COOKIE['remember_user'])) {

                        // Un accesso riuscito senza la scelta "Ricordami" elimina l'eventuale cookie precedente
                        setcookie(
                            'remember_user',
                            '',
                            time() - 3600,
                            '/'
                        );
                    }

                    mysqli_stmt_close($stmt);
                    mysqli_close($con);

                    // Dopo il login viene ripristinato il percorso da cui l'utente aveva richiesto l'accesso
                    header('Location: ' . $pagina_ritorno);
                    exit;
                }

                // Il messaggio rimane volutamente generico senza indicare quale credenziale sia errata
                $errore_php = 'Credenziali non valide. Riprova.';
            } else {

                // Il dettaglio tecnico viene registrato nel log senza essere mostrato all'utente
                error_log('Errore durante l\'esecuzione della query di login: ' . mysqli_stmt_error($stmt));
                $errore_php = 'Errore durante l\'accesso. Riprova più tardi.';
            }

            mysqli_stmt_close($stmt);
        } else {
            error_log('Errore nella preparazione della query di login: ' . mysqli_error($con));
            $errore_php = 'Errore durante l\'accesso. Riprova più tardi.';
        }

        mysqli_close($con);
    }
}

require 'includes/header.php';
?>

<!-- Contenitore della pagina di autenticazione -->
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">

            <!-- Titolo principale della pagina di login -->
            <h1>Bentornato</h1>

            <p>Accedi per gestire adozioni e volontariato.</p>
        </div>

        <?php if (!empty($errore_php)) { ?>
            <div class="alert-danger" role="alert">
                <?php echo htmlspecialchars($errore_php, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php } ?>

        <!-- novalidate lascia il controllo lato client al codice Vanilla JavaScript -->
        <form
            action="<?php echo htmlspecialchars($azione_form, ENT_QUOTES, 'UTF-8'); ?>"
            method="POST"
            id="form-login"
            novalidate
        >
            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlspecialchars($username_precompilato, ENT_QUOTES, 'UTF-8'); ?>"
                    aria-describedby="err-username"
                >
                <span class="errore-js" id="err-username"></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    aria-describedby="err-password"
                >
                <span class="errore-js" id="err-password"></span>
            </div>

            <!-- Il cookie memorizza soltanto lo username e scade 72 ore dopo l'ultimo accesso riuscito che rinnova la scelta -->
            <div class="form-group checkbox-group">
                <input
                    type="checkbox"
                    id="ricordami"
                    name="ricordami"
                    <?php if (!empty($_COOKIE['remember_user'])) { echo 'checked'; } ?>
                >
                <label for="ricordami">Ricordami</label>
            </div>

            <span class="cookie-disclaimer">
                Selezionando la casella acconsenti all'uso di un cookie per ricordare il tuo username per 72 ore.
            </span>

            <div class="auth-actions">
                <button type="submit" class="btn-solid-dark">Accedi</button>

                <p class="form-switch-text">Non hai ancora un account?</p>

                <a
                    href="<?php echo htmlspecialchars($link_registrazione, ENT_QUOTES, 'UTF-8'); ?>"
                    class="btn-outline-dark"
                >
                    Registrati ora
                </a>
            </div>
        </form>
    </div>
</div>

<script>
/*
 * Validazione lato client del form di login
 * Il PHP ripete comunque il controllo prima di procedere con l'autenticazione
 */
document.getElementById('form-login').addEventListener('submit', function(event) {
    // let viene utilizzato perché il valore cambia se almeno un controllo fallisce
    let formValido = true;

    // I riferimenti agli elementi non vengono riassegnati durante la validazione
    const inputUser = document.getElementById('username');
    const inputPass = document.getElementById('password');
    const errUser = document.getElementById('err-username');
    const errPass = document.getElementById('err-password');

    // Prima di ogni tentativo vengono rimossi gli eventuali errori precedenti
    errUser.textContent = '';
    errPass.textContent = '';
    inputUser.classList.remove('input-error');
    inputPass.classList.remove('input-error');

    if (inputUser.value.trim() === '') {
        errUser.textContent = 'Inserisci il tuo username.';
        inputUser.classList.add('input-error');
        formValido = false;
    }

    if (inputPass.value === '') {
        errPass.textContent = 'Inserisci la tua password.';
        inputPass.classList.add('input-error');
        formValido = false;
    }

    // Soltanto un form che supera entrambi i controlli viene inviato al PHP
    if (!formValido) {
        event.preventDefault();
    }
});
</script>

<?php require 'includes/footer.php'; ?>