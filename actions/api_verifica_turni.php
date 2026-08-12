<?php
/*
 * API per il controllo della disponibilità dei turni di volontariato
 * Viene interrogata dal JavaScript quando l'utente seleziona una data
 * e restituisce in JSON il numero di iscritti presenti nelle tre fasce
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Il controllo delle disponibilità è riservato agli utenti autenticati
if (!isset($_SESSION['username'])) {
    echo json_encode(array(
        'status' => 'error',
        'code' => 'AUTH_ERROR'
    ));
    exit;
}

$data_richiesta = isset($_GET['data']) ? trim($_GET['data']) : '';

// La data deve rispettare il formato prodotto dal campo input type="date"
$data_valida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_richiesta);

if ($data_valida) {
    $parti_data = explode('-', $data_richiesta);
    $anno = (int) $parti_data[0];
    $mese = (int) $parti_data[1];
    $giorno = (int) $parti_data[2];

    $data_valida = checkdate($mese, $giorno, $anno);
}

// Non vengono controllati turni relativi al giorno corrente o a date precedenti
if (!$data_valida || $data_richiesta < date('Y-m-d', strtotime('+1 day'))) {
    echo json_encode(array(
        'status' => 'error',
        'code' => 'INVALID_DATE'
    ));
    exit;
}

require __DIR__ . '/../includes/db_config.php';

// Le tre fasce partono da zero anche quando non esistono prenotazioni
$conteggio_fasce = array(
    '09:00:00' => 0,
    '13:00:00' => 0,
    '17:00:00' => 0
);

// Per questa operazione sono necessari esclusivamente privilegi di lettura
$con = get_db_connection('lecture');

$query = 'SELECT TIME(fascia_oraria), COUNT(*)
          FROM turni_volontariato
          WHERE DATE(fascia_oraria) = ?
          GROUP BY TIME(fascia_oraria)';

$stmt = mysqli_prepare($con, $query);

if (!$stmt) {
    error_log('Errore nella preparazione del controllo turni: ' . mysqli_error($con));
    mysqli_close($con);

    echo json_encode(array(
        'status' => 'error',
        'code' => 'QUERY_ERROR'
    ));
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $data_richiesta);

if (!mysqli_stmt_execute($stmt)) {
    error_log('Errore durante il controllo dei turni: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    echo json_encode(array(
        'status' => 'error',
        'code' => 'QUERY_ERROR'
    ));
    exit;
}

mysqli_stmt_bind_result($stmt, $orario_turno, $totale_iscritti);

// Ogni risultato viene associato alla relativa fascia del form
while (mysqli_stmt_fetch($stmt)) {
    /*
     * Le conversioni esplicite chiariscono anche all'analisi statica
     * che la chiave dell'array è una stringa e il conteggio un intero
     */
    $orario = (string) $orario_turno;
    $totale = (int) $totale_iscritti;

    if (array_key_exists($orario, $conteggio_fasce)) {
        $conteggio_fasce[$orario] = $totale;
    }
}

mysqli_stmt_close($stmt);
mysqli_close($con);

echo json_encode(array(
    'status' => 'success',
    'data' => $conteggio_fasce
));
?>