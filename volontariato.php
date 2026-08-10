<?php
/*
 * Pagina dedicata al volontariato
 * Presenta le attività disponibili e permette agli utenti autenticati
 * di scegliere uno o più turni tra quelli che non hanno raggiunto il limite
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require 'includes/header.php';
?>

<!-- Presentazione del volontariato -->
<div class="page-wrapper volontariato-intro-wrapper">
    <header class="section-header volontariato-header">
        <h2>Diventa Volontario</h2>
        <div class="paw-divider" aria-hidden="true">
            <span class="line"></span>
            <img src="assets/img/icona_zampette.png" alt="" class="paw-divider-icon">
            <span class="line"></span>
        </div>
    </header>

    <!-- La classe aggiuntiva sostituisce il precedente stile CSS inline -->
    <div class="volontariato-intro-content section-padding volontariato-intro-compatta">
        <div class="volontariato-intro-text">
            <p>Il Parco delle Fusa ha bisogno di persone appassionate per garantire il benessere quotidiano dei nostri ospiti felini. Diventare volontario significa donare una parte del proprio tempo per migliorare la vita dei gatti in attesa di adozione. Non serve esperienza pregressa, ma solo tanta affidabilità, costanza e un amore incondizionato per gli animali.</p>

            <div class="volontariato-btn-wrapper">
                <a href="#prenota" class="btn-solid-dark">Prenota il tuo Turno</a>
            </div>
        </div>

        <div class="volontariato-intro-image">
            <img src="assets/img/gatto_volontariato.png" alt="Volontario con gatto">
        </div>
    </div>
</div>

<!-- Mansioni principali -->
<section class="ruoli-volontariato section-padding">
    <div class="ruoli-container">
        <header class="section-header volontariato-header volontariato-header-mansioni">
            <h2>Le Mansioni del Volontario</h2>
            <div class="paw-divider" aria-hidden="true">
                <span class="line"></span>
                <img src="assets/img/icona_zampette_bianche.png" alt="" class="paw-divider-icon">
                <span class="line"></span>
            </div>
        </header>

        <div class="ruoli-grid">
            <article class="ruolo-card">
                <img src="assets/img/icona_mattina.png" alt="Turno del mattino" class="icon-png-large">
                <h3>Turno del Mattino</h3>
                <p>Aiutaci a iniziare la giornata dei felini preparando il cibo e sistemando gli spazi.</p>
            </article>

            <article class="ruolo-card">
                <img src="assets/img/icona_sera.png" alt="Turno della sera" class="icon-png-large">
                <h3>Turno della Sera</h3>
                <p>Assicura ai mici una serena buonanotte. Rifornirai il cibo secco e gli dedicherai qualche coccola serale.</p>
            </article>

            <article class="ruolo-card">
                <img src="assets/img/icona_gioco.png" alt="Socializzazione" class="icon-png-large">
                <h3>Socializzazione</h3>
                <p>Disponibilità nella fascia pomeridiana per favorire la socializzazione dei mici più timorosi e diffidenti.</p>
            </article>

            <article class="ruolo-card">
                <img src="assets/img/icona_eventi.png" alt="Gestione eventi" class="icon-png-large">
                <h3>Gestione Eventi</h3>
                <p>Cerchiamo aiuto per la gestione degli eventi sul territorio, per i mercatini solidali e le raccolte fondi.</p>
            </article>
        </div>
    </div>
</section>

<!-- Prenotazione dei turni -->
<div id="prenota"></div>

<div class="page-wrapper volontariato-form-wrapper section-padding">
    <header class="section-header volontariato-header">
        <h2>Prenota il tuo Turno</h2>
        <div class="paw-divider" aria-hidden="true">
            <span class="line"></span>
            <img src="assets/img/icona_zampette.png" alt="" class="paw-divider-icon">
            <span class="line"></span>
        </div>
        <p class="header-subtitle">Il tuo tempo è il regalo più prezioso. Seleziona una data per scoprire le fasce orarie disponibili. Accettiamo un massimo di 2 volontari per turno.</p>
    </header>

    <!-- I messaggi prodotti dalla comunicazione asincrona vengono inseriti qui -->
    <div id="messaggio-esito" class="d-none alert-wrapper mb-4" aria-live="polite"></div>

    <?php if (isset($_SESSION['username'])): ?>
        <div class="prenotazione-wrapper volontariato-prenotazione-box">
            <!-- JavaScript intercetta il submit e invia i dati al backend tramite fetch -->
            <form action="actions/processa_volontariato.php" method="POST" id="form-volontariato" novalidate>
                <div class="form-group">
                    <label for="data_turno" class="form-label-title">Seleziona la data del turno:</label>
                    <input type="date" id="data_turno" name="data_turno" class="input-data-large input-data-full-width">
                </div>

                <div class="form-group" id="sezione-fasce">
                    <p class="form-label-title mt-2">Fasce orarie disponibili (selezionane una o più):</p>

                    <div class="fasce-orarie-container" id="contenitore-orari">
                        <label class="fascia-oraria-label" id="label-mattina">
                            <input type="checkbox" name="fasce[]" value="09:00:00" class="chk-fascia" disabled>
                            <span>Mattina (09 - 13)</span>
                        </label>

                        <label class="fascia-oraria-label" id="label-pomeriggio">
                            <input type="checkbox" name="fasce[]" value="14:00:00" class="chk-fascia" disabled>
                            <span>Pomeriggio (13 - 17)</span>
                        </label>

                        <label class="fascia-oraria-label" id="label-sera">
                            <input type="checkbox" name="fasce[]" value="18:00:00" class="chk-fascia" disabled>
                            <span>Sera (17 - 21)</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-solid-dark w-100 mt-2" id="btn-invia-turno" disabled>Conferma Disponibilità</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert-wrapper mb-4 text-center">
            <div class="auth-alert-success">
                Vuoi unirti alla nostra squadra di volontari?<br>
                <a href="login.php?ritorno=volontariato.php" class="auth-alert-link">Accedi o Registrati</a> per poter prenotare i tuoi turni.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['username'])): ?>
<script>
/*
 * Gestione Vanilla JavaScript del form di volontariato
 * Prima dell'invio viene interrogato il server per conoscere i posti disponibili
 */
document.addEventListener('DOMContentLoaded', function() {
    const dataInput = document.getElementById('data_turno');
    const btnInvia = document.getElementById('btn-invia-turno');
    const checkboxes = document.querySelectorAll('.chk-fascia');
    const form = document.getElementById('form-volontariato');
    const msgBox = document.getElementById('messaggio-esito');

    /*
     * La data minima viene costruita utilizzando la data locale
     * in modo da evitare differenze dovute alla conversione del fuso orario
     */
    const domani = new Date();
    domani.setDate(domani.getDate() + 1);

    const anno = domani.getFullYear();
    const mese = String(domani.getMonth() + 1).padStart(2, '0');
    const giorno = String(domani.getDate()).padStart(2, '0');
    const dataMinima = anno + '-' + mese + '-' + giorno;

    dataInput.min = dataMinima;

    // Il pulsante è disponibile solamente quando almeno una fascia valida è selezionata
    function aggiornaPulsante() {
        const almenoUnaSelezionata = Array.from(checkboxes).some(function(checkbox) {
            return checkbox.checked && !checkbox.disabled;
        });

        btnInvia.disabled = !almenoUnaSelezionata;
    }

    // Ripristina tutte le fasce prima di verificare una nuova data
    function resetFasce() {
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = false;
            checkbox.disabled = true;
            checkbox.parentElement.classList.remove('pieno', 'selezionato');
        });

        aggiornaPulsante();
    }

    // Nasconde il messaggio precedente senza rimuovere il contenitore dalla pagina
    function nascondiMessaggio() {
        msgBox.classList.add('d-none');
        msgBox.innerHTML = '';
    }

    /*
     * Crea il messaggio di conferma o di errore senza utilizzare eventi
     * JavaScript direttamente negli attributi HTML
     */
    function mostraMessaggio(tipo, messaggio) {
        msgBox.innerHTML = '';

        const alert = document.createElement('div');
        alert.className = tipo === 'success'
            ? 'auth-alert-success alert-dismissible'
            : 'auth-alert-danger alert-dismissible';

        const testoPrincipale = document.createElement('strong');

        if (tipo === 'success') {
            testoPrincipale.className = 'messaggio-successo-titolo';
            const iconaSuccesso = document.createElement('img');
            iconaSuccesso.src = 'assets/img/icona_spunta_successo.png';
            iconaSuccesso.alt = '';
            iconaSuccesso.className = 'icona-successo';

            testoPrincipale.appendChild(iconaSuccesso);
            testoPrincipale.appendChild(document.createTextNode(' ' + messaggio));
            alert.appendChild(testoPrincipale);
            alert.appendChild(document.createElement('br'));
            alert.appendChild(document.createTextNode('Ti aspettiamo alla struttura, dove verrai istruito dagli altri volontari. A presto!'));
        } else {
            testoPrincipale.textContent = 'Errore:';
            alert.appendChild(testoPrincipale);
            alert.appendChild(document.createTextNode(' ' + messaggio));
        }

        const btnChiudi = document.createElement('button');
        btnChiudi.type = 'button';
        btnChiudi.className = 'btn-close-alert';
        btnChiudi.setAttribute('aria-label', 'Chiudi');
        btnChiudi.innerHTML = '&times;';

        btnChiudi.addEventListener('click', function() {
            msgBox.classList.add('d-none');
        });

        alert.appendChild(btnChiudi);
        msgBox.appendChild(alert);
        msgBox.classList.remove('d-none');
    }

    /*
     * Per ogni data selezionata il server restituisce il numero di iscritti
     * Le fasce con due volontari vengono mantenute disabilitate
     */
    function verificaDisponibilita(dataScelta, nascondiEsito) {
        if (nascondiEsito) {
            nascondiMessaggio();
        }

        resetFasce();

        fetch('actions/api_verifica_turni.php?data=' + encodeURIComponent(dataScelta))
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Errore nella risposta del server');
                }

                return response.json();
            })
            .then(function(risposta) {
                if (risposta.status !== 'success') {
                    throw new Error('Impossibile verificare i turni');
                }

                checkboxes.forEach(function(checkbox) {
                    const fascia = checkbox.value;
                    const iscritti = risposta.data[fascia] || 0;

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

    dataInput.addEventListener('change', function() {
        const dataScelta = dataInput.value;

        if (dataScelta === '' || dataScelta < dataMinima) {
            resetFasce();
            return;
        }

        verificaDisponibilita(dataScelta, true);
    });

    // La classe selezionato verrà utilizzata anche dal CSS per evidenziare la fascia scelta
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
        event.preventDefault();

        const fasceSelezionate = Array.from(checkboxes).filter(function(checkbox) {
            return checkbox.checked && !checkbox.disabled;
        });

        if (dataInput.value === '' || dataInput.value < dataMinima || fasceSelezionate.length === 0) {
            return;
        }

        const formData = new FormData(form);

        btnInvia.disabled = true;
        btnInvia.textContent = 'Elaborazione in corso...';

        fetch('actions/processa_volontariato.php', {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Errore nella risposta del server');
            }

            return response.json();
        })
        .then(function(risposta) {
            if (risposta.status === 'success') {
                mostraMessaggio('success', risposta.message);

                form.reset();
                resetFasce();
            } else {
                /*
                 * Il codice LIMIT_EXCEEDED viene intercettato esplicitamente
                 * Dopo l'errore la disponibilità viene interrogata di nuovo
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
            console.error('Errore durante il salvataggio del turno:', error);
            mostraMessaggio('error', 'Impossibile contattare il server. Riprova più tardi.');
            aggiornaPulsante();
        })
        .finally(function() {
            btnInvia.textContent = 'Conferma Disponibilità';
        });
    });
});
</script>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>