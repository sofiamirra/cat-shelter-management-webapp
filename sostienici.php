<?php
session_start();
require 'includes/header.php';
?>

<!-- ==========================================
     HERO & ADOZIONI DEL CUORE
     ========================================== -->
<div class="page-wrapper" style="padding-bottom: 4rem;">
    
    <!-- Testo Introduttivo Generale -->
    <div class="section-header" style="margin-bottom: 2rem;">
        <h2>Sostieni il Parco delle Fusa</h2>
        <div class="paw-divider">
            <div class="line"></div>
            <i class="paw">🐾</i>
            <div class="line"></div>
        </div>
        <p class="header-subtitle">
            Il nostro gattile si sostiene solo grazie al cuore di persone come te. Aiutaci a garantire pappe, cure mediche e un rifugio caldo ai mici sfortunati.
        </p>
    </div>

<!-- Sezione 1: Adozioni del Cuore -->
<section id="adozioni-cuore" class="scroll-anchor" style="padding-top: 2rem;">
        <div class="content-split">
            
            <!-- Colonna Testo (Con titolo integrato a sinistra) -->
            <div class="split-text">
                <h2 class="split-title">Adozioni del Cuore</h2>
                <p>Sostieni cure e terapie per gatti con disabilità o patologie, aiutandoli a ricevere l'assistenza necessaria. Le Adozioni del Cuore sono dedicate ai nostri ospiti più fragili, che possono avere maggiori difficoltà nel trovare una famiglia. Puoi contribuire alle loro cure con una donazione libera, senza un importo minimo. Ogni contributo aiuta a garantire le cure necessarie e una migliore qualità di vita.</p>
            </div>

            <!-- Colonna Box IBAN (Più largo e compatto) -->
            <div class="split-box info-bancarie-box">
                <h4>Coordinate per la donazione</h4>
                <p><strong>Intestato a:</strong> Parco delle Fusa - Torino</p>
                <p><strong>Causale:</strong> Erogazione liberale - Adozione del Cuore</p>
                
                <div class="iban-code">IT12 A345 6789 0123 4567 8901 234</div>
                
                <!-- Nuovo Finto Tasto / Area Arrotondata per 5x1000 -->
                <div class="box-5x1000">
                    Puoi anche destinare il tuo <strong>5x1000</strong><br>
                    indicando il Codice Fiscale: <strong>90012345678</strong>
                </div>
            </div>
            
        </div>
    </section>

</div>

<!-- ==========================================
     SEZIONE 2: ADOZIONI A DISTANZA 
     ========================================== -->
<section id="adozioni-distanza" class="scroll-anchor section-padding" style="background-color: var(--colore-bianco);">
    <div class="ruoli-container">
        
        <header class="section-header">
            <h2>Adozioni a Distanza</h2>
            <div class="paw-divider">
                <span class="line"></span><span class="paw">🐾</span><span class="line"></span>
            </div>
            <p class="header-subtitle">Non puoi portare un micio a casa? Diventa il suo angelo custode fino all'adozione scegliendo uno dei nostri piani di supporto mensile.</p>
        </header>

        <div class="tier-grid">
            <article class="tier-card">
                <img src="assets/img/icona_bronzo.png" alt="Medaglia Bronzo" class="tier-icon-img">
                <h3>Adozione Bronzo</h3>
                <div class="tier-price">10€ <span>/ mese</span></div>
                <ul class="tier-features">
                    <li>Contributo per cibo e lettiera quotidiana</li>
                    <li>Attestato digitale di adozione a distanza</li>
                    <li>Una foto aggiornata del micio ogni mese</li>
                </ul>
            </article>

            <article class="tier-card premium-tier">
                <img src="assets/img/icona_argento.png" alt="Medaglia Argento" class="tier-icon-img">
                <h3>Adozione Argento</h3>
                <div class="tier-price">30€ <span>/ mese</span></div>
                <ul class="tier-features">
                    <li>Contributo per cibo, vaccini e antiparassitari</li>
                    <li>Attestato digitale e foto aggiornata ogni mese</li>
                    <li>Un video dedicato del micio ogni due settimane</li>
                </ul>
            </article>

            <article class="tier-card">
                <img src="assets/img/icona_oro.png" alt="Medaglia Oro" class="tier-icon-img">
                <h3>Adozione Oro</h3>
                <div class="tier-price">50€ <span>/ mese</span></div>
                <ul class="tier-features">
                    <li>Contributo per cibo, lettiera e cure veterinarie</li>
                    <li>Tutti i vantaggi inclusi nell'adozione Argento</li>
                    <li>Un aggiornamento speciale sul micio ogni settimana</li>
                </ul>
            </article>
        </div>

        <div class="tier-footer-text">
            Per attivare un piano, scrivi a
            <a href="mailto:adozioni@parcodellefusa.it" class="tier-footer-link">adozioni@parcodellefusa.it</a>
            <br>
            indicando il micio scelto tra quelli selezionati per l’adozione a distanza.
        </div>
    </div>
</section>

<!-- ==========================================
     SEZIONE 3: DONAZIONI MATERIALI
     ========================================== -->
<section id="donazioni" class="scroll-anchor section-padding" style="background-color: var(--colore-sfondo-chiaro);">
    <div class="ruoli-container">
        
        <header class="section-header">
            <h2>Donazioni Materiali</h2>
            <div class="paw-divider">
                <span class="line"></span><span class="paw">🐾</span><span class="line"></span>
            </div>
            <p class="header-subtitle">Vuoi aiutare il rifugio senza adottare? Dona cibo, coperte, farmaci o accessori e contribuisci alle necessità quotidiane dei nostri ospiti.
            </p>
        </header>

        <!-- Tolto il testo inutile, lasciato solo il banner contatti -->
        <div class="donazioni-orizzontali">
        <div class="contact-banner">
                <div class="contact-banner-item">
                    <span class="contact-icon">📍</span>
                    <strong>Punto di Raccolta</strong>
                    <!-- Tolto il grassetto gigante -->
                    <span>I volontari ritirano i vostri aiuti in<br>Via Roma 123, 10100 Torino (TO)</span>
                </div>
                
                <div class="contact-banner-item">
                    <span class="contact-icon">🕒</span>
                    <strong>Orari di Ritiro</strong>
                    <!-- Tolto il grassetto gigante -->
                    <span>Tutti i giorni, 16:00 - 18:00<br>(senza appuntamento)</span>
                </div>
                
                <div class="contact-banner-item">
                    <span class="contact-icon">📞</span>
                    <strong>Contatti</strong>
                    <span>+39 011 123 4567<br><a href="mailto:info@parcodellefusa.it" class="contact-link">info@parcodellefusa.it</a></span>
                </div>
            </div>

    </div>
</section>

<!-- ==========================================
     SEZIONE 4: F.A.Q. 
     (Sfondo bianco forzato per spezzare dal beige sopra)
     ========================================== -->
<section class="scroll-anchor section-padding" style="background-color: var(--colore-bianco);">
    <div class="ruoli-container">
        
        <header class="section-header">
            <h2>F.A.Q. - Domande Frequenti</h2>
            <div class="paw-divider">
                <span class="line"></span><span class="paw">🐾</span><span class="line"></span>
            </div>
        </header>

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

<?php require 'includes/footer.php'; ?>