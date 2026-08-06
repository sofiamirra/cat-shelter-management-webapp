<?php
/**
 * API Backend per l'estrazione dei gatti.
 * Questo file viene interrogato in modo asincrono dal componente React.
 * Restituisce i dati esclusivamente in formato JSON.
 */

// Impostiamo l'header per dire al browser che stiamo inviando un JSON, non HTML
header('Content-Type: application/json; charset=utf-8');

require 'includes/db_config.php';

// Array che conterrà la risposta finale
$response = [];

// Connessione al database usando l'utente con privilegi minimi (solo lettura)
$con = get_db_connection('lecture');

if (!$con) {
    echo json_encode(["error" => "Errore di connessione al database."]);
    exit;
}

// Estrazione di tutti i gatti. Usiamo Prepared Statements per sicurezza standard.
$query = "SELECT id, nome, sesso, eta, razza, colore_mantello, lunghezza_pelo, colore_occhi, descrizione, data_arrivo FROM gatti ORDER BY data_arrivo DESC";

if ($stmt = mysqli_prepare($con, $query)) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $gatti = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Aggiungiamo ogni riga estratta al nostro array
        $gatti[] = $row;
    }

    // Risposta di successo
    $response = [
        "status" => "success",
        "data" => $gatti
    ];

    mysqli_stmt_close($stmt);
} else {
    // Gestione errore query
    $response = [
        "status" => "error",
        "message" => "Errore nell'esecuzione della query."
    ];
}

mysqli_close($con);

// Codifica l'array PHP in formato JSON e lo stampa a schermo
echo json_encode($response);
?>