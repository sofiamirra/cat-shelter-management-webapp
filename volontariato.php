<?php
/*
 * Pagina dedicata al volontariato
 * Presenta le attività disponibili e permette agli utenti autenticati
 * di scegliere uno o più turni tra quelli che non hanno raggiunto il limite
 */

// La sessione viene inizializzata dall'header comune prima di generare l'HTML della pagina
require 'includes/header.php';
?>

<!-- Intestazione principale della pagina dedicata al volontariato -->
<div class="page-wrapper volontariato-intro-wrapper">
    <header class="section-header volontariato-header">
        <h1>Diventa Volontario</h1>

        <!-- Il divisore è puramente decorativo, quindi viene escluso dalle tecnologie assistive -->
        <div class="paw-divider" aria-hidden="true">
            <span class="line"></span>
            <img src="assets/img/icona_zampette.png" width="128" height="128" alt="" class="paw-divider-icon">
            <span class="line"></span>
        </div>
    </header>

    <!-- Contenuto introduttivo con testo informativo e immagine rappresentativa del volontariato -->
    <div class="volontariato-intro-content">
        <div class="volontariato-intro-text">
            <p>Il Parco delle Fusa ha bisogno di persone appassionate per garantire il benessere quotidiano dei nostri ospiti felini. Diventare volontario significa donare una parte del proprio tempo per migliorare la vita dei gatti in attesa di adozione. Non serve esperienza pregressa, ma solo tanta affidabilità, costanza e un amore incondizionato per gli animali.</p>

            <div class="volontariato-btn-wrapper">
                <!-- Il collegamento interno porta direttamente alla sezione del form tramite l'id prenota -->
                <a href="#prenota" class="btn-solid-dark">Prenota il tuo Turno</a>
            </div>
        </div>

        <div class="volontariato-intro-image">
        <picture>
            <source
                media="(max-width: 48rem)"
                srcset="assets/img/gatto_volontariato_mobile.webp"
            >
            <img
                src="assets/img/gatto_volontariato.webp"
                width="1080"
                height="720"
                fetchpriority="high"
                alt="Volontario del rifugio con un gatto"
            >
        </picture>
        </div>
    </div>
</div>

<!-- Sezione dedicata alle principali attività svolte dai volontari -->
<section class="info-section">
    <div class="section-container">
        <header class="section-header volontariato-header">
            <h2>Le Mansioni del Volontario</h2>

            <div class="paw-divider" aria-hidden="true">
                <span class="line"></span>
                <img src="assets/img/icona_zampette_bianche.png" width="128" height="128" alt="" class="paw-divider-icon">
                <span class="line"></span>
            </div>
        </header>

        <div class="info-grid">

            <!-- Ogni mansione è rappresentata come article perché costituisce un contenuto autonomo con titolo e descrizione -->
            <article class="info-card">

                <!-- Le icone sono decorative perché ogni mansione è già identificata dal relativo h3 -->
                <img src="assets/img/icona_mattina.png" width="160" height="160" alt="">

                <h3>Turno del Mattino</h3>
                <p>Aiutaci a iniziare la giornata dei felini preparando il cibo e sistemando gli spazi.</p>
            </article>

            <article class="info-card">
                <img src="assets/img/icona_sera.png" width="160" height="160" alt="">

                <h3>Turno della Sera</h3>
                <p>Assicura ai mici una serena buonanotte. Rifornirai il cibo secco e gli dedicherai qualche coccola serale.</p>
            </article>

            <article class="info-card">
                <img src="assets/img/icona_gioco.png" width="160" height="160" alt="">

                <h3>Socializzazione</h3>
                <p>Disponibilità nella fascia pomeridiana per favorire la socializzazione dei mici più timorosi e diffidenti.</p>
            </article>

            <article class="info-card">
                <img src="assets/img/icona_eventi.png" width="160" height="160" alt="">

                <h3>Gestione Eventi</h3>
                <p>Cerchiamo aiuto per la gestione degli eventi sul territorio, per i mercatini solidali e le raccolte fondi.</p>
            </article>
        </div>
    </div>
</section>

<!-- Sezione raggiungibile dal collegamento "Prenota il tuo Turno" tramite l'id prenota -->
<div id="prenota" class="page-wrapper volontariato-form-wrapper">
    <header class="section-header volontariato-header">
        <h2>Prenota il tuo Turno</h2>

        <div class="paw-divider" aria-hidden="true">
            <span class="line"></span>
            <img src="assets/img/icona_zampette.png" width="128" height="128" alt="" class="paw-divider-icon">
            <span class="line"></span>
        </div>

        <p class="header-subtitle">Il tuo tempo è il regalo più prezioso. Seleziona una data per scoprire le fasce orarie disponibili. Accettiamo un massimo di 2 volontari per turno.</p>
    </header>

    <!-- Il feedback dei turni viene annunciato anche alle tecnologie assistive -->
    <div id="messaggio-esito" class="d-none alert-wrapper" aria-live="polite"></div>

    <!-- Il form viene mostrato soltanto se lo username è presente nella sessione dell'utente autenticato -->
    <?php if (isset($_SESSION['username'])): ?>
        <div class="form-card">

            <!-- novalidate lascia al Vanilla JavaScript i controlli lato client; il submit viene intercettato e inviato al backend tramite fetch -->
            <form action="actions/processa_volontariato.php" method="POST" id="form-volontariato" novalidate>

                <div class="form-group">

                    <!-- La data viene scelta prima delle fasce perché serve per interrogare il server sulla disponibilità -->
                    <label for="data_turno" class="form-label-title">Seleziona la data del turno:</label>
                    <input type="date" id="data_turno" name="data_turno" class="input-data-large">
                </div>

                <!-- fieldset raggruppa le checkbox correlate e legend ne fornisce il titolo -->
                <fieldset class="form-group" id="sezione-fasce">
                    <legend class="form-label-title">Fasce orarie disponibili (selezionane una o più):</legend>

                    <!-- Le fasce partono disabilitate e vengono abilitate dal JavaScript solo dopo la verifica della disponibilità sul server -->
                    <div class="fasce-orarie-container">

                        <!-- Ogni checkbox è contenuta nel proprio label, quindi il testo della fascia è direttamente associato al controllo -->
                        <label class="fascia-oraria-label">
                            <input type="checkbox" id="fascia-mattina" name="fasce[]" value="09:00:00" class="chk-fascia" disabled>
                            <span>Mattina (09 - 13)</span>
                        </label>

                        <label class="fascia-oraria-label">
                            <input type="checkbox" id="fascia-pomeriggio" name="fasce[]" value="13:00:00" class="chk-fascia" disabled>
                            <span>Pomeriggio (13 - 17)</span>
                        </label>

                        <label class="fascia-oraria-label">
                            <input type="checkbox" id="fascia-sera" name="fasce[]" value="17:00:00" class="chk-fascia" disabled>
                            <span>Sera (17 - 21)</span>
                        </label>
                    </div>
                </fieldset>

                <!-- Il pulsante parte disabilitato e viene attivato solo quando è selezionata almeno una fascia ancora disponibile -->
                <button type="submit" class="btn-solid-dark" id="btn-invia-turno" disabled>
                    Conferma Disponibilità
                </button>
            </form>
        </div>

    <!-- Il visitatore non autenticato può leggere la pagina ma deve accedere per prenotare i turni -->
    <?php else: ?>
        <div class="alert-wrapper">
            <div class="alert-success">
                Vuoi unirti alla nostra squadra di volontari?<br>
                <a href="login.php?ritorno=volontariato.php">Accedi o Registrati</a> per poter prenotare i tuoi turni.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Lo script del form viene generato solo per gli utenti autenticati, perché per gli altri il form non esiste nel DOM -->
<?php if (isset($_SESSION['username'])): ?>
<script>
/*
 * Gestione Vanilla JavaScript del form di volontariato
 * Lo script imposta la data minima, verifica in modo asincrono la disponibilità dei turni
 * aggiorna lo stato delle checkbox e invia il form al backend tramite fetch
 */

// La logica viene inizializzata quando gli elementi HTML della pagina sono stati caricati nel DOM
document.addEventListener('DOMContentLoaded', function() {

    // I riferimenti agli elementi del DOM sono const perché vengono assegnati una volta e non vengono successivamente riassegnati
    const dataInput = document.getElementById('data_turno');
    const btnInvia = document.getElementById('btn-invia-turno');

    // querySelectorAll recupera tutte le checkbox delle fasce e restituisce una NodeList
    const checkboxes = document.querySelectorAll('.chk-fascia');

    const form = document.getElementById('form-volontariato');
    const msgBox = document.getElementById('messaggio-esito');

    /*
     * La data minima viene costruita utilizzando la data locale
     * in modo da evitare differenze dovute alla conversione del fuso orario
     */

    // L'oggetto Date è dichiarato const perché il riferimento non cambia, mentre setDate ne aggiorna il valore interno
    const domani = new Date();
    domani.setDate(domani.getDate() + 1);

    const anno = domani.getFullYear();

    // Mese e giorno vengono convertiti in stringhe a due cifre per costruire il formato YYYY-MM-DD dell'input date
    const mese = String(domani.getMonth() + 1).padStart(2, '0');
    const giorno = String(domani.getDate()).padStart(2, '0');
    const dataMinima = anno + '-' + mese + '-' + giorno;

    // Il limite minimo viene applicato anche all'input HTML per impedire la scelta di date precedenti
    dataInput.min = dataMinima;

    /*
     * Controlla se esiste almeno una checkbox selezionata e non disabilitata
     * Il pulsante di submit viene abilitato solo in quel caso
     */
    function aggiornaPulsante() {

        // Array.from converte la NodeList in un array e some verifica se almeno un elemento soddisfa la condizione
        const almenoUnaSelezionata = Array.from(checkboxes).some(function(checkbox) {
            return checkbox.checked && !checkbox.disabled;
        });

        btnInvia.disabled = !almenoUnaSelezionata;
    }

    /*
     * Ripristina tutte le fasce prima di verificare una nuova data
     * Le checkbox tornano non selezionate e disabilitate
     */
    function resetFasce() {

        // forEach applica il reset a tutte le checkbox del gruppo
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = false;
            checkbox.disabled = true;

            // parentElement è il label che contiene la checkbox e su cui vengono applicate le classi grafiche
            checkbox.parentElement.classList.remove('pieno', 'selezionato');
        });

        aggiornaPulsante();
    }

    /*
     * Nasconde il banner precedente e ne svuota il contenuto
     * prima di mostrare l'esito di una nuova operazione
     */
    function nascondiMessaggio() {
        msgBox.classList.add('d-none');
        msgBox.innerHTML = '';
    }

    /*
     * Costruisce dinamicamente il banner di successo o errore
     * creando gli elementi nel DOM e collegando il pulsante di chiusura con addEventListener
     */
    function mostraMessaggio(tipo, messaggio) {

        // tipo determina quale stile applicare, mentre messaggio contiene il testo restituito dal backend

        // Il contenitore viene svuotato prima di costruire il nuovo messaggio
        msgBox.innerHTML = '';

        // createElement crea nuovi nodi HTML che verranno poi inseriti nel DOM
        const alert = document.createElement('div');

        // L'operatore ternario assegna la classe di successo oppure quella di errore in base al tipo ricevuto
        alert.className = tipo === 'success'
            ? 'alert-success alert-dismissible'
            : 'alert-danger alert-dismissible';

        // Il testo principale viene evidenziato semanticamente con strong
        const testoPrincipale = document.createElement('strong');

        if (tipo === 'success') {
            testoPrincipale.className = 'messaggio-successo-titolo';

            // L'icona di conferma è decorativa perché il messaggio testuale comunica già l'esito
            const iconaSuccesso = document.createElement('img');
            iconaSuccesso.src = 'assets/img/icona_spunta_successo.png';
            iconaSuccesso.width = 128;
            iconaSuccesso.height = 128;
            iconaSuccesso.alt = '';
            iconaSuccesso.className = 'icona-successo';

            // appendChild inserisce progressivamente i nodi creati all'interno del banner
            testoPrincipale.appendChild(iconaSuccesso);

            // createTextNode inserisce il messaggio come testo invece di interpretarlo come markup HTML
            testoPrincipale.appendChild(document.createTextNode(' ' + messaggio));

            alert.appendChild(testoPrincipale);
            alert.appendChild(document.createElement('br'));
            alert.appendChild(document.createTextNode('Ti aspettiamo alla struttura, dove verrai istruito dagli altri volontari. A presto!'));
        } else {
            testoPrincipale.textContent = 'Errore:';
            alert.appendChild(testoPrincipale);
            alert.appendChild(document.createTextNode(' ' + messaggio));
        }

        // Il pulsante di chiusura viene creato via DOM perché anche il banner è generato dinamicamente
        const btnChiudi = document.createElement('button');
        btnChiudi.type = 'button';
        btnChiudi.className = 'btn-close-alert';

        // aria-label fornisce un nome accessibile al pulsante che visivamente contiene solo il simbolo ×
        btnChiudi.setAttribute('aria-label', 'Chiudi');

        // Il simbolo di chiusura viene inserito come semplice testo
        btnChiudi.textContent = '×';

        btnChiudi.addEventListener('click', function() {
            msgBox.classList.add('d-none');
        });

        alert.appendChild(btnChiudi);
        msgBox.appendChild(alert);
        msgBox.classList.remove('d-none');
    }

    /*
     * Interroga l'API PHP per la data scelta e riceve in JSON il numero di iscritti per ciascuna fascia
     * Le fasce che hanno già raggiunto due volontari restano disabilitate
     */
    function verificaDisponibilita(dataScelta, nascondiEsito) {

        // nascondiEsito decide se rimuovere il messaggio precedente prima della nuova verifica
        if (nascondiEsito) {
            nascondiMessaggio();
        }

        // Prima della nuova risposta tutte le fasce vengono riportate allo stato sicuro disabilitato
        resetFasce();

        // fetch esegue una richiesta asincrona all'API passando la data come parametro GET
        // encodeURIComponent prepara il valore perché possa essere inserito correttamente nella URL
        fetch('actions/api_verifica_turni.php?data=' + encodeURIComponent(dataScelta))
            .then(function(response) {

                // response.ok verifica che la risposta HTTP sia andata a buon fine
                if (!response.ok) {
                    throw new Error('Errore nella risposta del server');
                }

                // La risposta JSON del PHP viene convertita in un oggetto JavaScript
                return response.json();
            })
            .then(function(risposta) {

                // Anche con una risposta HTTP valida viene controllato lo stato applicativo restituito dal backend
                if (risposta.status !== 'success') {
                    throw new Error('Impossibile verificare i turni');
                }

                checkboxes.forEach(function(checkbox) {

                    // Il value della checkbox coincide con la chiave usata dal JSON per quella fascia oraria
                    const fascia = checkbox.value;

                    // Se il backend non restituisce un conteggio per la fascia viene usato 0 come valore di default
                    const iscritti = risposta.data[fascia] || 0;

                    // La traccia consente al massimo due volontari per fascia, quindi una fascia piena resta disabilitata
                    if (iscritti >= 2) {
                        checkbox.disabled = true;
                        checkbox.parentElement.classList.add('pieno');
                    } else {
                        checkbox.disabled = false;
                    }
                });

                aggiornaPulsante();
            })
            .catch(function(error) {

                /*
                 * Se la disponibilità non può essere verificata le fasce restano disabilitate
                 * In questo modo non si permette un invio senza il controllo preventivo richiesto
                 */
                console.error('Errore nel recupero turni:', error);
                resetFasce();
                mostraMessaggio('error', 'Impossibile contattare il server. Riprova più tardi.');
            });
    }

    // Ogni cambio di data avvia una nuova verifica delle fasce disponibili
    dataInput.addEventListener('change', function() {
        const dataScelta = dataInput.value;

        // Una data vuota o precedente al minimo non deve produrre richieste al server
        if (dataScelta === '' || dataScelta < dataMinima) {
            resetFasce();
            return;
        }

        verificaDisponibilita(dataScelta, true);
    });

    /*
     * Ogni checkbox aggiorna la propria evidenziazione grafica
     * e ricalcola se il pulsante di conferma può essere abilitato
     */
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            if (checkbox.checked) {
                checkbox.parentElement.classList.add('selezionato');
            } else {
                checkbox.parentElement.classList.remove('selezionato');
            }

            aggiornaPulsante();
        });
    });

    /*
     * Il form viene inviato asincronamente solo dopo i controlli effettuati sul browser
     * Il PHP ripeterà comunque in modo indipendente tutti i controlli importanti
     */
    form.addEventListener('submit', function(event) {

        // preventDefault blocca l'invio HTML tradizionale perché i dati vengono inviati tramite fetch senza cambiare pagina
        event.preventDefault();

        // filter mantiene soltanto le fasce effettivamente selezionate e ancora abilitate
        const fasceSelezionate = Array.from(checkboxes).filter(function(checkbox) {
            return checkbox.checked && !checkbox.disabled;
        });

        // Se data o selezione non sono valide la funzione termina senza contattare il backend
        if (dataInput.value === '' || dataInput.value < dataMinima || fasceSelezionate.length === 0) {
            return;
        }

        // FormData raccoglie automaticamente i valori del form, comprese tutte le checkbox fasce[] selezionate
        const formData = new FormData(form);

        // Durante la richiesta il pulsante viene disabilitato per evitare invii ripetuti
        btnInvia.disabled = true;
        btnInvia.textContent = 'Elaborazione in corso...';

        // La seconda fetch invia i dati del form al backend tramite metodo POST
        fetch('actions/processa_volontariato.php', {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Errore nella risposta del server');
            }

            // Anche la risposta del salvataggio viene convertita dal formato JSON a un oggetto JavaScript
            return response.json();
        })
        .then(function(risposta) {
            if (risposta.status === 'success') {

                // In caso di successo viene mostrata la conferma e il form viene riportato allo stato iniziale
                mostraMessaggio('success', risposta.message);

                // reset svuota i controlli del form, mentre resetFasce ripristina anche gli stati gestiti dal JavaScript
                form.reset();
                resetFasce();
            } else {

                /*
                 * LIMIT_EXCEEDED identifica il caso in cui nel frattempo la fascia ha raggiunto due volontari
                 * Dopo l'errore la disponibilità viene interrogata nuovamente per aggiornare subito l'interfaccia
                 */
                mostraMessaggio('error', risposta.message);

                if (risposta.code === 'LIMIT_EXCEEDED' && dataInput.value !== '') {
                    verificaDisponibilita(dataInput.value, false);
                } else {
                    aggiornaPulsante();
                }
            }
        })
        .catch(function(error) {

            // Gli errori di rete o di risposta vengono registrati in console e tradotti in un messaggio comprensibile per l'utente
            console.error('Errore durante il salvataggio del turno:', error);
            mostraMessaggio('error', 'Impossibile contattare il server. Riprova più tardi.');
            aggiornaPulsante();
        })
        .finally(function() {

            // finally viene eseguito sia dopo il successo sia dopo l'errore e ripristina il testo originale del pulsante
            btnInvia.textContent = 'Conferma Disponibilità';
        });
    });
});
</script>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>