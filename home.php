<?php
/*
 * Pagina iniziale del sito
 * Presenta il rifugio, il percorso di adozione e le principali modalità di sostegno.
 * Gli ultimi due gatti arrivati vengono recuperati dal database in sola lettura.
 */

require 'includes/db_config.php';
require 'includes/header.php';
?>

<!-- Presentazione del rifugio -->
<section class="home-hero" aria-label="Introduzione al Gattile">
    <article class="home-hero-text">
        <h1>Un Rifugio d’Amore<br>per Gatti Bisognosi</h1>
        <p>
            Il Parco delle Fusa è un rifugio dedicato all'accoglienza e alla riabilitazione dei felini in difficoltà sul territorio. La nostra missione è garantire loro cure mediche, un ambiente sicuro e tanto amore in attesa di un'adozione. Lavoriamo ogni giorno per trasformare un passato di abbandono in un futuro sereno presso una nuova famiglia definitiva.
        </p>

        <div class="home-hero-buttons">
            <a href="ospiti.php" class="btn-solid-dark">Conosci i Nostri Ospiti</a>
            <a href="volontariato.php" class="btn-outline-dark">Diventa Volontario</a>
        </div>
    </article>

    <figure class="home-hero-image">
        <img src="assets/img/gatto_home.png" alt="La struttura del gattile Il Parco delle Fusa con i felini ospiti">
    </figure>
</section>

<!-- Processo di adozione -->
<section class="adoption-steps-wrapper section-padding" aria-label="Step di Adozione">
    <div class="adoption-steps">
        <header class="section-header">
            <h2>Scopri Come Adottare</h2>

            <div class="paw-divider" aria-hidden="true">
                <span class="line"></span>
                <img src="assets/img/icona_zampette_bianche.png" alt="" class="paw-divider-icon">
                <span class="line"></span>
            </div>

            <p class="header-subtitle">Pochi semplici passi per accogliere un felino in famiglia.</p>
        </header>

        <!-- I tre passaggi vengono raccolti in un unico contenitore beige -->
        <div class="adoption-steps-box">
            <div class="adoption-grid">
                <article class="step">
                    <div class="step-icon">
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

<!-- Ultimi due gatti registrati -->
<section class="home-arrivals-section section-padding" aria-label="Ultimi gatti arrivati">
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
        // La home utilizza l'utente con privilegi di sola lettura per estrazione sicura
        $con = get_db_connection('lecture');

        // La query seleziona i due gatti con data di arrivo più recente
        $query = "SELECT nome, sesso, eta, razza, colore_mantello
                  FROM gatti
                  ORDER BY data_arrivo DESC
                  LIMIT 2";

        // Preparazione dello statement come misura standard contro vulnerabilità
        $stmt = mysqli_prepare($con, $query);

        if ($stmt) {
            mysqli_stmt_execute($stmt);
        
            mysqli_stmt_bind_result(
                $stmt,
                $nome,
                $sesso,
                $eta,
                $razza,
                $colore_mantello
            );
        
            $gatti_trovati = false;
        
            while (mysqli_stmt_fetch($stmt)) {
                $gatti_trovati = true;
        
                // I dati testuali provenienti dal database vengono codificati prima dell'output
                // per neutralizzare potenziali attacchi XSS
                $nome_mostrato = htmlspecialchars((string) $nome, ENT_QUOTES, 'UTF-8');
                $colore_mostrato = htmlspecialchars((string) $colore_mantello, ENT_QUOTES, 'UTF-8');
        
                if (!empty($razza)) {
                    $razza_mostrata = htmlspecialchars((string) $razza, ENT_QUOTES, 'UTF-8');
                } else {
                    $razza_mostrata = 'Meticcio';
                }
        
                // Il codice relativo al sesso viene trasformato in un testo leggibile
                if ($sesso === 'M') {
                    $sesso_esteso = 'Maschio';
                } elseif ($sesso === 'F') {
                    $sesso_esteso = 'Femmina';
                } else {
                    $sesso_esteso = 'Non specificato';
                }
                ?>
        
                <article class="card-gatto-premium">
                    <figure class="card-img-wrapper">
                        <span class="badge-nuovo">Appena Accolto</span>
                        <img src="assets/img/placeholder_gatto.png" alt="Foto di <?php echo $nome_mostrato; ?>">
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

            if (!$gatti_trovati) {
                echo '<p class="text-center w-100">Al momento non ci sono nuovi ospiti registrati.</p>';
            }

            mysqli_stmt_close($stmt);
        } else {
            // Il dettaglio dell'errore resta disponibile nel log del server
            error_log('Errore nella preparazione della query della home: ' . mysqli_error($con));
            echo '<p class="text-center w-100">Al momento non ci sono nuovi ospiti registrati.</p>';
        }

        mysqli_close($con);
        ?>
    </div>

    <div class="text-center mt-2">
        <a href="ospiti.php" class="btn-solid-dark">Scopri tutti i Gatti</a>
    </div>
</section>

<!-- Adozioni speciali e donazioni -->
<section class="ruoli-volontariato home-special-section section-padding" aria-label="Supporto al gattile">
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
            <article class="ruolo-card">
                <img src="assets/img/icona_cuore.png" alt="" class="icon-png-large">
                <h3>Adozioni del Cuore</h3>
                <p>Sostieni cure e terapie per gatti con disabilità o patologie, aiutandoli a ricevere l'assistenza necessaria.</p>
                <a href="sostienici.php#adozioni-cuore" class="scopri-link">Scopri di più <span class="freccia" aria-hidden="true">&rarr;</span></a>
            </article>

            <article class="ruolo-card">
                <img src="assets/img/icona_distanza.png" alt="" class="icon-png-large">
                <h3>Adozioni a Distanza</h3>
                <p>Contribuisci a cibo, cure e assistenza di un gatto, seguendone la crescita attraverso aggiornamenti dedicati.</p>
                <a href="sostienici.php#adozioni-distanza" class="scopri-link">Scopri di più <span class="freccia" aria-hidden="true">&rarr;</span></a>
            </article>

            <article class="ruolo-card">
                <img src="assets/img/icona_dono.png" alt="" class="icon-png-large">
                <h3>Donazioni</h3>
                <p>Aiutaci donando cibo, coperte, farmaci o un piccolo contributo. Ogni singolo gesto fa un'enorme differenza per il rifugio.</p>
                <a href="sostienici.php#donazioni" class="scopri-link">Scopri di più <span class="freccia" aria-hidden="true">&rarr;</span></a>
            </article>
        </div>

    </div>
</section>

<!-- Informazioni per emergenze e ritrovamenti -->
<section class="home-emergency-section section-padding" aria-label="Cosa fare in caso di emergenza">
    <div class="emergency-container">

        <div class="emergency-text">
            <h3 class="emergency-title">Animale in Difficoltà?</h3>
            <p class="emergency-subtitle">Ecco a chi rivolgerti per soccorrere un esemplare bisognoso.</p>

            <div class="emergency-item">
                <img src="assets/img/icona_ambulanza.png" alt="" class="icon-png-small">
                <div class="emergency-item-content">
                    <h4>Soccorso Gatto Ferito o Malato</h4>
                    <p>Consulta le <a href="https://www.comune.torino.it/schede-informative/ritrovamenti-cani-gatti-sul-territorio-della-citta-torino" class="emergency-link" target="_blank" rel="noopener">indicazioni ufficiali del Comune.</a></p>
                </div>
            </div>

            <div class="emergency-item">
                <img src="assets/img/icona_scudo.png" alt="" class="icon-png-small">
                <div class="emergency-item-content">
                    <h4>Avvistamento Gatto sul Territorio</h4>
                    <p>Verifica sempre la sua <a href="https://www.comune.torino.it/schede-informative/colonie-feline" class="emergency-link" target="_blank" rel="noopener">appartenenza a una colonia.</a></p>
                </div>
            </div>

            <div class="emergency-item">
                <img src="assets/img/icona_selvatici.png" alt="" class="icon-png-small">
                <div class="emergency-item-content">
                    <h4>Richiesta di Accoglienza al Rifugio</h4>
                    <p>Contattaci prima di arrivare: <a href="tel:+390111234567" class="emergency-link">+39 011 123 4567</a></p>
                </div>
            </div>
        </div>

        <div class="emergency-image">
            <img src="assets/img/gatto_strada.png" alt="Gattino in difficoltà sul ciglio di una strada">
        </div>

    </div>
</section>

<!-- Partner che sostengono il rifugio -->
<aside class="home-partners-strip" aria-label="Partner del rifugio">
    <p class="partners-label">CON IL SUPPORTO DI</p>

    <div class="partners-logos">
        <img src="assets/img/sponsor_monge.png" alt="Logo del partner 1" class="sponsor-logo">
        <img src="assets/img/sponsor_lindocat.png" alt="Logo del partner 2" class="sponsor-logo">
        <img src="assets/img/sponsor_candioli.png" alt="Logo del partner 3" class="sponsor-logo">
        <img src="assets/img/sponsor_isolatesori.png" alt="Logo del partner 4" class="sponsor-logo">
    </div>
</aside>

<?php require 'includes/footer.php'; ?>