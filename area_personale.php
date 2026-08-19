<?php
/*
 * Area personale dell'utente autenticato
 * Mostra le visite e i turni di volontariato ancora in programma
 * Ogni utente può intervenire esclusivamente sulle proprie prenotazioni
 */

// La pagina non include ancora l'header comune e inizializza quindi direttamente la sessione
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// La pagina richiede una sessione autenticata e l'identificativo dell'utente
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php?ritorno=area_personale.php');
    exit;
}

require 'includes/db_config.php';

$utente_id = (int) $_SESSION['user_id'];
$visite = array();
$turni = array();
$errore_caricamento = false;

// Per visualizzare le attività sono sufficienti privilegi di sola lettura
$con = get_db_connection('lecture');

/*
 * Recupero delle visite future appartenenti all'utente con gli eventuali gatti associati
 * Le righe della stessa prenotazione vengono successivamente raggruppate utilizzando l'id della visita
 * Il filtro su utente_id impedisce di visualizzare prenotazioni appartenenti ad altri account
 */
$query_visite = 'SELECT pv.id, pv.data_ora, g.nome
                  FROM prenotazioni_visite AS pv
                  LEFT JOIN visita_gatti AS vg ON vg.prenotazione_id = pv.id
                  LEFT JOIN gatti AS g ON g.id = vg.gatto_id
                  WHERE pv.utente_id = ? AND pv.data_ora > NOW()
                  ORDER BY pv.data_ora ASC, pv.id ASC, g.nome ASC';

$stmt_visite = mysqli_prepare($con, $query_visite);

if ($stmt_visite) {
    mysqli_stmt_bind_param($stmt_visite, 'i', $utente_id);

    if (mysqli_stmt_execute($stmt_visite)) {
        mysqli_stmt_bind_result(
            $stmt_visite,
            $visita_id,
            $visita_data_ora,
            $visita_nome_gatto
        );

        /*
         * L'array temporaneo usa l'id della prenotazione come chiave
         * Una visita associata a più gatti rimane quindi una sola attività con più nomi
         */
        $visite_indicizzate = array();

        while (mysqli_stmt_fetch($stmt_visite)) {
            $id_corrente = (int) $visita_id;

            if (!isset($visite_indicizzate[$id_corrente])) {
                $visite_indicizzate[$id_corrente] = array(
                    'id' => $id_corrente,
                    'data_ora' => (string) $visita_data_ora,
                    'gatti' => array()
                );
            }

            // Il nome può essere NULL nel caso di una visita priva di associazioni
            if ($visita_nome_gatto !== null && $visita_nome_gatto !== '') {
                $visite_indicizzate[$id_corrente]['gatti'][] = (string) $visita_nome_gatto;
            }
        }

        // Gli indici basati sull'id vengono rimossi prima di utilizzare l'array nella visualizzazione
        $visite = array_values($visite_indicizzate);
    } else {
        error_log('Errore durante il recupero delle visite personali: ' . mysqli_stmt_error($stmt_visite));
        $errore_caricamento = true;
    }

    mysqli_stmt_close($stmt_visite);
} else {
    error_log('Errore nella preparazione delle visite personali: ' . mysqli_error($con));
    $errore_caricamento = true;
}

/*
 * Recupero dei turni futuri appartenenti all'utente
 * Anche questa query utilizza esclusivamente l'identificativo salvato nella sessione
 */
$query_turni = 'SELECT id, fascia_oraria
                FROM turni_volontariato
                WHERE utente_id = ? AND fascia_oraria > NOW()
                ORDER BY fascia_oraria ASC';

$stmt_turni = mysqli_prepare($con, $query_turni);

if ($stmt_turni) {
    mysqli_stmt_bind_param($stmt_turni, 'i', $utente_id);

    if (mysqli_stmt_execute($stmt_turni)) {
        mysqli_stmt_bind_result($stmt_turni, $turno_id, $turno_fascia);

        while (mysqli_stmt_fetch($stmt_turni)) {
            $turni[] = array(
                'id' => (int) $turno_id,
                'fascia_oraria' => (string) $turno_fascia
            );
        }
    } else {
        error_log('Errore durante il recupero dei turni personali: ' . mysqli_stmt_error($stmt_turni));
        $errore_caricamento = true;
    }

    mysqli_stmt_close($stmt_turni);
} else {
    error_log('Errore nella preparazione dei turni personali: ' . mysqli_error($con));
    $errore_caricamento = true;
}

mysqli_close($con);

require 'includes/header.php';

// Il parametro status permette di mostrare l'esito delle action di annullamento
$status = isset($_GET['status']) ? $_GET['status'] : '';
?>

<div class="page-wrapper area-personale-wrapper">

    <header class="section-header">
        <h1>Le Mie Attività</h1>

        <div class="paw-divider" aria-hidden="true">
            <span class="line"></span>
            <img src="assets/img/icona_zampette.png" width="128" height="128" alt="" class="paw-divider-icon">
            <span class="line"></span>
        </div>

        <p class="header-subtitle">
            Consulta le visite e i turni di volontariato che hai prenotato.
        </p>
    </header>

    <!-- Feedback delle operazioni effettuate dalla pagina personale -->
    <?php if ($status === 'visita_eliminata'): ?>
        <div class="alert-wrapper">
            <div class="alert-success" role="status">
                <strong>Visita annullata correttamente.</strong>
            </div>
        </div>

    <?php elseif ($status === 'turno_eliminato'): ?>
        <div class="alert-wrapper">
            <div class="alert-success" role="status">
                <strong>Turno di volontariato annullato correttamente.</strong>
            </div>
        </div>

    <?php elseif ($status === 'non_disponibile'): ?>
        <div class="alert-wrapper">
            <div class="alert-danger" role="alert">
                La prenotazione richiesta non è più disponibile oppure non appartiene al tuo account.
            </div>
        </div>

    <?php elseif ($status === 'errore'): ?>
        <div class="alert-wrapper">
            <div class="alert-danger" role="alert">
                Si è verificato un errore durante l'operazione. Riprova più tardi.
            </div>
        </div>
    <?php endif; ?>

    <?php if ($errore_caricamento): ?>
        <div class="alert-wrapper">
            <div class="alert-danger" role="alert">
                Non è stato possibile caricare tutte le attività. Riprova più tardi.
            </div>
        </div>
    <?php endif; ?>

    <div class="area-personale-grid">

        <!-- Visite prenotate -->
        <section class="dashboard-section">
            <header class="dashboard-section-header">
                <h2>Le Mie Visite</h2>
                <p>Incontri già programmati con i nostri ospiti.</p>
            </header>

            <?php if (count($visite) > 0): ?>
                <div class="dashboard-list">

                    <?php foreach ($visite as $visita): ?>
                        <?php
                        // Data e ora vengono formattate soltanto al momento della visualizzazione
                        $timestamp_visita = strtotime($visita['data_ora']);
                        $data_visita = $timestamp_visita ? date('d/m/Y', $timestamp_visita) : '';
                        $ora_visita = $timestamp_visita ? date('H:i', $timestamp_visita) : '';

                        // I nomi dei gatti associati alla stessa prenotazione vengono mostrati insieme
                        $nomi_gatti = count($visita['gatti']) > 0
                            ? implode(', ', $visita['gatti'])
                            : 'Nessun gatto associato';
                        ?>

                        <article class="dashboard-item">
                            <div class="dashboard-item-header">
                                <h3><?php echo htmlspecialchars($data_visita, ENT_QUOTES, 'UTF-8'); ?></h3>

                                <span class="dashboard-orario">
                                    Ore <?php echo htmlspecialchars($ora_visita, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>

                            <p class="dashboard-description">
                                <strong>Gatti:</strong>
                                <?php echo htmlspecialchars($nomi_gatti, ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <!-- L'id identifica la prenotazione richiesta, mentre l'action ricontrollerà anche il proprietario -->
                            <form
                                id="form-annulla-visita-<?php echo (int) $visita['id']; ?>"
                                action="actions/elimina_prenotazione.php"
                                method="POST"
                                class="form-annulla-attivita"
                                data-messaggio="Vuoi annullare questa visita?"
                            >
                                <input
                                    type="hidden"
                                    id="prenotazione-id-<?php echo (int) $visita['id']; ?>"
                                    name="prenotazione_id"
                                    value="<?php echo (int) $visita['id']; ?>"
                                >

                                <button type="submit" id="btn-annulla-visita-<?php echo (int) $visita['id']; ?>" class="btn-annulla-attivita">
                                    Annulla Visita
                                </button>
                            </form>
                        </article>

                    <?php endforeach; ?>

                </div>
            <?php else: ?>
                <p class="dashboard-empty">
                    Non hai visite in programma.
                </p>
            <?php endif; ?>
        </section>

        <!-- Turni di volontariato prenotati -->
        <section class="dashboard-section">
            <header class="dashboard-section-header">
                <h2>I Miei Turni</h2>
                <p>Disponibilità già registrate per il volontariato.</p>
            </header>

            <?php if (count($turni) > 0): ?>
                <div class="dashboard-list">

                    <?php foreach ($turni as $turno): ?>
                        <?php
                        // Data e ora vengono ricavate dal valore DATETIME salvato nel database
                        $timestamp_turno = strtotime($turno['fascia_oraria']);
                        $data_turno = $timestamp_turno ? date('d/m/Y', $timestamp_turno) : '';
                        $ora_turno = $timestamp_turno ? date('H:i', $timestamp_turno) : '';

                        // L'orario viene convertito nel nome della fascia mostrata nella pagina Volontariato
                        $nome_fascia = 'Turno';

                        if ($ora_turno === '09:00') {
                            $nome_fascia = 'Mattina (09 - 13)';
                        } elseif ($ora_turno === '13:00') {
                            $nome_fascia = 'Pomeriggio (13 - 17)';
                        } elseif ($ora_turno === '17:00') {
                            $nome_fascia = 'Sera (17 - 21)';
                        }
                        ?>

                        <article class="dashboard-item">
                            <div class="dashboard-item-header">
                                <h3><?php echo htmlspecialchars($data_turno, ENT_QUOTES, 'UTF-8'); ?></h3>

                                <span class="dashboard-orario">
                                    <?php echo htmlspecialchars($nome_fascia, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>

                            <!-- Anche in questo caso l'action verifica che il turno appartenga all'utente autenticato -->
                            <form
                                id="form-annulla-turno-<?php echo (int) $turno['id']; ?>"
                                action="actions/elimina_turno.php"
                                method="POST"
                                class="form-annulla-attivita"
                                data-messaggio="Vuoi annullare questo turno di volontariato?"
                            >
                                <input
                                    id="turno-id-<?php echo (int) $turno['id']; ?>"
                                    type="hidden"
                                    name="turno_id"
                                    value="<?php echo (int) $turno['id']; ?>"
                                >

                                <button type="submit" id="btn-annulla-turno-<?php echo (int) $turno['id']; ?>" class="btn-annulla-attivita">
                                    Annulla Turno
                                </button>
                            </form>
                        </article>

                    <?php endforeach; ?>

                </div>
            <?php else: ?>
                <p class="dashboard-empty">
                    Non hai turni di volontariato in programma.
                </p>
            <?php endif; ?>
        </section>

    </div>
</div>

<script>
/*
 * Prima di inviare una richiesta di annullamento viene chiesta una conferma
 * Il messaggio specifico viene letto dall'attributo data-messaggio del relativo form
 */
document.querySelectorAll('.form-annulla-attivita').forEach(function(form) {
    form.addEventListener('submit', function(event) {
        const messaggio = form.getAttribute('data-messaggio');

        if (!window.confirm(messaggio)) {
            event.preventDefault();
        }
    });
});
</script>

<?php require 'includes/footer.php'; ?>