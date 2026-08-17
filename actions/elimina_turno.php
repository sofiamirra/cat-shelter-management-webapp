<?php
/*
 * Eliminazione di un turno di volontariato dall'area personale
 * Il turno viene cancellato soltanto se appartiene all'utente autenticato
 * ed è ancora relativo a una fascia futura
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

// L'id identifica il turno richiesto, mentre l'utente viene ricavato esclusivamente dalla sessione
$turno_id = isset($_POST['turno_id']) ? (int) $_POST['turno_id'] : 0;
$utente_id = (int) $_SESSION['user_id'];

if ($turno_id <= 0) {
    header('Location: ../area_personale.php?status=non_disponibile');
    exit;
}

require '../includes/db_config.php';

// La cancellazione modifica il database e utilizza quindi l'utente modifier
$con = get_db_connection('modifier');

/*
 * L'id ricevuto dal form non è sufficiente per autorizzare la cancellazione
 * La query verifica anche il proprietario del turno e che la fascia sia ancora futura
 */
$query = 'DELETE FROM turni_volontariato
          WHERE id = ? AND utente_id = ? AND fascia_oraria > NOW()';

// Lo statement viene preparato mantenendo separata la query dai valori ricevuti
$stmt = mysqli_prepare($con, $query);

if (!$stmt) {
    // Un errore di preparazione viene registrato nel log prima di chiudere la connessione
    error_log('Errore nella preparazione della cancellazione del turno: ' . mysqli_error($con));
    mysqli_close($con);

    header('Location: ../area_personale.php?status=errore');
    exit;
}

/*
 * I due placeholder vengono associati come interi
 * turno_id proviene dal form mentre utente_id proviene dalla sessione autenticata
 */
mysqli_stmt_bind_param($stmt, 'ii', $turno_id, $utente_id);

// execute esegue il DELETE utilizzando i valori precedentemente associati
if (!mysqli_stmt_execute($stmt)) {
    error_log('Errore durante la cancellazione del turno: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    header('Location: ../area_personale.php?status=errore');
    exit;
}

/*
 * affected_rows restituisce il numero di righe realmente eliminate
 * Una riga indica il successo, mentre zero indica che il turno non era cancellabile o non apparteneva all'utente
 */
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