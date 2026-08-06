<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['username']) || !isset($_GET['data'])) {
    echo json_encode([]);
    exit;
}

require 'includes/db_config.php';

$data_richiesta = trim($_GET['data']);
$conteggio_fasce = [];

$con = get_db_connection('lecture');

if ($con) {
    // Estraiamo solo l'orario (es. "09:00:00") dai record in cui la data corrisponde
    $query = "SELECT TIME(fascia_oraria) as orario_turno, COUNT(*) as totale_iscritti 
              FROM turni_volontariato 
              WHERE DATE(fascia_oraria) = ? 
              GROUP BY orario_turno";
              
    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, "s", $data_richiesta);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $conteggio_fasce[$row['orario_turno']] = (int)$row['totale_iscritti'];
        }
        
        mysqli_stmt_close($stmt);
    }
    mysqli_close($con);
}

echo json_encode($conteggio_fasce);
?>