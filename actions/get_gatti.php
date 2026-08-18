<?php
/*
 * API utilizzata dal componente React della pagina Ospiti
 * Recupera i dati dei gatti con l'utente MySQL di sola lettura
 * e restituisce i dati al browser in formato JSON
 */

// L'endpoint produce JSON e non una pagina HTML
header('Content-Type: application/json; charset=utf-8');

require '../includes/db_config.php';

/*
 * La risposta parte da uno stato di errore
 * e viene sostituita solo se la lettura dei dati riesce
 */
$response = array(
    'status' => 'error',
    'message' => 'Non è stato possibile caricare i gatti. Riprova più tardi.'
);

// La galleria richiede esclusivamente privilegi di lettura
$con = get_db_connection('lecture');

/*
 * La query non contiene dati forniti dall'utente
 * Vengono recuperati soltanto i campi utilizzati dal componente React
 */
$query = 'SELECT id, nome, sesso, eta, razza, colore_mantello, descrizione, data_arrivo
          FROM gatti
          ORDER BY data_arrivo DESC';

$result = mysqli_query($con, $query);

if ($result) {
    $gatti = array();

    // Ogni riga viene trasformata nell'oggetto che React riceverà attraverso il JSON
    while ($riga = mysqli_fetch_assoc($result)) {
        $gatti[] = array(
            'id' => (int) $riga['id'],
            'nome' => (string) $riga['nome'],
            'sesso' => (string) $riga['sesso'],
            'eta' => (int) $riga['eta'],
            'razza' => (string) $riga['razza'],
            'colore_mantello' => (string) $riga['colore_mantello'],
            'descrizione' => (string) $riga['descrizione'],
            'data_arrivo' => (string) $riga['data_arrivo']
        );
    }

    // Un array vuoto è comunque una risposta valida se il database non contiene gatti
    $response = array(
        'status' => 'success',
        'data' => $gatti
    );

    mysqli_free_result($result);
} else {

    // I dettagli tecnici rimangono nel log del server e non vengono esposti al client
    error_log('Errore durante il recupero dei gatti: ' . mysqli_error($con));
}
mysqli_close($con);

// L'array PHP viene convertito nel formato JSON atteso da GattiApp
echo json_encode($response);
?>