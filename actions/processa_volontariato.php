<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'code' => 'AUTH_ERROR', 'message' => 'Devi essere autenticato.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'code' => 'METHOD_NOT_ALLOWED', 'message' => 'Metodo non consentito.']);
    exit;
}

require 'includes/db_config.php';

$data_turno = trim($_POST['data_turno'] ?? '');
$fasce = $_POST['fasce'] ?? [];
$username_session = $_SESSION['username'];

if (empty($data_turno) || empty($fasce)) {
    echo json_encode(['status' => 'error', 'code' => 'MISSING_DATA', 'message' => 'Dati incompleti.']);
    exit;
}

$con = get_db_connection('modifier');
if (!$con) {
    echo json_encode(['status' => 'error', 'code' => 'DB_CONNECTION', 'message' => 'Errore di connessione.']);
    exit;
}

mysqli_begin_transaction($con);

try {
    // Estrazione ID utente
    $utente_id = null;
    $query_user = "SELECT id FROM utenti WHERE username = ?";
    if ($stmt_user = mysqli_prepare($con, $query_user)) {
        mysqli_stmt_bind_param($stmt_user, "s", $username_session);
        mysqli_stmt_execute($stmt_user);
        mysqli_stmt_bind_result($stmt_user, $utente_id);
        mysqli_stmt_fetch($stmt_user);
        mysqli_stmt_close($stmt_user);
    }

    if (!$utente_id) {
        throw new Exception("Utente non trovato.", 1);
    }

    // Controllo Integrità (Limite 2 volontari)
    $query_check = "SELECT COUNT(*) FROM turni_volontariato WHERE fascia_oraria = ? FOR UPDATE";
    
    foreach ($fasce as $orario) {
        $datetime_completo = $data_turno . ' ' . $orario;
        $iscritti = 0;
        
        if ($stmt_check = mysqli_prepare($con, $query_check)) {
            mysqli_stmt_bind_param($stmt_check, "s", $datetime_completo);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_bind_result($stmt_check, $iscritti);
            mysqli_stmt_fetch($stmt_check);
            mysqli_stmt_close($stmt_check);
        }
        
        if ($iscritti >= 2) {
            throw new Exception("La fascia oraria selezionata è al completo.", 2);
        }
    }

    // Inserimento
    $query_insert = "INSERT INTO turni_volontariato (utente_id, fascia_oraria) VALUES (?, ?)";
    if ($stmt_insert = mysqli_prepare($con, $query_insert)) {
        foreach ($fasce as $orario) {
            $datetime_completo = $data_turno . ' ' . $orario;
            mysqli_stmt_bind_param($stmt_insert, "is", $utente_id, $datetime_completo);
            
            if (!mysqli_stmt_execute($stmt_insert)) {
                if (mysqli_errno($con) == 1062) { 
                    throw new Exception("Hai già prenotato uno o più turni selezionati per questa data.", 3);
                } else {
                    throw new Exception("Si è verificato un errore durante il salvataggio.", 4);
                }
            }
        }
        mysqli_stmt_close($stmt_insert);
    }

    mysqli_commit($con);
    echo json_encode(['status' => 'success', 'message' => 'Turni registrati con successo.']);

} catch (Exception $e) {
    mysqli_rollback($con);
    
    $errorCode = 'SYSTEM_ERROR';
    $message = $e->getMessage();
    
    if ($e->getCode() == 2) {
        $errorCode = 'LIMIT_EXCEEDED';
    } elseif ($e->getCode() == 3 || $e->getCode() == 1062) { 
        $errorCode = 'ALREADY_BOOKED';
        $message = "Hai già prenotato uno o più turni selezionati per questa data.";
    }
    
    // Il JSON invia il codice per le direttive del professore, ma il messaggio è pulito
    echo json_encode(['status' => 'error', 'code' => $errorCode, 'message' => $message]);
}

mysqli_close($con);
?>