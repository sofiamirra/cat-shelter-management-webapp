<?php
/*
 * Eliminazione di una visita prenotata dall'area personale
 * Il server verifica che la prenotazione appartenga realmente all'utente
 * e consente la cancellazione soltanto se la visita è ancora futura
 */

// L'action non include l'header comune e inizializza quindi direttamente la sessione
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

// L'id identifica la visita richiesta, mentre l'utente viene ricavato esclusivamente dalla sessione
$prenotazione_id = isset($_POST['prenotazione_id']) ? (int) $_POST['prenotazione_id'] : 0;
$utente_id = (int) $_SESSION['user_id'];

if ($prenotazione_id <= 0) {
    header('Location: ../area_personale.php?status=non_disponibile');
    exit;
}

require '../includes/db_config.php';

// La cancellazione modifica il database e utilizza quindi l'utente modifier
$con = get_db_connection('modifier');

/*
 * La query elimina la visita soltanto se id, proprietario e data soddisfano tutte le condizioni
 * Le associazioni in visita_gatti dipendono dal vincolo ON DELETE CASCADE definito nel database
 */
$query = 'DELETE FROM prenotazioni_visite
          WHERE id = ? AND utente_id = ? AND data_ora > NOW()';

// mysqli_prepare crea lo statement con i due placeholder che verranno valorizzati successivamente
$stmt = mysqli_prepare($con, $query);

if (!$stmt) {
    // Se la preparazione fallisce il dettaglio tecnico rimane nel log del server
    error_log('Errore nella preparazione della cancellazione della visita: ' . mysqli_error($con));
    mysqli_close($con);

    header('Location: ../area_personale.php?status=errore');
    exit;
}

/*
 * 'ii' indica che entrambi i parametri sono interi
 * In particolare utente_id non arriva dal browser ma dalla sessione autenticata
 */
mysqli_stmt_bind_param($stmt, 'ii', $prenotazione_id, $utente_id);

// La query preparata viene eseguita con i valori appena associati
if (!mysqli_stmt_execute($stmt)) {
    error_log('Errore durante la cancellazione della visita: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    header('Location: ../area_personale.php?status=errore');
    exit;
}

/*
 * affected_rows permette di sapere se il DELETE ha realmente trovato una riga valida
 * Zero righe evita di distinguere pubblicamente tra id inesistente, visita passata o visita appartenente ad altri
 */
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