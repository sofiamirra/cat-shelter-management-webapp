<?php
/*
 * Configurazione centralizzata delle connessioni MySQL
 * Le credenziali cambiano in base ai privilegi necessari alla pagina
 */

// Gli errori MySQLi vengono gestiti esplicitamente nei diversi punti del codice
mysqli_report(MYSQLI_REPORT_OFF);

function get_db_connection($role = 'lecture')
{
    // Parametri della connessione locale MAMP
    $host = '127.0.0.1';
    $db_name = 'gattile_db';
    $port = 8889;

    /*
     * Ogni operazione utilizza l'utente con i privilegi minimi necessari
     * Un ruolo non riconosciuto ricade sul profilo di sola lettura
     */
    switch ($role) {
        case 'modifier':
            // Utente con privilegi di lettura e modifica
            $user = 'modifier';
            $password = 'Str0ng#Admin9';
            break;

        case 'registrator':
            // Utente dedicato esclusivamente alla registrazione dei nuovi utenti
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

    $con = mysqli_connect($host, $user, $password, $db_name, $port);

    if (!$con) {

        // Il dettaglio tecnico viene registrato nel log mentre all'utente resta un messaggio generico
        error_log(
            'Errore di connessione MySQL per l\'utente '
            . $user
            . ': '
            . mysqli_connect_error()
        );

        die('Impossibile connettersi al database');
    }

    // Imposta la codifica della connessione al database (UTF-8 completo)
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