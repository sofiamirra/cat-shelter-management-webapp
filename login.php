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
 * Il parametro ritorno permette di tornare alla pagina da cui è stato richiesto
 * l'accesso, ma vengono accettate solamente pagine previste dal sito
 */
$pagina_ritorno = 'home.php';
$pagine_consentite = array('ospiti.php', 'volontariato.php', 'area_personale.php');

if (isset($_GET['ritorno']) && in_array($_GET['ritorno'], $pagine_consentite, true)) {
    $pagina_ritorno = $_GET['ritorno'];
}

// Se l'utente è già autenticato viene inviato direttamente alla pagina prevista
if (isset($_SESSION['username'])) {
    header('Location: ' . $pagina_ritorno);
    exit;
}

require 'includes/db_config.php';

$errore_php = '';

/*
 * Lo username viene inizialmente recuperato dal cookie, se ancora presente
 * La password non viene invece mai memorizzata o precompilata
 */
$username_precompilato = isset($_COOKIE['remember_user']) ? $_COOKIE['remember_user'] : '';

// Il parametro di ritorno viene mantenuto anche durante l'invio del form
$azione_form = 'login.php';
$link_registrazione = 'registrazione.php';

if ($pagina_ritorno !== 'home.php') {
    $azione_form .= '?ritorno=' . $pagina_ritorno;
    $link_registrazione .= '?ritorno=' . $pagina_ritorno;
}

// Il server verifica nuovamente i dati anche se il form viene controllato in JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $ricordami = isset($_POST['ricordami']);

    // In caso di errore viene mantenuto nel campo lo username appena digitato
    $username_precompilato = $username;

    if (empty($username) || empty($password)) {
        $errore_php = 'Compila entrambi i campi per accedere.';
    } else {
        // Per il login è sufficiente la connessione con privilegi di sola lettura
        $con = get_db_connection('lecture');

        /*
         * La query recupera l'hash della password e il tipo di utente
         * Lo username viene passato tramite prepared statement
         */
        $query = 'SELECT id, password, is_admin FROM utenti WHERE username = ?';
        $stmt = mysqli_prepare($con, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_bind_result($stmt, $id_db, $hash_db, $is_admin_db);

                if (mysqli_stmt_fetch($stmt) && password_verify($password, (string) $hash_db)) {
                    /*
                     * Dopo l'autenticazione viene rigenerato l'identificativo della sessione
                     * e vengono salvati i dati necessari nelle pagine successive
                     */
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $id_db;
                    $_SESSION['username'] = $username;
                    $_SESSION['is_admin'] = $is_admin_db;

                    /*
                     * Se richiesto, lo username dell'accesso riuscito viene conservato
                     * per 72 ore. La password resta sempre esclusa dal cookie
                     */
                    if ($ricordami) {
                        setcookie('remember_user', $username, time() + (72 * 3600), '/');
                    } elseif (isset($_COOKIE['remember_user'])) {
                        // Se la casella non è selezionata viene eliminato un eventuale cookie precedente
                        setcookie('remember_user', '', time() - 3600, '/');
                    }

                    mysqli_stmt_close($stmt);
                    mysqli_close($con);

                    // L'accesso concluso con successo riporta l'utente alla pagina prevista
                    header('Location: ' . $pagina_ritorno);
                    exit;
                }

                // Non viene specificato se sia errato lo username o la password
                $errore_php = 'Credenziali non valide. Riprova.';
            } else {
                // Il dettaglio tecnico viene scritto nel log e non mostrato all'utente
                error_log('Errore durante l\'esecuzione della query di login: ' . mysqli_stmt_error($stmt));
                $errore_php = 'Credenziali non valide. Riprova.';
            }

            mysqli_stmt_close($stmt);
        } else {
            error_log('Errore nella preparazione della query di login: ' . mysqli_error($con));
            $errore_php = 'Credenziali non valide. Riprova.';
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
            <h2>Bentornato</h2>
            <p>Accedi per gestire adozioni e volontariato.</p>
        </div>

        <?php if (!empty($errore_php)) { ?>
            <div class="auth-alert-danger"><?php echo htmlspecialchars($errore_php, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>

        <!-- Il controllo lato client viene eseguito in Vanilla JavaScript -->
        <form action="<?php echo htmlspecialchars($azione_form, ENT_QUOTES, 'UTF-8'); ?>" method="POST" id="form-login">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username_precompilato, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="errore-js" id="err-username"></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <span class="errore-js" id="err-password"></span>
            </div>

            <!-- Il cookie memorizza soltanto lo username e scade dopo 72 ore -->
            <div class="form-group checkbox-group">
                <input type="checkbox" id="ricordami" name="ricordami" <?php if (!empty($_COOKIE['remember_user'])) { echo 'checked'; } ?>>
                <label for="ricordami" class="checkbox-label">Ricordami</label>
            </div>
            <span class="cookie-disclaimer">Selezionando la casella acconsenti all'uso di un cookie per ricordare il tuo username per 72 ore.</span>
            <div class="text-center mt-2">
                <button type="submit" class="btn-solid-dark w-100">Accedi</button>
                <p class="form-switch-text">Non hai ancora un account?</p>
                <a href="<?php echo htmlspecialchars($link_registrazione, ENT_QUOTES, 'UTF-8'); ?>" class="btn-outline-dark">Registrati ora</a>
            </div>
        </form>
    </div>
</div>

<script>
/*
 * Validazione lato client del form di login
 * Il controllo impedisce l'invio quando username o password sono vuoti
 */
document.getElementById('form-login').addEventListener('submit', function(event) {
    let formValido = true;

    const inputUser = document.getElementById('username');
    const inputPass = document.getElementById('password');
    const errUser = document.getElementById('err-username');
    const errPass = document.getElementById('err-password');

    // Vengono rimossi gli eventuali errori prodotti da un tentativo precedente
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

    // Il form viene inviato al PHP solamente quando entrambi i campi sono compilati
    if (!formValido) {
        event.preventDefault();
    }
});
</script>

<?php require 'includes/footer.php'; ?>