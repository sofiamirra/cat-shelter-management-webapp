<?php
/*
 * Pagina dedicata ai gatti ospitati nella struttura
 * React gestisce la visualizzazione, la ricerca, l'ordinamento e la selezione
 * Il form per prenotare una visita viene invece gestito in Vanilla JavaScript
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require 'includes/header.php';
?>

<div class="page-wrapper">

    <!-- Intestazione della pagina -->
    <header class="section-header">
        <h2>I Nostri Ospiti</h2>
        <div class="paw-divider" aria-hidden="true">
            <span class="line"></span>
            <img src="assets/img/icona_zampette.png" alt="" class="paw-divider-icon">
            <span class="line"></span>
        </div>
        <p class="header-subtitle">Scopri i felini in cerca di casa. Usa i filtri per esplorare la galleria e conoscere le loro storie per trovare il micio più adatto a te.</p>
    </header>

    <!-- Messaggi mostrati dopo il tentativo di prenotazione -->
    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert-wrapper mb-4" id="banner-feedback">
            <div class="auth-alert-success alert-dismissible">
                <strong class="messaggio-successo-titolo"><img src="assets/img/icona_spunta_successo.png" alt="" class="icona-successo"> Prenotazione confermata!</strong><br>
                Ti aspettiamo in struttura. I dettagli sono stati salvati correttamente.
                <button type="button" class="btn-close-alert" id="btn-chiudi-feedback" aria-label="Chiudi">&times;</button>
            </div>
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
        <div class="alert-wrapper mb-4" id="banner-feedback">
            <div class="auth-alert-danger alert-dismissible">
                <strong>Si è verificato un errore!</strong> Riprova più tardi o contatta la struttura.
                <button type="button" class="btn-close-alert" id="btn-chiudi-feedback" aria-label="Chiudi">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Il form di prenotazione viene mostrato soltanto agli utenti autenticati -->
    <?php if (isset($_SESSION['username'])): ?>
        <div class="prenotazione-wrapper mb-4" id="sezione-prenotazione">
            <div class="prenotazione-header text-center mb-2">
                <h2>Prenota una visita in struttura</h2>
                <p class="header-subtitle prenotazione-subtitle">Seleziona uno o più gatti dalle <strong>card in basso</strong> per compilare la tua richiesta.</p>
                <p class="orari-visita-text">Le visite si effettuano tutti i giorni dalle 10:30 alle 17:30.</p>
            </div>

            <!-- La validazione lato client viene effettuata interamente in Vanilla JavaScript -->
            <form action="actions/processa_prenotazione.php" method="POST" id="form-prenotazione-visita" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="data_visita" class="form-label-title">Data della visita:</label>
                        <input type="date" id="data_visita" name="data_visita" class="input-data-large">
                        <span class="errore-js" id="err-data"></span>
                    </div>

                    <div class="form-group">
                        <label for="ora_visita" class="form-label-title">Ora della visita:</label>
                        <input type="time" id="ora_visita" name="ora_visita" class="input-data-large">
                        <span class="errore-js" id="err-ora"></span>
                    </div>
                </div>

                <div class="form-group mt-2">
                    <label class="form-label-title">Gatti selezionati per l'incontro:</label>
                    <ul class="lista-selezionati" id="ui-lista-gatti">
                        <li class="nessun-gatto-selezionato">Nessun gatto selezionato al momento. Clicca sulle card in basso.</li>
                    </ul>
                </div>

                <!-- React comunica gli ID scelti al form attraverso il CustomEvent -->
                <input type="hidden" name="id_gatti_selezionati" id="input-hidden-gatti" value="">

                <!-- Il pulsante viene abilitato dal JavaScript soltanto dopo una selezione -->
                <button type="submit" class="btn-solid-dark w-100 mt-1" id="btn-prenota" disabled>Conferma Prenotazione</button>
            </form>
        </div>

    <!-- Chi non è autenticato può consultare i gatti ma non selezionarli -->
    <?php else: ?>
        <div class="alert-wrapper mb-4 text-center">
            <div class="auth-alert-success">
                <a href="login.php?ritorno=ospiti.php" class="auth-alert-link">Accedi o Registrati</a> per selezionare i mici e prenotare la tua visita.
            </div>
        </div>
    <?php endif; ?>

    <!-- React inserisce qui filtri e card dei gatti -->
    <div id="react-root"></div>
</div>

<!-- React e Babel vengono caricati secondo la modalità utilizzata durante il corso -->
<script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
<script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<!-- Lo stato della sessione PHP viene reso disponibile al componente React -->
<script>
const IS_LOGGED_IN = <?php echo isset($_SESSION['username']) ? 'true' : 'false'; ?>;
</script>

<script type="text/babel" src="assets/js/GattiApp.js"></script>

<script>
/*
 * Gestione Vanilla JavaScript della pagina
 * Il codice riceve dal componente React la selezione dei gatti e valida il form
 */

// Il banner di feedback può essere chiuso senza utilizzare JavaScript inline nell'HTML
const btnChiudiFeedback = document.getElementById('btn-chiudi-feedback');

if (btnChiudiFeedback) {
    btnChiudiFeedback.addEventListener('click', function() {
        document.getElementById('banner-feedback').classList.add('d-none');
    });
}

const formPrenotazione = document.getElementById('form-prenotazione-visita');

if (formPrenotazione) {
    const listaGatti = document.getElementById('ui-lista-gatti');
    const hiddenGatti = document.getElementById('input-hidden-gatti');
    const btnPrenota = document.getElementById('btn-prenota');
    const inputData = document.getElementById('data_visita');
    const inputOra = document.getElementById('ora_visita');
    const erroreData = document.getElementById('err-data');
    const erroreOra = document.getElementById('err-ora');

    /*
     * La prima data prenotabile è il giorno successivo
     * La costruzione manuale mantiene il valore nel formato YYYY-MM-DD del campo date
     */
    const domani = new Date();
    domani.setDate(domani.getDate() + 1);

    const anno = domani.getFullYear();
    const mese = String(domani.getMonth() + 1).padStart(2, '0');
    const giorno = String(domani.getDate()).padStart(2, '0');
    const dataMinima = anno + '-' + mese + '-' + giorno;

    inputData.min = dataMinima;
    inputOra.min = '10:30';
    inputOra.max = '17:30';

    /*
     * Il componente React invia un CustomEvent ogni volta che cambia la selezione
     * L'array ricevuto viene usato per aggiornare la lista e il campo hidden del form
     */
    document.addEventListener('aggiornamentoGattiScelti', function(event) {
        const gattiSelezionati = Array.isArray(event.detail) ? event.detail : [];

        listaGatti.innerHTML = '';

        if (gattiSelezionati.length === 0) {
            const elementoVuoto = document.createElement('li');
            elementoVuoto.className = 'nessun-gatto-selezionato';
            elementoVuoto.textContent = 'Nessun gatto selezionato al momento. Clicca sulle card in basso.';
            listaGatti.appendChild(elementoVuoto);

            hiddenGatti.value = '';
            btnPrenota.disabled = true;
            return;
        }

        const idGatti = [];

        gattiSelezionati.forEach(function(gatto) {
            const elementoLista = document.createElement('li');
            const razza = gatto.razza ? gatto.razza : 'Meticcio';

            elementoLista.textContent = '🐾 ' + gatto.nome + ' (Razza: ' + razza + ')';
            listaGatti.appendChild(elementoLista);
            idGatti.push(gatto.id);
        });

        // Gli ID vengono convertiti in JSON per essere trasmessi al backend PHP
        hiddenGatti.value = JSON.stringify(idGatti);
        btnPrenota.disabled = false;
    });

    /*
     * La validazione viene ripetuta sul browser prima dell'invio
     * Il server eseguirà comunque gli stessi controlli sui dati ricevuti
     */
    formPrenotazione.addEventListener('submit', function(event) {
        let formValido = true;

        erroreData.textContent = '';
        erroreOra.textContent = '';
        inputData.classList.remove('input-error');
        inputOra.classList.remove('input-error');

        if (inputData.value === '') {
            erroreData.textContent = 'Seleziona una data per la visita.';
            inputData.classList.add('input-error');
            formValido = false;
        } else if (inputData.value < dataMinima) {
            erroreData.textContent = 'La visita deve essere prenotata almeno dal giorno successivo.';
            inputData.classList.add('input-error');
            formValido = false;
        }

        if (inputOra.value === '') {
            erroreOra.textContent = 'Seleziona un orario per la visita.';
            inputOra.classList.add('input-error');
            formValido = false;
        } else if (inputOra.value < '10:30' || inputOra.value > '17:30') {
            erroreOra.textContent = "L'orario deve essere compreso tra le 10:30 e le 17:30.";
            inputOra.classList.add('input-error');
            formValido = false;
        }

        // La prenotazione non può essere inviata senza almeno un gatto selezionato
        if (hiddenGatti.value === '') {
            formValido = false;
        }

        if (!formValido) {
            event.preventDefault();
        }
    });
}
</script>

<?php require 'includes/footer.php'; ?>