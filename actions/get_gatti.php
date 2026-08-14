<?php
/*
 * API utilizzata dal componente React della pagina Ospiti
 * Recupera i dati dei gatti con un utente MySQL di sola lettura
 * e restituisce al browser esclusivamente una risposta JSON
 */

// L'endpoint produce JSON e non una pagina HTML
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../includes/db_config.php';

/*
 * La risposta parte da uno stato di errore
 * e viene sostituita solo se lettura e costruzione dei dati riescono
 */
$response = array(
    'status' => 'error',
    'message' => "Errore nell'esecuzione della query."
);

// La galleria richiede esclusivamente privilegi di lettura
$con = get_db_connection('lecture');

/*
 * La query non contiene dati forniti dall'utente e quindi non richiede bind_param
 * Vengono recuperati soltanto i campi utilizzati dal componente React
 */
$query = 'SELECT id, nome, sesso, eta, razza, colore_mantello, descrizione, data_arrivo
          FROM gatti
          ORDER BY data_arrivo DESC';

$stmt = mysqli_prepare($con, $query);

if ($stmt) {
    if (mysqli_stmt_execute($stmt)) {

        // Le colonne della SELECT vengono associate alle variabili PHP nello stesso ordine della query
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

        // Ogni riga viene trasformata nell'oggetto che React riceverà attraverso il JSON
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

        // Un array vuoto è comunque una risposta valida se il database non contiene gatti
        $response = array(
            'status' => 'success',
            'data' => $gatti
        );
    } else {

        // I dettagli tecnici rimangono nel log del server e non vengono esposti al client
        error_log('Errore durante l\'esecuzione della query dei gatti: ' . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);
} else {
    error_log('Errore nella preparazione della query dei gatti: ' . mysqli_error($con));
}

mysqli_close($con);

// L'array PHP viene convertito nel formato JSON atteso da GattiApp
echo json_encode($response);
?>