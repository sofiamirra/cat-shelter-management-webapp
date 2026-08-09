<?php
/*
 * Configurazione centralizzata delle connessioni MySQL
 * Le credenziali cambiano in base ai privilegi necessari alla pagina
 */

// Gli errori MySQLi vengono controllati direttamente nel codice
mysqli_report(MYSQLI_REPORT_OFF);

function get_db_connection($role = 'lecture')
{
    // Parametri della connessione locale MAMP
    $host = '127.0.0.1';
    $db_name = 'gattile_db';
    $port = 8889;

    // Selezione dell'utente MySQL in base alle operazioni richieste
    switch ($role) {
        case 'modifier':
            // Utente con privilegi di lettura e modifica
            $user = 'modifier';
            $password = 'Str0ng#Admin9';
            break;

        case 'registrator':
            // Utente dedicato all'inserimento dei nuovi utenti
            $user = 'registrator';
            $password = 'ToB31nsert?';
            break;

        case 'lecture':
        default:
            // Utente con privilegi di sola lettura
            $user = 'lecture';
            $password = 'P@ssw0rd!';
            break;
    }

    // Apertura della connessione con le credenziali selezionate
    $con = mysqli_connect($host, $user, $password, $db_name, $port);

    if (!$con) {
        // Il dettaglio tecnico viene registrato nel log del server
        error_log(
            'Errore di connessione MySQL per l\'utente '
            . $user
            . ': '
            . mysqli_connect_error()
        );

        die('Impossibile connettersi al database');
    }

    // Imposta UTF-8 completo per la corretta gestione dei dati testuali
    if (!mysqli_set_charset($con, 'utf8mb4')) {
        error_log(
            'Errore durante l\'impostazione della codifica: '
            . mysqli_error($con)
        );

        mysqli_close($con);
        die('Impossibile configurare correttamente la connessione al database');
    }

    return $con;
}
?>