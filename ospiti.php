<?php
/*
 * Pagina dedicata ai gatti ospitati nella struttura
 * React gestisce la visualizzazione, la ricerca, l'ordinamento e la selezione
 * Il form per prenotare una visita viene invece gestito in Vanilla JavaScript
 */

// La sessione viene inizializzata centralmente dall'header prima di qualsiasi output HTML
require 'includes/header.php';
?>

<div class="page-wrapper">

    <!-- Intestazione principale della pagina, seguita dalle sezioni dedicate alla prenotazione e alla galleria -->
    <header class="section-header">
        <h1>I Nostri Ospiti</h1>

        <!-- Il divisore è puramente decorativo, quindi viene escluso dalle tecnologie assistive -->
        <div class="paw-divider" aria-hidden="true">
            <span class="line"></span>
            <img src="assets/img/icona_zampette.png" alt="" class="paw-divider-icon">
            <span class="line"></span>
        </div>

        <p class="header-subtitle">Scopri i felini in cerca di casa. Usa i filtri per esplorare la galleria e conoscere le loro storie per trovare il micio più adatto a te.</p>
    </header>

    <!-- Il parametro status nella query string determina quale messaggio mostrare dopo il redirect del backend -->
    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert-wrapper" id="banner-feedback">
            <div class="alert-success alert-dismissible">
                <strong class="messaggio-successo-titolo"><img src="assets/img/icona_spunta_successo.png" alt="" class="icona-successo"> Prenotazione confermata!</strong><br>
                Ti aspettiamo in struttura. I dettagli sono stati salvati correttamente.

                <!-- aria-label descrive la funzione del pulsante perché il simbolo × da solo non è sufficientemente informativo -->
                <button type="button" class="btn-close-alert" id="btn-chiudi-feedback" aria-label="Chiudi">&times;</button>
            </div>
        </div>

    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
        <!-- Se il backend reindirizza con status=error viene mostrato un messaggio generico senza esporre dettagli tecnici -->
        <div class="alert-wrapper" id="banner-feedback">
            <div class="alert-danger alert-dismissible">
                <strong>Si è verificato un errore!</strong> Riprova più tardi o contatta la struttura.
                <button type="button" class="btn-close-alert" id="btn-chiudi-feedback" aria-label="Chiudi">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- La presenza dello username in sessione permette di mostrare il form soltanto agli utenti autenticati -->
    <?php if (isset($_SESSION['username'])): ?>
        <div class="form-card" id="sezione-prenotazione">
            <div class="prenotazione-header">
                <h2>Prenota una visita in struttura</h2>
                <p class="header-subtitle prenotazione-subtitle">Seleziona uno o più gatti dalle <strong>card in basso</strong> per compilare la tua richiesta.</p>
                <p class="orari-visita-text">Le visite si effettuano tutti i giorni dalle 10:30 alle 17:30.</p>
            </div>

            <!-- novalidate evita la validazione automatica del browser perché i controlli lato client vengono gestiti in Vanilla JavaScript -->
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

                <div class="form-group">
                    <!-- La lista viene aggiornata dinamicamente con i gatti ricevuti dal componente React -->
                    <p class="form-label-title">Gatti selezionati per l'incontro:</p>

                    <ul class="lista-selezionati" id="ui-lista-gatti">
                        <li class="nessun-gatto-selezionato">Nessun gatto selezionato al momento. Clicca sulle card in basso.</li>
                    </ul>
                </div>

                <!-- Il Vanilla JavaScript salva nel campo hidden gli ID ricevuti da React tramite CustomEvent, così il form può inviarli al backend PHP -->
                <input type="hidden" name="id_gatti_selezionati" id="input-hidden-gatti" value="">

                <!-- Il pulsante parte disabilitato e viene attivato soltanto quando React ha comunicato almeno un gatto selezionato -->
                <button type="submit" class="btn-solid-dark" id="btn-prenota" disabled>Conferma Prenotazione</button>
            </form>
        </div>

    <!-- Chi non è autenticato può consultare la galleria ma non utilizzare il form di prenotazione -->
    <?php else: ?>
        <div class="alert-wrapper">
            <div class="alert-success">
                <a href="login.php?ritorno=ospiti.php">Accedi o Registrati</a> per selezionare i mici e prenotare la tua visita.
            </div>
        </div>
    <?php endif; ?>

    <!-- Contenitore vuoto in cui React monterà dinamicamente filtri, ordinamento e card dei gatti -->
    <div id="react-root"></div>
</div>

<!-- React, ReactDOM e Babel vengono caricati da CDN come visto nelle esercitazioni del corso -->
<script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
<script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<!-- PHP converte lo stato della sessione in un booleano JavaScript che React usa per consentire o bloccare la selezione -->
<script>
// const è adatto perché lo stato ricevuto da PHP resta invariato per tutta l'esecuzione della pagina
const IS_LOGGED_IN = <?php echo isset($_SESSION['username']) ? 'true' : 'false'; ?>;
</script>

<script type="text/babel" src="assets/js/GattiApp.js"></script>

<script>
/*
 * Gestione Vanilla JavaScript della pagina
 * Il codice gestisce il banner di feedback, riceve da React la selezione dei gatti
 * aggiorna il form e ne controlla data e orario prima dell'invio al backend
 */

// I riferimenti agli elementi del DOM sono dichiarati con const perché vengono assegnati una volta e non successivamente riassegnati
const btnChiudiFeedback = document.getElementById('btn-chiudi-feedback');

// Il banner viene generato da PHP solo dopo una prenotazione, quindi il listener viene collegato soltanto se il pulsante esiste
if (btnChiudiFeedback) {
    btnChiudiFeedback.addEventListener('click', function() {
        document.getElementById('banner-feedback').classList.add('d-none');
    });
}

// Il form non viene generato per i visitatori non autenticati, quindi la sua logica viene eseguita solo quando l'elemento esiste
const formPrenotazione = document.getElementById('form-prenotazione-visita');

if (formPrenotazione) {
    // Riferimenti agli elementi che verranno letti o aggiornati durante la gestione della prenotazione
    const listaGatti = document.getElementById('ui-lista-gatti');
    const hiddenGatti = document.getElementById('input-hidden-gatti');
    const btnPrenota = document.getElementById('btn-prenota');
    const inputData = document.getElementById('data_visita');
    const inputOra = document.getElementById('ora_visita');
    const erroreData = document.getElementById('err-data');
    const erroreOra = document.getElementById('err-ora');

    // L'oggetto Date resta lo stesso riferimento, mentre setDate ne modifica internamente la data
    const domani = new Date();
    domani.setDate(domani.getDate() + 1);

    const anno = domani.getFullYear();

    // Mese e giorno vengono convertiti in stringhe a due cifre per ottenere il formato YYYY-MM-DD richiesto dall'input date
    const mese = String(domani.getMonth() + 1).padStart(2, '0');
    const giorno = String(domani.getDate()).padStart(2, '0');
    const dataMinima = anno + '-' + mese + '-' + giorno;

    // I limiti vengono applicati anche ai controlli HTML per guidare l'utente nella scelta
    inputData.min = dataMinima;
    inputOra.min = '10:30';
    inputOra.max = '17:30';

    /*
     * React emette l'evento personalizzato aggiornamentoGattiScelti sul document
     * event.detail contiene l'array dei gatti selezionati e permette al Vanilla JavaScript di riceverlo
     */
    document.addEventListener('aggiornamentoGattiScelti', function(event) {
        // event.detail contiene l'array dei gatti selezionati ricevuto dal componente React
        const gattiSelezionati = event.detail;

        // La lista viene svuotata prima di ricostruirla in base alla nuova selezione
        listaGatti.innerHTML = '';

        // Nessuna selezione: viene ripristinato il messaggio iniziale e il form resta non inviabile
        if (gattiSelezionati.length === 0) {
            const elementoVuoto = document.createElement('li');
            elementoVuoto.className = 'nessun-gatto-selezionato';
            elementoVuoto.textContent = 'Nessun gatto selezionato al momento. Clicca sulle card in basso.';
            listaGatti.appendChild(elementoVuoto);

            hiddenGatti.value = '';
            btnPrenota.disabled = true;
            return;
        }

        // L'array è dichiarato const perché il riferimento non cambia, mentre i singoli ID vengono aggiunti con push
        const idGatti = [];

        // forEach percorre tutti i gatti selezionati per costruire la lista visibile e raccoglierne gli ID
        gattiSelezionati.forEach(function(gatto) {
            const elementoLista = document.createElement('li');

            // Se la razza non è disponibile viene mostrato il valore alternativo "Meticcio"
            const razza = gatto.razza ? gatto.razza : 'Meticcio';

            // textContent inserisce i dati come testo nella lista senza interpretarli come HTML
            elementoLista.textContent = gatto.nome + ' (Razza: ' + razza + ')';
            listaGatti.appendChild(elementoLista);
            idGatti.push(gatto.id);
        });

        // JSON.stringify converte l'array degli ID in una stringa JSON che può essere inserita nel campo hidden e inviata con il form
        hiddenGatti.value = JSON.stringify(idGatti);
        btnPrenota.disabled = false;
    });

    /*
     * La validazione viene ripetuta sul browser prima dell'invio
     * Il server eseguirà comunque gli stessi controlli sui dati ricevuti
     */
    formPrenotazione.addEventListener('submit', function(event) {
        // let viene usato perché il valore di formValido può cambiare da true a false durante i controlli
        let formValido = true;

        // Prima di una nuova validazione vengono rimossi i messaggi e gli stili di errore del tentativo precedente
        erroreData.textContent = '';
        erroreOra.textContent = '';
        inputData.classList.remove('input-error');
        inputOra.classList.remove('input-error');

        // La data è obbligatoria e deve essere almeno successiva alla giornata corrente
        if (inputData.value === '') {
            erroreData.textContent = 'Seleziona una data per la visita.';
            inputData.classList.add('input-error');
            formValido = false;
        } else if (inputData.value < dataMinima) {
            erroreData.textContent = 'La visita deve essere prenotata almeno dal giorno successivo.';
            inputData.classList.add('input-error');
            formValido = false;
        }

        // L'orario è obbligatorio e deve rientrare nella fascia prevista per le visite
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
            // preventDefault blocca il normale submit HTML soltanto quando almeno un controllo non è superato
            event.preventDefault();
        }
    });
}
</script>

<?php require 'includes/footer.php'; ?>