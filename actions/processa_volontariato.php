<?php
/*
 * Backend per la registrazione dei turni di volontariato
 * Il server ricontrolla autonomamente data, fasce e disponibilità
 * prima di effettuare qualsiasi inserimento definitivo nel database
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// L'inserimento dei turni è consentito soltanto agli utenti autenticati
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    echo json_encode(array(
        'status' => 'error',
        'code' => 'AUTH_ERROR',
        'message' => 'Devi essere autenticato.'
    ));
    exit;
}

// Questa action accetta esclusivamente richieste POST provenienti dal form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array(
        'status' => 'error',
        'code' => 'METHOD_NOT_ALLOWED',
        'message' => 'Metodo non consentito.'
    ));
    exit;
}

require __DIR__ . '/../includes/db_config.php';

$data_turno = isset($_POST['data_turno']) ? trim($_POST['data_turno']) : '';
$fasce = isset($_POST['fasce']) ? $_POST['fasce'] : array();
$utente_id = (int) $_SESSION['user_id'];

// Il server accetta solamente le tre fasce realmente presenti nel form
$fasce_consentite = array('09:00:00', '14:00:00', '18:00:00');
$dati_validi = true;

if ($data_turno === '' || !is_array($fasce) || count($fasce) === 0) {
    $dati_validi = false;
}

// Controllo del formato e dell'esistenza effettiva della data ricevuta
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_turno)) {
    $dati_validi = false;
} else {
    $parti_data = explode('-', $data_turno);
    $anno = (int) $parti_data[0];
    $mese = (int) $parti_data[1];
    $giorno = (int) $parti_data[2];

    if (!checkdate($mese, $giorno, $anno)) {
        $dati_validi = false;
    }
}

// Anche modificando manualmente il form non è possibile prenotare date passate
if ($data_turno < date('Y-m-d', strtotime('+1 day'))) {
    $dati_validi = false;
}

// Un valore arbitrario inviato al posto delle fasce previste viene rifiutato dal server
if (is_array($fasce)) {
    foreach ($fasce as $orario) {
        if (!is_string($orario) || !in_array($orario, $fasce_consentite, true)) {
            $dati_validi = false;
            break;
        }
    }
}

if (!$dati_validi) {
    echo json_encode(array(
        'status' => 'error',
        'code' => 'MISSING_DATA',
        'message' => 'Dati incompleti.'
    ));
    exit;
}

// Eventuali valori ripetuti vengono eliminati prima dei controlli sul database
$fasce = array_values(array_unique($fasce));

// L'inserimento richiede l'utente MySQL con privilegi di modifica
$con = get_db_connection('modifier');

if (!mysqli_begin_transaction($con)) {
    error_log('Impossibile avviare la transazione dei turni');

    mysqli_close($con);

    echo json_encode(array(
        'status' => 'error',
        'code' => 'SYSTEM_ERROR',
        'message' => 'Si è verificato un errore durante il salvataggio.'
    ));
    exit;
}

$errore_codice = '';
$errore_messaggio = '';

/*
 * Il primo controllo verifica quanti volontari risultano già registrati
 * La query viene preparata una volta e riutilizzata per tutte le fasce selezionate
 */
$query_limite = 'SELECT id
                 FROM turni_volontariato
                 WHERE fascia_oraria = ?
                 FOR UPDATE';

$stmt_limite = mysqli_prepare($con, $query_limite);

if (!$stmt_limite) {
    $errore_codice = 'SYSTEM_ERROR';
    $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
    error_log('Errore nella preparazione del controllo limite: ' . mysqli_error($con));
}

$datetime_controllo = '';

if ($stmt_limite) {
    mysqli_stmt_bind_param($stmt_limite, 's', $datetime_controllo);

    foreach ($fasce as $orario) {
        $datetime_controllo = $data_turno . ' ' . $orario;

        if (!mysqli_stmt_execute($stmt_limite)) {
            $errore_codice = 'SYSTEM_ERROR';
            $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
            error_log('Errore durante il controllo limite volontari: ' . mysqli_stmt_error($stmt_limite));
            break;
        }

        mysqli_stmt_store_result($stmt_limite);
        $numero_iscritti = mysqli_stmt_num_rows($stmt_limite);
        mysqli_stmt_free_result($stmt_limite);

        if ($numero_iscritti >= 2) {
            $errore_codice = 'LIMIT_EXCEEDED';
            $errore_messaggio = 'La fascia oraria selezionata è al completo.';
            break;
        }
    }

    mysqli_stmt_close($stmt_limite);
}

/*
 * Se il limite non è stato superato viene verificato che lo stesso utente
 * non abbia già registrato la medesima fascia nella stessa data
 */
if ($errore_codice === '') {
    $query_duplicato = 'SELECT id
                        FROM turni_volontariato
                        WHERE utente_id = ? AND fascia_oraria = ?
                        LIMIT 1';

    $stmt_duplicato = mysqli_prepare($con, $query_duplicato);

    if (!$stmt_duplicato) {
        $errore_codice = 'SYSTEM_ERROR';
        $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
        error_log('Errore nella preparazione del controllo duplicati: ' . mysqli_error($con));
    } else {
        $datetime_controllo = '';
        mysqli_stmt_bind_param($stmt_duplicato, 'is', $utente_id, $datetime_controllo);

        foreach ($fasce as $orario) {
            $datetime_controllo = $data_turno . ' ' . $orario;

            if (!mysqli_stmt_execute($stmt_duplicato)) {
                $errore_codice = 'SYSTEM_ERROR';
                $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
                error_log('Errore durante il controllo dei turni già prenotati: ' . mysqli_stmt_error($stmt_duplicato));
                break;
            }

            mysqli_stmt_store_result($stmt_duplicato);
            $gia_prenotato = mysqli_stmt_num_rows($stmt_duplicato) > 0;
            mysqli_stmt_free_result($stmt_duplicato);

            if ($gia_prenotato) {
                $errore_codice = 'ALREADY_BOOKED';
                $errore_messaggio = 'Hai già prenotato uno o più turni selezionati per questa data.';
                break;
            }
        }

        mysqli_stmt_close($stmt_duplicato);
    }
}

/*
 * Gli inserimenti vengono effettuati soltanto dopo che tutte le fasce
 * hanno superato sia il controllo del limite sia quello sui duplicati
 */
if ($errore_codice === '') {
    $query_insert = 'INSERT INTO turni_volontariato (utente_id, fascia_oraria) VALUES (?, ?)';
    $stmt_insert = mysqli_prepare($con, $query_insert);

    if (!$stmt_insert) {
        $errore_codice = 'SYSTEM_ERROR';
        $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
        error_log('Errore nella preparazione dell\'inserimento turno: ' . mysqli_error($con));
    } else {
        $datetime_inserimento = '';
        mysqli_stmt_bind_param($stmt_insert, 'is', $utente_id, $datetime_inserimento);

        foreach ($fasce as $orario) {
            $datetime_inserimento = $data_turno . ' ' . $orario;

            if (!mysqli_stmt_execute($stmt_insert)) {
                $errore_codice = 'SYSTEM_ERROR';
                $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
                error_log('Errore durante l\'inserimento del turno: ' . mysqli_stmt_error($stmt_insert));
                break;
            }
        }

        mysqli_stmt_close($stmt_insert);
    }
}

// Se anche una sola operazione non è valida nessun turno della richiesta viene salvato
if ($errore_codice !== '') {
    mysqli_rollback($con);
    mysqli_close($con);

    echo json_encode(array(
        'status' => 'error',
        'code' => $errore_codice,
        'message' => $errore_messaggio
    ));
    exit;
}

// Tutti i controlli e tutti gli inserimenti sono riusciti
if (!mysqli_commit($con)) {
    mysqli_rollback($con);
    error_log('Errore durante la conferma della registrazione dei turni');
    mysqli_close($con);

    echo json_encode(array(
        'status' => 'error',
        'code' => 'SYSTEM_ERROR',
        'message' => 'Si è verificato un errore durante il salvataggio.'
    ));
    exit;
}

mysqli_close($con);

echo json_encode(array(
    'status' => 'success',
    'message' => 'Turni registrati con successo.'
));
?>