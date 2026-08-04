<?php
/**
 * Pagina di Logout.
 * Distrugge in modo sicuro la sessione lato server e reindirizza alla home page.
 */

// Avvio la sessione per avervi accesso
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Svuoto completamente l'array globale di sessione
$_SESSION = array();

// Distruggo il cookie di sessione per sicurezza aggiuntiva 
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Distruggo definitivamente i dati della sessione sul server
session_destroy();

// Operazione conclusa: reindirizzo l'utente alla home
header("Location: home.php");
exit;
?>