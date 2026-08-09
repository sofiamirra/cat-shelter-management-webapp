<?php
/**
 * Pagina di Login
 * Gestisce l'autenticazione degli utenti e implementa la logica del Cookie "Ricordami".
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Se l'utente è già loggato, lo rimandiamo alla home
if (isset($_SESSION['username'])) {
    header("Location: home.php");
    exit;
}

require 'includes/db_config.php';

$errore_php = "";

// Gestione del form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $ricordami = isset($_POST['ricordami']) ? true : false;

    if (empty($username) || empty($password)) {
        $errore_php = "Compila entrambi i campi per accedere.";
    } else {
        $con = get_db_connection('lecture');
        
        $query = "SELECT id, password, is_admin FROM utenti WHERE username = ?";
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $id_db, $hash_db, $is_admin_db);
            
            if (mysqli_stmt_fetch($stmt)) {
                if (password_verify($password, $hash_db)) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $id_db;
                    $_SESSION['username'] = $username;
                    $_SESSION['is_admin'] = $is_admin_db;
                    
                    if ($ricordami) {
                        setcookie("remember_user", $username, time() + (72 * 3600), "/");
                    } else {
                        if(isset($_COOKIE['remember_user'])) {
                            setcookie("remember_user", "", time() - 3600, "/");
                        }
                    }
                    header("Location: home.php");
                    exit;
                } else {
                    $errore_php = "Credenziali non valide. Riprova.";
                }
            } else {
                $errore_php = "Credenziali non valide. Riprova.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_close($con);
    }
}

$username_precompilato = isset($_COOKIE['remember_user']) ? $_COOKIE['remember_user'] : "";

require 'includes/header.php';
?>

<!-- Wrapper che attiva il CSS della Card -->
<div class="auth-wrapper">
    <div class="auth-card">
        
        <div class="auth-header">
            <h2>Bentornato</h2>
            <p>Accedi per gestire adozioni e volontariato.</p>
        </div>

        <?php
        if (!empty($errore_php)) {
            echo "<div class='auth-alert'>$errore_php</div>";
        }
        ?>

        <form action="login.php" method="POST" id="form-login">
            
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

            <!-- Checkbox pulito senza inline CSS -->
            <div class="form-group checkbox-group">
                <input type="checkbox" id="ricordami" name="ricordami" <?php if(!empty($username_precompilato)) echo 'checked'; ?>>
                <label for="ricordami" class="checkbox-label">Ricordami per 72 ore</label>
            </div>

            <!-- Bottoni centrati -->
            <div class="text-center mt-2">
                <button type="submit" class="btn-solid-dark w-100">Accedi</button>
                <p class="form-switch-text">Non hai ancora un account?</p>
                <a href="registrazione.php" class="btn-outline-dark">Registrati ora</a>
            </div>
        </form>

    </div>
</div>

<script>
document.getElementById('form-login').addEventListener('submit', function(event) {
    let formValido = true;
    const inputUser = document.getElementById('username');
    const inputPass = document.getElementById('password');
    const errUser = document.getElementById('err-username');
    const errPass = document.getElementById('err-password');

    errUser.textContent = "";
    errPass.textContent = "";
    inputUser.classList.remove('input-error');
    inputPass.classList.remove('input-error');

    if (inputUser.value.trim() === "") {
        errUser.textContent = "Inserisci il tuo username.";
        inputUser.classList.add('input-error');
        formValido = false;
    }

    if (inputPass.value === "") {
        errPass.textContent = "Inserisci la tua password.";
        inputPass.classList.add('input-error');
        formValido = false;
    }

    if (!formValido) {
        event.preventDefault();
    }
});
</script>

<?php require 'includes/footer.php'; ?>