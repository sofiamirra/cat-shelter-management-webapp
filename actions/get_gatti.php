<?php
/*
 * API utilizzata dal componente React della pagina Ospiti
 * Recupera i dati dei gatti con un utente MySQL di sola lettura
 * e restituisce al browser esclusivamente una risposta JSON
 */

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../includes/db_config.php';

// La risposta mantiene sempre la stessa struttura con stato, dati o messaggio di errore
$response = array(
    'status' => 'error',
    'message' => "Errore nell'esecuzione della query."
);

// Per questa operazione sono necessari esclusivamente privilegi di lettura
$con = get_db_connection('lecture');

/*
 * La query non contiene dati forniti dall'utente
 * Vengono recuperati soltanto i campi effettivamente utilizzati dal componente React
 */
$query = 'SELECT id, nome, sesso, eta, razza, colore_mantello, descrizione, data_arrivo
          FROM gatti
          ORDER BY data_arrivo DESC';

$stmt = mysqli_prepare($con, $query);

if ($stmt) {
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_bind_result(
            $stmt,
            $id,
            $nome,
            $sesso,
            $eta,
            $razza,
            $colore_mantello,
            $descrizione,
            $data_arrivo
        );

        $gatti = array();

        // Ogni riga del risultato viene trasformata in un elemento dell'array JSON
        while (mysqli_stmt_fetch($stmt)) {
            $gatti[] = array(
                'id' => (int) $id,
                'nome' => (string) $nome,
                'sesso' => (string) $sesso,
                'eta' => (int) $eta,
                'razza' => (string) $razza,
                'colore_mantello' => (string) $colore_mantello,
                'descrizione' => (string) $descrizione,
                'data_arrivo' => (string) $data_arrivo
            );
        }

        $response = array(
            'status' => 'success',
            'data' => $gatti
        );
    } else {
        // Il dettaglio tecnico viene registrato nel log senza essere inviato al browser
        error_log('Errore durante l\'esecuzione della query dei gatti: ' . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);
} else {
    error_log('Errore nella preparazione della query dei gatti: ' . mysqli_error($con));
}

mysqli_close($con);

// L'array PHP viene convertito nel formato JSON atteso dal componente React
echo json_encode($response);
?>