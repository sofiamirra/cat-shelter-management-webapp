<?php
/*
 * Pagina iniziale del sito
 * Presenta il rifugio, il percorso di adozione e le principali modalità di sostegno
 * Gli ultimi due gatti arrivati vengono recuperati dal database in sola lettura
 */

// I due file sono indispensabili alla pagina: uno configura il database e l'altro genera l'intestazione comune
// require interrompe l'esecuzione se una risorsa necessaria non può essere caricata
require 'includes/db_config.php';
require 'includes/header.php';
?>

<!-- Presentazione principale del rifugio -->
<section class="home-intro">
    <div class="home-intro-text">

        <!-- Unico h1 della pagina: identifica il contenuto principale della Home -->
        <h1>Un Rifugio d’Amore<br>per Gatti Bisognosi</h1>

        <p>
            Il Parco delle Fusa è un rifugio dedicato all'accoglienza e alla riabilitazione dei felini in difficoltà sul territorio. La nostra missione è garantire loro cure mediche, un ambiente sicuro e tanto amore in attesa di un'adozione. Lavoriamo ogni giorno per trasformare un passato di abbandono in un futuro sereno presso una nuova famiglia definitiva.
        </p>

        <!-- Collegamenti alle due principali possibilità di interazione offerte dal sito -->
        <div class="home-intro-buttons">
            <a href="ospiti.php" class="btn-solid-dark">Conosci i Nostri Ospiti</a>
            <a href="volontariato.php" class="btn-outline-dark">Diventa Volontario</a>
        </div>
    </div>

    <!-- figure raggruppa l'immagine principale associata alla presentazione del rifugio -->
    <figure class="home-intro-image">
        <img src="assets/img/gatto_home.png" alt="La struttura del gattile Il Parco delle Fusa con i felini ospiti">
    </figure>
</section>

<!-- La section possiede già un titolo visibile, quindi non è necessario duplicarne il nome con aria-label -->
<section class="adoption-steps-wrapper section-padding">
    <div class="adoption-steps">
        <header class="section-header">
            <h2>Scopri Come Adottare</h2>

            <!-- Il divisore è decorativo: aria-hidden lo esclude dalle tecnologie assistive -->
            <div class="paw-divider" aria-hidden="true">
                <span class="line"></span>
                <img src="assets/img/icona_zampette_bianche.png" alt="" class="paw-divider-icon">
                <span class="line"></span>
            </div>

            <p class="header-subtitle">Pochi semplici passi per accogliere un felino in famiglia.</p>
        </header>

        <div class="adoption-steps-box">
            <div class="adoption-grid">

                <!-- Ogni passaggio è un contenuto autonomo con titolo e descrizione, quindi viene rappresentato con article -->
                <article class="step">
                    <div class="step-icon">

                        <!-- L'icona è decorativa perché il significato del passaggio è già espresso dal relativo h3 -->
                        <img src="assets/img/icona_innamorati.png" alt="">
                    </div>

                    <h3>1. Innamorati</h3>
                    <p>Esplora la nostra galleria ospiti e trova il compagno di vita perfetto per te.</p>
                </article>

                <article class="step">
                    <div class="step-icon">
                        <img src="assets/img/icona_incontralo.png" alt="">
                    </div>

                    <h3>2. Incontralo</h3>
                    <p>Seleziona i mici che ti interessano e prenota un incontro conoscitivo.</p>
                </article>

                <article class="step">
                    <div class="step-icon">
                        <img src="assets/img/icona_casa.png" alt="">
                    </div>

                    <h3>3. Portalo a Casa</h3>
                    <p>Completa le pratiche di adozione e regalagli una famiglia per sempre.</p>
                </article>
            </div>
        </div>
    </div>
</section>

<!-- Sezione dinamica che mostra i due gatti con data di arrivo più recente -->
<section class="home-arrivals-section section-padding">
    <header class="section-header">
        <h2>Gli Ultimi Arrivati</h2>

        <div class="paw-divider" aria-hidden="true">
            <span class="line"></span>
            <img src="assets/img/icona_zampette.png" alt="" class="paw-divider-icon">
            <span class="line"></span>
        </div>

        <p class="header-subtitle">I nuovi ospiti del rifugio sono pronti a trovare una casa.</p>
    </header>

    <div class="gatti-grid-2">
        <?php
        // La Home deve soltanto leggere i gatti, quindi usa l'utente MySQL lecture con privilegi di sola lettura
        $con = get_db_connection('lecture');

        /*
         * La query ordina i gatti dalla data di arrivo più recente alla più vecchia
         * LIMIT 2 mantiene soltanto i primi due risultati richiesti per la Home
         */
        $query = "SELECT nome, sesso, eta, razza, colore_mantello
                  FROM gatti
                  ORDER BY data_arrivo DESC
                  LIMIT 2";

        // La query non contiene dati forniti dall'utente, quindi può essere eseguita direttamente
        $result = mysqli_query($con, $query);

        if ($result) {

            // Ogni fetch restituisce una riga del risultato come array associativo
            while ($gatto = mysqli_fetch_assoc($result)) {
                $nome = $gatto['nome'];
                $sesso = $gatto['sesso'];
                $eta = $gatto['eta'];
                $razza = $gatto['razza'];
                $colore_mantello = $gatto['colore_mantello'];

                /*
                 * I valori testuali vengono codificati prima dell'output HTML
                 * per evitare che eventuale markup o script venga interpretato dal browser e prevenire XSS
                 * ENT_QUOTES gestisce anche le virgolette mentre UTF-8 mantiene la codifica utilizzata dal sito
                 */
                $nome_mostrato = htmlspecialchars((string) $nome, ENT_QUOTES, 'UTF-8');
                $colore_mostrato = htmlspecialchars((string) $colore_mantello, ENT_QUOTES, 'UTF-8');

                // Per eventuali schede prive di razza viene utilizzato un valore alternativo comprensibile
                if (!empty($razza)) {
                    $razza_mostrata = htmlspecialchars((string) $razza, ENT_QUOTES, 'UTF-8');
                } else {
                    $razza_mostrata = 'Meticcio';
                }

                // Il codice memorizzato nel database viene trasformato nel testo completo mostrato all'utente
                if ($sesso === 'M') {
                    $sesso_esteso = 'Maschio';
                } elseif ($sesso === 'F') {
                    $sesso_esteso = 'Femmina';
                } else {

                    // Il valore alternativo mantiene leggibile la scheda anche in presenza di un dato inatteso
                    $sesso_esteso = 'Non specificato';
                }
                ?>

                <!-- Ogni scheda rappresenta un singolo gatto come contenuto autonomo -->
                <article class="card-gatto-premium">

                    <!-- figure raggruppa l'immagine associata alla scheda del gatto -->
                    <figure class="card-img-wrapper">
                        <span class="badge-nuovo">Appena Accolto</span>

                        <!-- Il placeholder è uguale per tutti i gatti e non aggiunge informazioni alla scheda, quindi utilizza alt vuoto -->
                        <img src="assets/img/placeholder_gatto.png" alt="">
                    </figure>

                    <div class="card-body">
                        <h3><?php echo $nome_mostrato; ?></h3>

                        <p class="card-desc">
                            <strong>Sesso:</strong> <?php echo $sesso_esteso; ?><br>
                            <strong>Età:</strong> <?php echo (int) $eta; ?> anni<br>
                            <strong>Razza:</strong> <?php echo $razza_mostrata; ?><br>
                            <strong>Manto:</strong> <?php echo $colore_mostrato; ?>
                        </p>
                    </div>
                </article>

                <?php
            }

            // Se il risultato non contiene righe viene mostrato un messaggio al posto delle card
            if (mysqli_num_rows($result) === 0) {
                echo '<p class="text-center w-100">Al momento non ci sono nuovi ospiti registrati.</p>';
            }

            // Terminata la lettura, il result set non è più necessario
            mysqli_free_result($result);
        } else {

            /*
             * Il dettaglio tecnico viene scritto nel log
             * mentre all'utente viene mostrato un messaggio semplice e comprensibile
             */
            error_log('Errore durante il recupero dei gatti della home: ' . mysqli_error($con));
            echo '<p class="text-center w-100">Non è stato possibile caricare i nuovi ospiti. Riprova più tardi.</p>';
        }

        // Terminata la lettura, la connessione al database viene chiusa
        mysqli_close($con);
        ?>
    </div>

    <!-- Collegamento alla galleria completa gestita nella pagina Ospiti -->
    <div class="text-center mt-2">
        <a href="ospiti.php" class="btn-solid-dark">Scopri tutti i Gatti</a>
    </div>
</section>

<!-- Modalità alternative per sostenere il rifugio senza procedere con un'adozione fisica -->
<section class="ruoli-volontariato home-special-section section-padding">
    <div class="ruoli-container">

        <header class="section-header">
            <h2>Un Aiuto a Distanza</h2>

            <div class="paw-divider" aria-hidden="true">
                <span class="line"></span>
                <img src="assets/img/icona_zampette_bianche.png" alt="" class="paw-divider-icon">
                <span class="line"></span>
            </div>

            <p class="header-subtitle">Scopri come sostenere il rifugio se non puoi adottare fisicamente un felino.</p>
        </header>

        <div class="ruoli-grid">

            <!-- Ogni modalità di sostegno è un contenuto autonomo con titolo, descrizione e collegamento di approfondimento -->
            <article class="ruolo-card">

                <!-- L'icona è decorativa perché il significato della card viene già comunicato dal relativo h3 -->
                <img src="assets/img/icona_cuore.png" alt="" class="icon-png-large">

                <h3>Adozioni del Cuore</h3>
                <p>Sostieni cure e terapie per gatti con disabilità o patologie, aiutandoli a ricevere l'assistenza necessaria.</p>

                <!-- Il collegamento porta direttamente alla sezione corrispondente di sostienici.php -->
                <a href="sostienici.php#adozioni-cuore" class="scopri-link">
                    Scopri di più <span class="freccia" aria-hidden="true">&rarr;</span>
                </a>
            </article>

            <article class="ruolo-card">
                <img src="assets/img/icona_distanza.png" alt="" class="icon-png-large">

                <h3>Adozioni a Distanza</h3>
                <p>Contribuisci a cibo, cure e assistenza di un gatto, seguendone la crescita attraverso aggiornamenti dedicati.</p>

                <a href="sostienici.php#adozioni-distanza" class="scopri-link">
                    Scopri di più <span class="freccia" aria-hidden="true">&rarr;</span>
                </a>
            </article>

            <article class="ruolo-card">
                <img src="assets/img/icona_dono.png" alt="" class="icon-png-large">

                <h3>Donazioni</h3>
                <p>Aiutaci donando cibo, coperte, farmaci o un piccolo contributo. Ogni singolo gesto fa un'enorme differenza per il rifugio.</p>

                <a href="sostienici.php#donazioni" class="scopri-link">
                    Scopri di più <span class="freccia" aria-hidden="true">&rarr;</span>
                </a>
            </article>
        </div>
    </div>
</section>

<!-- Informazioni utili per emergenze, ritrovamenti e richieste di accoglienza -->
<section class="home-emergency-section section-padding">
    <div class="emergency-container">

        <div class="emergency-text">
            <h2 class="emergency-title">Animale in Difficoltà?</h2>
            <p class="emergency-subtitle">Ecco chi contattare per soccorrere un micio bisognoso.</p>

            <div class="emergency-item">

                <!-- Le piccole icone aiutano visivamente a distinguere i casi ma non aggiungono informazioni rispetto ai titoli -->
                <img src="assets/img/icona_ambulanza.png" alt="" class="icon-png-small">

                <div class="emergency-item-content">
                    <h3>Soccorso Gatto Ferito o Malato</h3>

                    <!-- target="_blank" apre il sito istituzionale in una nuova scheda mentre rel="noopener" protegge la finestra originale -->
                    <p>Consulta le <a href="https://www.comune.torino.it/schede-informative/ritrovamenti-cani-gatti-sul-territorio-della-citta-torino" class="emergency-link" target="_blank" rel="noopener">indicazioni ufficiali del Comune.</a></p>
                </div>
            </div>

            <div class="emergency-item">
                <img src="assets/img/icona_scudo.png" alt="" class="icon-png-small">

                <div class="emergency-item-content">
                    <h3>Avvistamento Gatto sul Territorio</h3>
                    <p>Verifica se il gatto <a href="https://www.comune.torino.it/schede-informative/colonie-feline" class="emergency-link" target="_blank" rel="noopener">appartiene a una colonia felina.</a></p>
                </div>
            </div>

            <div class="emergency-item">
                <img src="assets/img/icona_selvatici.png" alt="" class="icon-png-small">

                <div class="emergency-item-content">
                    <h3>Richiesta di Accoglienza al Rifugio</h3>
                    <p>Contattaci prima di arrivare: <a href="tel:+390111234567" class="emergency-link">+39 011 123 4567</a></p>
                </div>
            </div>
        </div>

        <!-- Immagine informativa della sezione, quindi mantiene una descrizione alternativa significativa -->
        <div class="emergency-image">
            <img src="assets/img/gatto_strada.png" alt="Gattino in difficoltà sul ciglio di una strada">
        </div>
    </div>
</section>

<!-- I partner sono informazioni complementari rispetto al contenuto principale della Home, quindi vengono raccolti in aside -->
<aside class="home-partners-strip">
    <h2 class="partners-label">CON IL SUPPORTO DI</h2>

    <div class="partners-logos">

        <!-- I loghi mantengono un alt descrittivo perché il nome dei partner non compare come testo adiacente -->
        <img src="assets/img/sponsor_monge.png" alt="Monge" class="sponsor-logo">
        <img src="assets/img/sponsor_lindocat.png" alt="Lindocat" class="sponsor-logo">
        <img src="assets/img/sponsor_candioli.png" alt="Candioli" class="sponsor-logo">
        <img src="assets/img/sponsor_isolatesori.png" alt="L'Isola dei Tesori" class="sponsor-logo">
    </div>
</aside>

<?php require 'includes/footer.php'; ?>