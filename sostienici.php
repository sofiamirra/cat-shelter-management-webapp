<?php
/*
 * Pagina dedicata alle modalità di sostegno al rifugio
 * Presenta adozioni del cuore, adozioni a distanza, donazioni materiali e FAQ
 */

// La sessione viene inizializzata dall'header comune prima di generare l'HTML della pagina
require 'includes/header.php';
?>

<!-- Intestazione principale della pagina e introduzione alle modalità di sostegno -->
<div class="page-wrapper sostienici-page-wrapper">
    <header class="section-header sostienici-main-header">
        <h1>Sostieni il Parco delle Fusa</h1>

        <!-- Il divisore ha funzione solo decorativa: aria-hidden lo esclude dalle tecnologie assistive -->
        <div class="paw-divider" aria-hidden="true">
            <span class="line"></span>
            <img src="assets/img/icona_zampette.png" width="128" height="128" alt="" class="paw-divider-icon">
            <span class="line"></span>
        </div>

        <p class="header-subtitle">Il nostro gattile si sostiene solo grazie al cuore di persone come te. Aiutaci a garantire pappe, cure mediche e un rifugio caldo ai mici sfortunati.</p>
    </header>

    <!-- Sezione raggiungibile direttamente dai collegamenti della Home tramite l'id dell'ancora -->
    <section id="adozioni-cuore" class="sostienici-cuore-section">

        <!-- Il contenitore raccoglie il testo informativo e le coordinate necessarie per effettuare la donazione -->
        <div class="adozione-cuore-content">
            <div class="adozione-cuore-text">
                <h2>Adozioni del Cuore</h2>
                <p>Sostieni cure e terapie per gatti con disabilità o patologie, aiutandoli a ricevere l'assistenza necessaria. Le Adozioni del Cuore sono dedicate ai nostri ospiti più fragili, che possono avere maggiori difficoltà nel trovare una famiglia. Puoi contribuire alle loro cure con una donazione libera, senza un importo minimo. Ogni contributo aiuta a garantire le cure necessarie e una migliore qualità di vita.</p>
            </div>

            <div class="info-bancarie-box">
                <h3>Coordinate per la donazione</h3>

                <p><strong>Intestato a:</strong> Parco delle Fusa - Torino</p>
                <p><strong>Causale:</strong> Erogazione liberale - Adozione del Cuore</p>

                <!-- L'IBAN viene mostrato come testo non modificabile e può essere copiato tramite il pulsante associato -->
                <div class="iban-copy-wrapper">
                    <div class="iban-code" id="iban-donazione">IT12 A345 6789 0123 4567 8901 234</div>

                    <!-- Il pulsante usa aria-label perché l'icona grafica da sola non fornisce un nome accessibile -->
                    <button type="button" class="btn-copia-iban" id="btn-copia-iban" aria-label="Copia IBAN">

                        <!-- L'icona è solo grafica perché la funzione del pulsante è già descritta da aria-label -->
                        <span class="copy-icon" aria-hidden="true"></span>
                    </button>

                    <!-- aria-live="polite" annuncia l'esito della copia alle tecnologie assistive senza interrompere bruscamente la lettura in corso -->
                    <span class="copy-feedback" id="copy-feedback" aria-live="polite"></span>
                </div>

                <div class="cinque-per-mille-box">
                    Puoi anche destinare il tuo <strong>5x1000</strong><br>
                    indicando il Codice Fiscale: <strong>90012345678</strong>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Sezione delle adozioni a distanza raggiungibile direttamente tramite l'id dell'ancora -->
<section id="adozioni-distanza" class="section-padding">
    <div class="section-container">
        <header class="section-header">
            <h2>Adozioni a Distanza</h2>

            <div class="paw-divider" aria-hidden="true">
                <span class="line"></span>
                <img src="assets/img/icona_zampette_bianche.png" width="128" height="128" alt="" class="paw-divider-icon">
                <span class="line"></span>
            </div>

            <p class="header-subtitle">Non puoi portare un micio a casa? Diventa il suo angelo custode fino all'adozione scegliendo uno dei nostri piani di supporto mensile.</p>
        </header>

        <div class="piani-grid">

            <!-- Ogni piano viene rappresentato come article perché costituisce un contenuto autonomo con titolo, prezzo e caratteristiche -->
            <article class="piano-card">

                <!-- Le medaglie sono decorative perché il livello del piano è già indicato dal titolo della card -->
                <img src="assets/img/icona_bronzo.png" width="160" height="160" alt="">

                <h3>Adozione Bronzo</h3>

                <div class="piano-prezzo">10€ <span>/ mese</span></div>

                <!-- Le caratteristiche del piano vengono organizzate come lista perché rappresentano elementi dello stesso insieme -->
                <ul class="piano-caratteristiche">
                    <li>Contributo per cibo e lettiera quotidiana</li>
                    <li>Attestato digitale di adozione a distanza</li>
                    <li>Una foto aggiornata del micio ogni mese</li>
                </ul>
            </article>

            <article class="piano-card piano-evidenziato">
                <img src="assets/img/icona_argento.png" width="160" height="160" alt="">

                <h3>Adozione Argento</h3>

                <div class="piano-prezzo">30€ <span>/ mese</span></div>

                <ul class="piano-caratteristiche">
                    <li>Contributo per cibo, vaccini e antiparassitari</li>
                    <li>Attestato digitale e foto aggiornata ogni mese</li>
                    <li>Un video dedicato del micio ogni due settimane</li>
                </ul>
            </article>

            <article class="piano-card">
                <img src="assets/img/icona_oro.png" width="160" height="160" alt="">

                <h3>Adozione Oro</h3>

                <div class="piano-prezzo">50€ <span>/ mese</span></div>

                <ul class="piano-caratteristiche">
                    <li>Contributo per cibo, lettiera e cure veterinarie</li>
                    <li>Tutti i vantaggi inclusi nell'adozione Argento</li>
                    <li>Un aggiornamento speciale sul micio ogni settimana</li>
                </ul>
            </article>
        </div>

        <!-- Testo conclusivo con il contatto necessario per attivare il piano scelto -->
        <p class="piani-info">
            Per attivare un piano, scrivi a
            <a href="mailto:adozioni@parcodellefusa.it">adozioni@parcodellefusa.it</a>
            <br>
            indicando il micio scelto tra quelli selezionati per l’adozione a distanza.
        </p>
    </div>
</section>

<!-- Sezione delle donazioni materiali raggiungibile direttamente tramite l'id dell'ancora -->
<section id="donazioni" class="section-padding">
    <div class="section-container">
        <header class="section-header">
            <h2>Donazioni Materiali</h2>

            <div class="paw-divider" aria-hidden="true">
                <span class="line"></span>
                <img src="assets/img/icona_zampette.png" width="128" height="128" alt="" class="paw-divider-icon">
                <span class="line"></span>
            </div>

            <p class="header-subtitle">Vuoi aiutare il rifugio senza adottare? Dona cibo, coperte, farmaci o accessori e contribuisci alle necessità quotidiane dei nostri ospiti.</p>
        </header>

        <!-- Il banner raccoglie luogo, orari e recapiti utili per consegnare le donazioni materiali -->
        <div class="contact-banner">
            <div class="contact-banner-item">

                <!-- Le icone sono decorative perché ciascun blocco contiene già un'etichetta testuale -->
                <img src="assets/img/icona_posizione_scura.png" width="128" height="128" alt="">

                <h3>Punto di Raccolta</h3>
                <p>I volontari ritirano i vostri aiuti in<br>Strada Val Salice 123, Torino</p>
            </div>

            <div class="contact-banner-item">
                <img src="assets/img/icona_orari.png" width="128" height="128" alt="">

                <h3>Orari di Ritiro</h3>
                <p>Tutti i giorni, 16:00 - 18:00<br>(senza appuntamento)</p>
            </div>

            <div class="contact-banner-item">
                <img src="assets/img/icona_telefono_scuro.png" width="128" height="128" alt="">

                <h3>Contatti</h3>
                <p>
                    <a href="tel:+390111234567" class="contact-link">+39 011 123 4567</a><br>
                    <a href="mailto:info@parcodellefusa.it" class="contact-link">info@parcodellefusa.it</a>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Sezione delle domande frequenti -->
<section class="faq-section section-padding">
    <div class="section-container">
        <header class="section-header">
            <h2>F.A.Q. - Domande Frequenti</h2>

            <div class="paw-divider" aria-hidden="true">
                <span class="line"></span>
                <img src="assets/img/icona_zampette_bianche.png" width="128" height="128" alt="" class="paw-divider-icon">
                <span class="line"></span>
            </div>
        </header>

        <!-- Le FAQ usano details e summary senza richiedere JavaScript -->
        <div class="faq-accordion">

            <details>
                <summary>Come mi preparo all'arrivo del micio a casa?</summary>
                <p>Prepara una stanza sicura e tranquilla, con lettiera, ciotole distanti dalla lettiera, tiragraffi e nascondigli. Lascia che il gatto esplori gradualmente la casa, rispettando i suoi tempi e permettendogli di ambientarsi senza forzature.</p>
            </details>

            <details>
                <summary>I gatti del rifugio sono vaccinati e testati?</summary>
                <p>Sì. Prima dell’adozione, i gatti adulti vengono sottoposti ai test per FIV e FeLV, vaccinati, trattati contro pulci e parassiti intestinali e sterilizzati. Per i cuccioli viene fornito un piano vaccinale da completare con il proprio veterinario.</p>
            </details>

            <details>
                <summary>Avete dei veterinari di riferimento per le emergenze?</summary>
                <p>Sì, collaboriamo con diverse cliniche veterinarie della provincia di Torino. Durante il primo anno dopo l’adozione e per i gatti in Adozione del Cuore, sono disponibili consulenze agevolate presso i nostri veterinari di riferimento.</p>
            </details>

            <details>
                <summary>Posso adottare se ho già altri animali in casa?</summary>
                <p>Sì. Valuteremo insieme il carattere del gatto e ti accompagneremo nell’inserimento graduale. Ti forniremo indicazioni pratiche per la gestione degli spazi e lo scambio degli odori, favorendo una convivenza serena con cani o altri gatti.</p>
            </details>

            <details>
                <summary>Cosa succede se l'inserimento non funziona?</summary>
                <p>Non sarai lasciato solo. I nostri volontari con esperienza nel comportamento felino offrono supporto e consulenze anche dopo l’adozione. Se l’incompatibilità dovesse risultare insuperabile, il gatto resterà sempre sotto la nostra tutela.</p>
            </details>
        </div>
    </div>
</section>

<script>
/*
 * Funzione Vanilla JavaScript per la copia dell'IBAN
 * Recupera il valore mostrato nella pagina, rimuove gli spazi e lo copia negli appunti
 * Il risultato dell'operazione viene comunicato nel contenitore aria-live
 */

// Il riferimento al pulsante viene dichiarato const perché viene assegnato una volta e non viene successivamente riassegnato
const btnCopiaIban = document.getElementById('btn-copia-iban');

// Al click viene eseguita la procedura di lettura e copia dell'IBAN
btnCopiaIban.addEventListener('click', function() {

    // textContent legge l'IBAN mostrato nella pagina e la regex /\s+/g rimuove tutti gli spazi prima della copia
    const iban = document.getElementById('iban-donazione').textContent.replace(/\s+/g, '');

    // Recupera il contenitore in cui verrà mostrato l'esito dell'operazione
    const feedback = document.getElementById('copy-feedback');

    // Il messaggio informa l'utente dell'esito della copia
    navigator.clipboard.writeText(iban)
        .then(function() {

            // Se la copia riesce viene mostrato il messaggio di conferma
            feedback.textContent = 'Copiato!';

            // Il messaggio viene cancellato automaticamente dopo 1,8 secondi
            setTimeout(function() {
                feedback.textContent = '';
            }, 1800);
        })
        .catch(function() {

            // Se il browser non permette la copia viene mostrato un messaggio di errore
            feedback.textContent = 'Copia non riuscita';
        });
});
</script>

<?php require 'includes/footer.php'; ?>