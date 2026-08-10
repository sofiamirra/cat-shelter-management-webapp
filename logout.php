<?php
/*
 * Pagina di logout
 * Elimina i dati della sessione corrente e riporta l'utente alla home
 */

// Per poter eliminare una sessione esistente è necessario aprirla prima
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Le variabili memorizzate nella sessione vengono rimosse dall'array corrente
$_SESSION = array();

/*
 * Se PHP utilizza un cookie per identificare la sessione, viene eliminato
 * usando gli stessi parametri con cui era stato creato
 */
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// I dati associati alla sessione vengono infine eliminati dal server
session_destroy();

// Terminato il logout, la navigazione riparte dalla pagina iniziale
header('Location: home.php');
exit;
?>