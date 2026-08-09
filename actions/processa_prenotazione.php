<?php
/**
 * Backend per il salvataggio della prenotazione visita.
 * Gestisce l'inserimento multiplo su tabelle relazionali tramite transazione SQL.
 */

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require 'includes/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_visita = trim($_POST['data_visita']);
    $ora_visita = trim($_POST['ora_visita']);
    $gatti_json = $_POST['id_gatti_selezionati']; 
    $username_session = $_SESSION['username'];

    // Decodifica della stringa JSON inviata da JavaScript in un array PHP
    $gatti_selezionati = json_decode($gatti_json, true);
    
    // Se non ci sono gatti selezionati, blocchiamo l'esecuzione
    if (empty($gatti_selezionati)) {
        header("Location: ospiti.php?status=error");
        exit;
    }

    // Unione di data e ora nel formato standard DATETIME (YYYY-MM-DD HH:MM:SS)
    $data_ora_visita = $data_visita . ' ' . $ora_visita . ':00';

    // Connessione come "modifier"
    $con = get_db_connection('modifier');
    if (!$con) { die("Errore di connessione."); }

    // Avvio della transazione per garantire l'integrità del database
    mysqli_begin_transaction($con);

    try {
        // --- FASE 1: Recupero dell'ID utente ---
        $utente_id = null;
        $query_user = "SELECT id FROM utenti WHERE username = ?";
        if ($stmt_user = mysqli_prepare($con, $query_user)) {
            mysqli_stmt_bind_param($stmt_user, "s", $username_session);
            mysqli_stmt_execute($stmt_user);
            mysqli_stmt_bind_result($stmt_user, $utente_id);
            mysqli_stmt_fetch($stmt_user);
            mysqli_stmt_close($stmt_user);
        }

        if (!$utente_id) {
            throw new Exception("Utente non trovato");
        }

        // --- FASE 2: Inserimento in prenotazioni_visite ---
        $prenotazione_id = null;
        $query_prenotazione = "INSERT INTO prenotazioni_visite (utente_id, data_ora) VALUES (?, ?)";
        if ($stmt_prenotazione = mysqli_prepare($con, $query_prenotazione)) {
            mysqli_stmt_bind_param($stmt_prenotazione, "is", $utente_id, $data_ora_visita);
            mysqli_stmt_execute($stmt_prenotazione);
            // Recupera l'ID appena generato
            $prenotazione_id = mysqli_insert_id($con);
            mysqli_stmt_close($stmt_prenotazione);
        }

        if (!$prenotazione_id) {
            throw new Exception("Errore creazione record prenotazione");
        }

        // --- FASE 3: Inserimento in visita_gatti (per ogni gatto selezionato) ---
        $query_visita = "INSERT INTO visita_gatti (prenotazione_id, gatto_id) VALUES (?, ?)";
        if ($stmt_visita = mysqli_prepare($con, $query_visita)) {
            foreach ($gatti_selezionati as $gatto_id) {
                mysqli_stmt_bind_param($stmt_visita, "ii", $prenotazione_id, $gatto_id);
                mysqli_stmt_execute($stmt_visita);
            }
            mysqli_stmt_close($stmt_visita);
        }

        // Se arriviamo fin qui senza errori, confermiamo la transazione
        mysqli_commit($con);
        header("Location: ospiti.php?status=success");

    } catch (Exception $e) {
        // In caso di errore, annulliamo tutte le operazioni precedenti
        mysqli_rollback($con);
        header("Location: ospiti.php?status=error");
    }

    mysqli_close($con);
} else {
    header("Location: ospiti.php");
}
exit;
?>