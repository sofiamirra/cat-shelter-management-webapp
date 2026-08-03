<?php
/**
 * Configurazione della connessione al database MySQL.
 * Gestisce la connessione differenziata in base al ruolo utente.
 */

// Abilita la gestione rigorosa delle eccezioni mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function get_db_connection($role = 'lecture') {
    // Parametri di connessione locali per il server MAMP
    $host = '127.0.0.1';
    $db_name = 'gattile_db';
    $port = 8889; 
    
    // Seleziona le credenziali in base al tipo di accesso
    switch ($role) {
        case 'modifier':
            // Privilegi di lettura e scrittura 
            $user = 'modifier';
            $password = 'Str0ng#Admin9';
            break;
        case 'registrator':
            // Privilegi limitati al solo inserimento nella tabella utenti
            $user = 'registrator';
            $password = 'ToB31nsert?';
            break;
        case 'lecture':
        default:
            // Privilegi di sola lettura
            $user = 'lecture';
            $password = 'P@ssw0rd!';
            break;
    }

    try {
        // Tentativo di apertura della connessione con i parametri specificati
        $con = mysqli_connect($host, $user, $password, $db_name, $port);
        // Imposta la codifica della connessione a UTF-8
        mysqli_set_charset($con, "utf8mb4");
        return $con;
    } catch (mysqli_sql_exception $e) {
        // Gestione degli errori per evitare la propagazione di eccezioni non gestite
        error_log("Errore di connessione al database: " . $e->getMessage());
        die("Impossibile connettersi al database.");
    }
}
?>

