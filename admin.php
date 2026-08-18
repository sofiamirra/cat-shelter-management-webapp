<?php
/*
 * Area riservata agli amministratori
 * Permette di inserire nuovi ospiti e consultare
 * le visite e i turni registrati nella struttura
 */

// La pagina non include ancora l'header comune e inizializza direttamente la sessione
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
 * L'autorizzazione viene verificata lato server
 * La sola presenza del collegamento Admin nell'interfaccia non viene quindi utilizzata come protezione
 */
$is_admin = isset($_SESSION['username'])
    && isset($_SESSION['is_admin'])
    && (int) $_SESSION['is_admin'] === 1;

if (!$is_admin) {
    header('Location: home.php');
    exit;
}

require 'includes/db_config.php';

$visite = array();
$turni = array();
$visite_future = array();
$visite_storiche = array();
$turni_futuri = array();
$turni_storici = array();
$errore_admin = false;

// Il pannello consulta i dati utilizzando esclusivamente l'utente con privilegi di lettura
$con = get_db_connection('lecture');

/*
 * Recupero delle visite con lo username e gli eventuali gatti associati
 * Le LEFT JOIN mantengono visibile anche una visita priva di associazioni in visita_gatti
 * Più righe relative alla stessa prenotazione vengono poi ricomposte nell'array PHP
 */
$query_visite = 'SELECT pv.id, pv.data_ora, u.username, g.nome
                  FROM prenotazioni_visite AS pv
                  JOIN utenti AS u ON u.id = pv.utente_id
                  LEFT JOIN visita_gatti AS vg ON vg.prenotazione_id = pv.id
                  LEFT JOIN gatti AS g ON g.id = vg.gatto_id
                  ORDER BY pv.data_ora ASC, pv.id ASC, g.nome ASC';

// La query non contiene dati forniti dall'utente, quindi può essere eseguita direttamente
$result_visite = mysqli_query($con, $query_visite);

if ($result_visite) {

    /*
     * L'id della prenotazione viene utilizzato come chiave temporanea
     * Una visita con più gatti rimane così una sola attività con più nomi associati
     */
    $visite_indicizzate = array();

    while ($riga = mysqli_fetch_assoc($result_visite)) {
        $id_corrente = (int) $riga['id'];

        // La struttura principale della visita viene creata soltanto alla prima riga con lo stesso id
        if (!isset($visite_indicizzate[$id_corrente])) {
            $visite_indicizzate[$id_corrente] = array(
                'id' => $id_corrente,
                'data_ora' => (string) $riga['data_ora'],
                'username' => (string) $riga['username'],
                'gatti' => array()
            );
        }

        // Con la LEFT JOIN il nome può essere NULL se non esistono gatti associati alla visita
        if ($riga['nome'] !== null && $riga['nome'] !== '') {
            $visite_indicizzate[$id_corrente]['gatti'][] = (string) $riga['nome'];
        }
    }

    // Gli id usati per il raggruppamento non servono nella visualizzazione
    $visite = array_values($visite_indicizzate);

    mysqli_free_result($result_visite);
} else {
    error_log('Errore durante il recupero delle visite amministrative: ' . mysqli_error($con));
    $errore_admin = true;
}

/*
 * Recupero di tutti i turni di volontariato insieme allo username dell'utente
 * Anche questa query è statica e richiede esclusivamente privilegi di lettura
 */
$query_turni = 'SELECT tv.id, tv.fascia_oraria, u.username
                FROM turni_volontariato AS tv
                JOIN utenti AS u ON u.id = tv.utente_id
                ORDER BY tv.fascia_oraria ASC';

$result_turni = mysqli_query($con, $query_turni);

if ($result_turni) {

    // Ogni riga viene trasformata nell'elemento utilizzato successivamente dal pannello
    while ($riga = mysqli_fetch_assoc($result_turni)) {
        $turni[] = array(
            'id' => (int) $riga['id'],
            'fascia_oraria' => (string) $riga['fascia_oraria'],
            'username' => (string) $riga['username']
        );
    }

    mysqli_free_result($result_turni);
} else {
    error_log('Errore durante il recupero dei turni amministrativi: ' . mysqli_error($con));
    $errore_admin = true;
}

mysqli_close($con);

/*
 * Le attività vengono separate tra future e concluse confrontando i relativi timestamp
 * Le query arrivano già in ordine crescente, quindi lo storico viene invertito per mostrare prima gli eventi recenti
 */
$adesso = time();

foreach ($visite as $visita) {
    $timestamp = strtotime($visita['data_ora']);

    if ($timestamp !== false && $timestamp >= $adesso) {
        $visite_future[] = $visita;
    } else {
        $visite_storiche[] = $visita;
    }
}

$visite_storiche = array_reverse($visite_storiche);

foreach ($turni as $turno) {
    $timestamp = strtotime($turno['fascia_oraria']);

    if ($timestamp !== false && $timestamp >= $adesso) {
        $turni_futuri[] = $turno;
    } else {
        $turni_storici[] = $turno;
    }
}

$turni_storici = array_reverse($turni_storici);

require 'includes/header.php';
?>

<div class="page-wrapper admin-page-wrapper">

    <header class="section-header">
        <h1>Pannello Amministratore</h1>

        <div class="paw-divider" aria-hidden="true">
            <span class="line"></span>
            <img src="assets/img/icona_zampette.png" width="128" height="128" alt="" class="paw-divider-icon">
            <span class="line"></span>
        </div>

        <p class="header-subtitle">
            Gestisci gli ospiti e consulta le attività programmate nella struttura.
        </p>
    </header>

    <?php if ($errore_admin): ?>
        <div class="alert-wrapper">
            <div class="alert-danger" role="alert">
                Non è stato possibile caricare tutte le informazioni. Riprova più tardi.
            </div>
        </div>
    <?php endif; ?>

    <!-- Collegamento alla funzione amministrativa per l'inserimento di nuovi ospiti -->
    <section class="admin-dashboard-card admin-card-ospiti">
        <span class="admin-card-badge">Gatti</span>

        <div class="admin-card-header">
            <div>
                <h2>Gestione Ospiti</h2>
                <p>Registra la scheda di un nuovo gatto accolto nella struttura.</p>
            </div>
        </div>

        <div class="admin-card-action">
            <a href="inserimento_gatto.php" class="btn-solid-dark">
                Inserisci un Nuovo Gatto
            </a>
        </div>
    </section>

    <div class="admin-dashboard-grid">

        <!-- Visite prenotate dagli utenti -->
        <section class="admin-dashboard-card">
            <div class="admin-card-header">
                <div>
                    <h2>Gestione Visite</h2>
                    <p>Consulta gli incontri programmati dagli utenti.</p>
                </div>

                <span class="admin-card-badge">
                    <?php echo count($visite_future); ?> visite
                </span>
            </div>

            <h3 class="admin-subtitle">Prossime Visite</h3>

            <?php if (count($visite_future) > 0): ?>
                <div class="admin-list">

                    <?php
                    /*
                     * giorno_corrente permette di stampare la data una sola volta
                     * per tutte le visite appartenenti allo stesso giorno
                     */
                    $giorno_corrente = '';

                    foreach ($visite_future as $visita):
                        $timestamp = strtotime($visita['data_ora']);
                        $giorno = $timestamp ? date('Y-m-d', $timestamp) : '';
                        $data = $timestamp ? date('d/m/Y', $timestamp) : '';
                        $ora = $timestamp ? date('H:i', $timestamp) : '';

                        $nomi_gatti = count($visita['gatti']) > 0
                            ? implode(', ', $visita['gatti'])
                            : 'Nessun gatto associato';

                        if ($giorno !== $giorno_corrente):
                            $giorno_corrente = $giorno;
                    ?>
                            <div class="admin-date-divider">
                                <?php echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                    <?php endif; ?>

                        <article class="admin-list-item">
                            <div class="admin-list-main">
                                <strong><?php echo htmlspecialchars($ora, ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span><?php echo htmlspecialchars($visita['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>

                            <span class="admin-list-detail">
                                <?php echo htmlspecialchars($nomi_gatti, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </article>

                    <?php endforeach; ?>

                </div>
            <?php else: ?>
                <p class="admin-empty">Non ci sono visite future programmate.</p>
            <?php endif; ?>

            <!-- details e summary forniscono un controllo espandibile nativo senza JavaScript -->
            <details class="admin-history">
                <summary>Storico Visite</summary>

                <div class="admin-history-content">
                    <?php if (count($visite_storiche) > 0): ?>

                        <?php
                        $giorno_corrente = '';

                        foreach ($visite_storiche as $visita):
                            $timestamp = strtotime($visita['data_ora']);
                            $giorno = $timestamp ? date('Y-m-d', $timestamp) : '';
                            $data = $timestamp ? date('d/m/Y', $timestamp) : '';
                            $ora = $timestamp ? date('H:i', $timestamp) : '';

                            $nomi_gatti = count($visita['gatti']) > 0
                                ? implode(', ', $visita['gatti'])
                                : 'Nessun gatto associato';

                            if ($giorno !== $giorno_corrente):
                                $giorno_corrente = $giorno;
                        ?>
                                <div class="admin-date-divider">
                                    <?php echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                        <?php endif; ?>

                            <article class="admin-list-item admin-list-item-history">
                                <div class="admin-list-main">
                                    <strong><?php echo htmlspecialchars($ora, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <span><?php echo htmlspecialchars($visita['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>

                                <span class="admin-list-detail">
                                    <?php echo htmlspecialchars($nomi_gatti, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </article>

                        <?php endforeach; ?>

                    <?php else: ?>
                        <p class="admin-empty">Lo storico delle visite è vuoto.</p>
                    <?php endif; ?>
                </div>
            </details>
        </section>

        <!-- Turni di volontariato prenotati dagli utenti -->
        <section class="admin-dashboard-card">
            <div class="admin-card-header">
                <div>
                    <h2>Gestione Volontari</h2>
                    <p>Consulta gli utenti iscritti ai prossimi turni.</p>
                </div>

                <span class="admin-card-badge">
                    <?php echo count($turni_futuri); ?> turni
                </span>
            </div>

            <h3 class="admin-subtitle">Prossimi Turni</h3>

            <?php if (count($turni_futuri) > 0): ?>
                <div class="admin-list">

                    <?php
                    $giorno_corrente = '';

                    foreach ($turni_futuri as $turno):
                        $timestamp = strtotime($turno['fascia_oraria']);
                        $giorno = $timestamp ? date('Y-m-d', $timestamp) : '';
                        $data = $timestamp ? date('d/m/Y', $timestamp) : '';
                        $ora = $timestamp ? date('H:i', $timestamp) : '';

                        // L'orario memorizzato viene convertito nella stessa etichetta utilizzata nella pagina Volontariato
                        if ($ora === '09:00') {
                            $nome_fascia = 'Mattina (09 - 13)';
                        } elseif ($ora === '13:00') {
                            $nome_fascia = 'Pomeriggio (13 - 17)';
                        } elseif ($ora === '17:00') {
                            $nome_fascia = 'Sera (17 - 21)';
                        } else {
                            $nome_fascia = $ora;
                        }

                        if ($giorno !== $giorno_corrente):
                            $giorno_corrente = $giorno;
                    ?>
                            <div class="admin-date-divider">
                                <?php echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                    <?php endif; ?>

                        <article class="admin-list-item">
                            <div class="admin-list-main">
                                <strong><?php echo htmlspecialchars($nome_fascia, ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span><?php echo htmlspecialchars($turno['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </article>

                    <?php endforeach; ?>

                </div>
            <?php else: ?>
                <p class="admin-empty">Non ci sono turni futuri programmati.</p>
            <?php endif; ?>

            <details class="admin-history">
                <summary>Storico Turni</summary>

                <div class="admin-history-content">
                    <?php if (count($turni_storici) > 0): ?>

                        <?php
                        $giorno_corrente = '';

                        foreach ($turni_storici as $turno):
                            $timestamp = strtotime($turno['fascia_oraria']);
                            $giorno = $timestamp ? date('Y-m-d', $timestamp) : '';
                            $data = $timestamp ? date('d/m/Y', $timestamp) : '';
                            $ora = $timestamp ? date('H:i', $timestamp) : '';

                            if ($ora === '09:00') {
                                $nome_fascia = 'Mattina (09 - 13)';
                            } elseif ($ora === '13:00') {
                                $nome_fascia = 'Pomeriggio (13 - 17)';
                            } elseif ($ora === '17:00') {
                                $nome_fascia = 'Sera (17 - 21)';
                            } else {
                                $nome_fascia = $ora;
                            }

                            if ($giorno !== $giorno_corrente):
                                $giorno_corrente = $giorno;
                        ?>
                                <div class="admin-date-divider">
                                    <?php echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                        <?php endif; ?>

                            <article class="admin-list-item admin-list-item-history">
                                <div class="admin-list-main">
                                    <strong><?php echo htmlspecialchars($nome_fascia, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <span><?php echo htmlspecialchars($turno['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </article>

                        <?php endforeach; ?>

                    <?php else: ?>
                        <p class="admin-empty">Lo storico dei turni è vuoto.</p>
                    <?php endif; ?>
                </div>
            </details>
        </section>

    </div>
</div>

<?php require 'includes/footer.php'; ?>