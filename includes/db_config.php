<?php
// includes/db_config.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function get_db_connection($role = 'lecture') {
    // Usiamo 127.0.0.1 invece di localhost per forzare il Mac 
    // a usare la porta corretta di MAMP (8889)
    $host = '127.0.0.1'; 
    $db_name = 'gattile_db';
    $port = 8889; // La porta MySQL di MAMP
    
    switch ($role) {
        case 'modifier':
            $user = 'modifier';
            $password = 'Str0ng#Admin9';
            break;
        case 'registrator':
            $user = 'registrator';
            $password = 'ToB31nsert?';
            break;
        case 'lecture':
        default:
            $user = 'lecture';
            $password = 'P@ssw0rd!';
            break;
    }

    try {
        // Aggiungiamo la $port come quinto parametro
        $con = mysqli_connect($host, $user, $password, $db_name, $port);
        mysqli_set_charset($con, "utf8mb4");
        return $con;
    } catch (mysqli_sql_exception $e) {
        error_log("Errore DB: " . $e->getMessage());
        die("<p style='color:red;'>Errore di connessione al database. MAMP MySQL è acceso?</p>");
    }
}
?>