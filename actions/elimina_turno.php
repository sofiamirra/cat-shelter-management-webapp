<?php
/*
 * Eliminazione di un turno di volontariato dall'area personale
 * Il turno viene cancellato soltanto se appartiene all'utente autenticato
 * ed è ancora relativo a una fascia futura
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

// Recupero degli identificativi utilizzati per verificare la proprietà del turno
$turno_id = isset($_POST['turno_id']) ? (int) $_POST['turno_id'] : 0;
$utente_id = (int) $_SESSION['user_id'];

if ($turno_id <= 0) {
    header('Location: ../area_personale.php?status=non_disponibile');
    exit;
}

require __DIR__ . '/../includes/db_config.php';

// La cancellazione richiede l'utente MySQL con privilegi di modifica
$con = get_db_connection('modifier');

/*
 * L'identificativo dell'utente viene verificato direttamente nella query
 * in modo che un account non possa eliminare turni appartenenti ad altri utenti
 * I turni già trascorsi non possono essere modificati
 */
$query = 'DELETE FROM turni_volontariato
          WHERE id = ? AND utente_id = ? AND fascia_oraria > NOW()';

$stmt = mysqli_prepare($con, $query);

if (!$stmt) {
    error_log('Errore nella preparazione della cancellazione del turno: ' . mysqli_error($con));
    mysqli_close($con);
    header('Location: ../area_personale.php?status=errore');
    exit;
}

mysqli_stmt_bind_param($stmt, 'ii', $turno_id, $utente_id);

if (!mysqli_stmt_execute($stmt)) {
    error_log('Errore durante la cancellazione del turno: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    header('Location: ../area_personale.php?status=errore');
    exit;
}

// Il numero di righe eliminate permette di distinguere successo e richiesta non valida
$righe_eliminate = mysqli_stmt_affected_rows($stmt);

mysqli_stmt_close($stmt);
mysqli_close($con);

if ($righe_eliminate === 1) {
    header('Location: ../area_personale.php?status=turno_eliminato');
} else {
    header('Location: ../area_personale.php?status=non_disponibile');
}

exit;
?>