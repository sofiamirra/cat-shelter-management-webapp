<?php
/*
 * Pagina di logout
 * Elimina i dati della sessione corrente e riporta l'utente alla Home
 */

// Per eliminare correttamente una sessione esistente è necessario inizializzarla prima
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Vengono rimossi tutti i dati dell'utente conservati nella sessione corrente
$_SESSION = array();

/*
 * Se PHP utilizza un cookie per identificare la sessione,
 * viene eliminato utilizzando gli stessi parametri con cui era stato creato
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

// I dati associati alla sessione vengono infine eliminati anche lato server
session_destroy();

/*
 * Il cookie remember_user non viene cancellato
 * perché deve continuare a precompilare lo username fino alla propria scadenza di 72 ore
 */

// Terminato il logout, la navigazione riparte dalla pagina iniziale
header('Location: home.php');
exit;
?>