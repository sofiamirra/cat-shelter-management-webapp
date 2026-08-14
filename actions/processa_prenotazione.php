<?php
/*
 * Backend per il salvataggio delle prenotazioni delle visite
 * Registra la visita e associa alla stessa prenotazione tutti i gatti selezionati
 * Le operazioni sul database vengono eseguite all'interno di una transazione
 */

// Questa action non include l'header, quindi deve inizializzare direttamente la sessione
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// L'operazione è riservata agli utenti che hanno completato correttamente il login
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header('Location: ../login.php?ritorno=ospiti.php');
    exit;
}

require __DIR__ . '/../includes/db_config.php';

// L'action accetta soltanto dati inviati attraverso il form della pagina Ospiti
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../ospiti.php');
    exit;
}

// I valori mancanti vengono sostituiti con stringhe vuote per poter applicare successivamente gli stessi controlli
$data_visita = isset($_POST['data_visita']) ? trim($_POST['data_visita']) : '';
$ora_visita = isset($_POST['ora_visita']) ? trim($_POST['ora_visita']) : '';
$gatti_json = isset($_POST['id_gatti_selezionati']) ? $_POST['id_gatti_selezionati'] : '';
$utente_id = (int) $_SESSION['user_id'];

// Il JSON prodotto dal JavaScript viene riconvertito nell'array degli ID dei gatti
$gatti_selezionati = json_decode($gatti_json, true);

// Il flag viene impostato a false se almeno uno dei controlli lato server non viene superato
$dati_validi = true;

/*
 * La data deve rispettare il formato prodotto dal campo date
 * checkdate verifica inoltre che giorno, mese e anno costituiscano una data reale
 */
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_visita)) {
    $dati_validi = false;
} else {
    $parti_data = explode('-', $data_visita);
    $anno = (int) $parti_data[0];
    $mese = (int) $parti_data[1];
    $giorno = (int) $parti_data[2];

    if (!checkdate($mese, $giorno, $anno)) {
        $dati_validi = false;
    }
}

// Anche il server impedisce prenotazioni per il giorno corrente o per date precedenti
$data_minima = date('Y-m-d', strtotime('+1 day'));

if ($data_visita < $data_minima) {
    $dati_validi = false;
}

/*
 * L'orario viene controllato indipendentemente dal browser
 * Sono ammesse solamente visite comprese tra le 10:30 e le 17:30
 */
if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $ora_visita)) {
    $dati_validi = false;
} elseif ($ora_visita < '10:30' || $ora_visita > '17:30') {
    $dati_validi = false;
}

// La prenotazione deve contenere almeno un identificativo di gatto
if (!is_array($gatti_selezionati) || count($gatti_selezionati) === 0) {
    $dati_validi = false;
} else {

    // Ogni identificativo proveniente dal campo hidden deve essere un intero positivo
    foreach ($gatti_selezionati as $gatto_id) {
        if (!is_int($gatto_id) || $gatto_id <= 0) {
            $dati_validi = false;
            break;
        }
    }
}

// I dati non validi non raggiungono mai le operazioni di scrittura sul database
if (!$dati_validi) {
    header('Location: ../ospiti.php?status=error');
    exit;
}

// Eventuali ID ripetuti vengono eliminati prima dell'inserimento
$gatti_selezionati = array_values(array_unique($gatti_selezionati));

// Data e ora vengono riunite nel formato utilizzato dal campo DATETIME del database
$data_ora_visita = $data_visita . ' ' . $ora_visita . ':00';

// La prenotazione modifica il database e richiede quindi l'utente con privilegi di modifica
$con = get_db_connection('modifier');

/*
 * La prenotazione coinvolge più tabelle
 * La transazione permette di confermare tutti gli inserimenti insieme oppure annullarli in caso di errore
 */
if (!mysqli_begin_transaction($con)) {
    error_log('Impossibile avviare la transazione della prenotazione');
    mysqli_close($con);
    header('Location: ../ospiti.php?status=error');
    exit;
}

// Il flag determina quale messaggio verrà successivamente mostrato dalla pagina Ospiti
$prenotazione_riuscita = false;

try {
    /*
     * Prima viene creato un solo record per la visita
     * L'ID generato verrà poi utilizzato per associare tutti i gatti scelti
     */
    $query_prenotazione = 'INSERT INTO prenotazioni_visite (utente_id, data_ora) VALUES (?, ?)';
    $stmt_prenotazione = mysqli_prepare($con, $query_prenotazione);

    if (!$stmt_prenotazione) {
        throw new Exception('Preparazione inserimento prenotazione non riuscita');
    }

    // i indica l'intero dell'utente mentre s indica la stringa contenente data e ora
    mysqli_stmt_bind_param($stmt_prenotazione, 'is', $utente_id, $data_ora_visita);

    if (!mysqli_stmt_execute($stmt_prenotazione)) {
        $errore_stmt = mysqli_stmt_error($stmt_prenotazione);
        mysqli_stmt_close($stmt_prenotazione);
        throw new Exception('Inserimento prenotazione non riuscito: ' . $errore_stmt);
    }

    // Viene recuperato l'ID generato automaticamente per la nuova prenotazione
    $prenotazione_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt_prenotazione);

    if ($prenotazione_id <= 0) {
        throw new Exception('Identificativo della prenotazione non disponibile');
    }

    /*
     * Lo stesso prepared statement viene riutilizzato per tutti i gatti selezionati
     * Cambia soltanto il valore di gatto_id ad ogni esecuzione
     */
    $query_visita = 'INSERT INTO visita_gatti (prenotazione_id, gatto_id) VALUES (?, ?)';
    $stmt_visita = mysqli_prepare($con, $query_visita);

    if (!$stmt_visita) {
        throw new Exception('Preparazione associazione gatti non riuscita');
    }

    /*
     * Entrambi i parametri sono interi
     * gatto_id_corrente viene aggiornato nel ciclo senza dover preparare nuovamente lo statement
     */
    $gatto_id_corrente = 0;
    mysqli_stmt_bind_param($stmt_visita, 'ii', $prenotazione_id, $gatto_id_corrente);

    foreach ($gatti_selezionati as $gatto_id) {
        $gatto_id_corrente = $gatto_id;

        if (!mysqli_stmt_execute($stmt_visita)) {
            $errore_stmt = mysqli_stmt_error($stmt_visita);
            mysqli_stmt_close($stmt_visita);
            throw new Exception('Associazione del gatto non riuscita: ' . $errore_stmt);
        }
    }

    mysqli_stmt_close($stmt_visita);

    // Solo dopo tutti gli inserimenti la transazione viene resa definitiva
    if (!mysqli_commit($con)) {
        throw new Exception('Conferma della transazione non riuscita');
    }

    $prenotazione_riuscita = true;
} catch (Exception $e) {

    // Qualunque errore annulla sia la visita sia le associazioni eventualmente già inserite
    mysqli_rollback($con);

    // Il dettaglio tecnico viene conservato nel log senza essere mostrato direttamente all'utente
    error_log('Errore durante la prenotazione della visita: ' . $e->getMessage());
}

// La connessione viene chiusa dopo la conclusione o l'annullamento della transazione
mysqli_close($con);

// L'esito viene mostrato dalla pagina Ospiti attraverso il relativo banner
if ($prenotazione_riuscita) {
    header('Location: ../ospiti.php?status=success');
} else {
    header('Location: ../ospiti.php?status=error');
}

exit;
?>