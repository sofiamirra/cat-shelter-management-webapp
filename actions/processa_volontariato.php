<?php
/*
 * Backend per la registrazione dei turni di volontariato
 * Il server ricontrolla autonomamente data, fasce e disponibilità
 * prima di effettuare qualsiasi inserimento definitivo nel database
 */

// Questa action non include l'header comune e inizializza quindi direttamente la sessione
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// La risposta dell'action viene sempre restituita al JavaScript in formato JSON
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

require '../includes/db_config.php';

// I dati ricevuti vengono recuperati prima di ripetere sul server tutti i controlli necessari
$data_turno = isset($_POST['data_turno']) ? trim($_POST['data_turno']) : '';
$fasce = isset($_POST['fasce']) ? $_POST['fasce'] : array();
$utente_id = (int) $_SESSION['user_id'];

// Il server accetta solamente le tre fasce realmente previste dal form
$fasce_consentite = array('09:00:00', '13:00:00', '17:00:00');
$dati_validi = true;

// Devono essere presenti una data e almeno una fascia oraria
if ($data_turno === '' || !is_array($fasce) || count($fasce) === 0) {
    $dati_validi = false;
}

/*
 * Prima viene controllato il formato YYYY-MM-DD
 * checkdate verifica inoltre che giorno, mese e anno costituiscano una data reale
 */
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

// Il controllo viene ripetuto lato server anche se il browser impedisce già la scelta di date precedenti
if ($data_turno < date('Y-m-d', strtotime('+1 day'))) {
    $dati_validi = false;
}

// Valori arbitrari inviati modificando manualmente il form vengono rifiutati
if (is_array($fasce)) {
    foreach ($fasce as $orario) {
        if (!is_string($orario) || !in_array($orario, $fasce_consentite, true)) {
            $dati_validi = false;
            break;
        }
    }
}

// I dati non validi non raggiungono mai le operazioni di scrittura sul database
if (!$dati_validi) {
    echo json_encode(array(
        'status' => 'error',
        'code' => 'MISSING_DATA',
        'message' => 'Dati incompleti.'
    ));
    exit;
}

// Eventuali fasce ripetute vengono eliminate prima dei controlli sul database
$fasce = array_values(array_unique($fasce));

// L'inserimento richiede l'utente MySQL con privilegi di modifica
$con = get_db_connection('modifier');

// Queste variabili raccolgono l'eventuale errore da restituire al JavaScript
$errore_codice = '';
$errore_messaggio = '';

/*
 * Il server ricontrolla quanti volontari sono già presenti
 * in ciascuna delle fasce selezionate
 */
$query_limite = 'SELECT COUNT(*)
                 FROM turni_volontariato
                 WHERE fascia_oraria = ?';

$stmt_limite = mysqli_prepare($con, $query_limite);

if (!$stmt_limite) {
    $errore_codice = 'SYSTEM_ERROR';
    $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
    error_log('Errore nella preparazione del controllo limite: ' . mysqli_error($con));
} else {
    $datetime_controllo = '';
    $numero_iscritti = 0;

    mysqli_stmt_bind_param($stmt_limite, 's', $datetime_controllo);

    foreach ($fasce as $orario) {
        // Data e fascia vengono riunite nello stesso formato DATETIME utilizzato nel database
        $datetime_controllo = $data_turno . ' ' . $orario;

        if (!mysqli_stmt_execute($stmt_limite)) {
            $errore_codice = 'SYSTEM_ERROR';
            $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
            error_log('Errore durante il controllo limite volontari: ' . mysqli_stmt_error($stmt_limite));
            break;
        }

        mysqli_stmt_bind_result($stmt_limite, $numero_iscritti);

        if (!mysqli_stmt_fetch($stmt_limite)) {
            $errore_codice = 'SYSTEM_ERROR';
            $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
            error_log('Errore durante la lettura del numero di volontari');
            break;
        }

        // La consegna permette un massimo di due volontari per ciascuna fascia
        if ((int) $numero_iscritti >= 2) {
            $errore_codice = 'LIMIT_EXCEEDED';
            $errore_messaggio = 'La fascia oraria selezionata è al completo.';
            break;
        }
    }

    mysqli_stmt_close($stmt_limite);
}

/*
 * Se le fasce hanno ancora disponibilità viene verificato
 * che lo stesso utente non abbia già prenotato uno dei turni richiesti
 */
if ($errore_codice === '') {
    $query_duplicato = 'SELECT COUNT(*)
                        FROM turni_volontariato
                        WHERE utente_id = ? AND fascia_oraria = ?';

    $stmt_duplicato = mysqli_prepare($con, $query_duplicato);

    if (!$stmt_duplicato) {
        $errore_codice = 'SYSTEM_ERROR';
        $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
        error_log('Errore nella preparazione del controllo duplicati: ' . mysqli_error($con));
    } else {
        $datetime_controllo = '';
        $numero_duplicati = 0;

        mysqli_stmt_bind_param($stmt_duplicato, 'is', $utente_id, $datetime_controllo);

        foreach ($fasce as $orario) {
            $datetime_controllo = $data_turno . ' ' . $orario;

            if (!mysqli_stmt_execute($stmt_duplicato)) {
                $errore_codice = 'SYSTEM_ERROR';
                $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
                error_log('Errore durante il controllo dei turni già prenotati: ' . mysqli_stmt_error($stmt_duplicato));
                break;
            }

            mysqli_stmt_bind_result($stmt_duplicato, $numero_duplicati);

            if (!mysqli_stmt_fetch($stmt_duplicato)) {
                $errore_codice = 'SYSTEM_ERROR';
                $errore_messaggio = 'Si è verificato un errore durante il salvataggio.';
                error_log('Errore durante la lettura dei turni già prenotati');
                break;
            }

            if ((int) $numero_duplicati > 0) {
                $errore_codice = 'ALREADY_BOOKED';
                $errore_messaggio = 'Hai già prenotato uno o più turni selezionati per questa data.';
                break;
            }
        }

        mysqli_stmt_close($stmt_duplicato);
    }
}

// Se uno dei controlli non è superato non viene effettuato alcun inserimento
if ($errore_codice !== '') {
    mysqli_close($con);

    echo json_encode(array(
        'status' => 'error',
        'code' => $errore_codice,
        'message' => $errore_messaggio
    ));
    exit;
}

/*
 * La transazione rende unico il salvataggio delle fasce selezionate
 * Se un inserimento fallisce vengono annullati anche quelli precedenti della stessa richiesta
 */
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

// Se un inserimento fallisce vengono annullati tutti i turni della richiesta
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

// Tutti gli inserimenti sono riusciti e vengono confermati
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

// La risposta positiva viene intercettata dal JavaScript senza ricaricare la pagina
echo json_encode(array(
    'status' => 'success',
    'message' => 'Turni registrati con successo.'
));
?>