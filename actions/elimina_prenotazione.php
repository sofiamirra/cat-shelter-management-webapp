<?php
/*
 * Eliminazione di una visita prenotata dall'area personale
 * Il server verifica che la prenotazione appartenga realmente all'utente
 * e consente la cancellazione soltanto se la visita è ancora futura
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// L'operazione è disponibile soltanto agli utenti autenticati
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header('Location: ../login.php?ritorno=area_personale.php');
    exit;
}

// La cancellazione deve essere richiesta esclusivamente tramite il form POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../area_personale.php');
    exit;
}

// Recupero e conversione dell'identificativo ricevuto dal form
$prenotazione_id = isset($_POST['prenotazione_id']) ? (int) $_POST['prenotazione_id'] : 0;
$utente_id = (int) $_SESSION['user_id'];

if ($prenotazione_id <= 0) {
    header('Location: ../area_personale.php?status=non_disponibile');
    exit;
}

require __DIR__ . '/../includes/db_config.php';

// La cancellazione richiede l'utente MySQL con privilegi di modifica
$con = get_db_connection('modifier');

/*
 * La condizione sull'utente impedisce di cancellare prenotazioni appartenenti ad altri account
 * La condizione sulla data impedisce di modificare visite già trascorse
 * Le righe di visita_gatti vengono eliminate automaticamente dal vincolo ON DELETE CASCADE
 */
$query = 'DELETE FROM prenotazioni_visite
          WHERE id = ? AND utente_id = ? AND data_ora > NOW()';

$stmt = mysqli_prepare($con, $query);

if (!$stmt) {
    error_log('Errore nella preparazione della cancellazione della visita: ' . mysqli_error($con));
    mysqli_close($con);
    header('Location: ../area_personale.php?status=errore');
    exit;
}

mysqli_stmt_bind_param($stmt, 'ii', $prenotazione_id, $utente_id);

if (!mysqli_stmt_execute($stmt)) {
    error_log('Errore durante la cancellazione della visita: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    header('Location: ../area_personale.php?status=errore');
    exit;
}

// Una riga eliminata indica che la prenotazione era valida e apparteneva all'utente
$righe_eliminate = mysqli_stmt_affected_rows($stmt);

mysqli_stmt_close($stmt);
mysqli_close($con);

if ($righe_eliminate === 1) {
    header('Location: ../area_personale.php?status=visita_eliminata');
} else {
    header('Location: ../area_personale.php?status=non_disponibile');
}

exit;
?>