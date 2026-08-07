<?php
/**
 * Pagina iniziale (Home Page).
 * Hub direzionale strutturato in tag semantici. Presenta dinamicamente gli 
 * ultimi felini registrati nel database, prelevandoli in sola lettura.
 */

session_start();
require 'includes/header.php';
require 'includes/db_config.php';
?>

<!-- 1. HERO MISSION -->
<section class="home-hero" aria-label="Introduzione al Gattile">
    <article class="home-hero-text">
        <h1>Un Rifugio d’Amore<br>per Gatti Bisognosi</h1>
        <p>
            Il Parco delle Fusa è un rifugio dedicato all'accoglienza e alla riabilitazione dei felini in difficoltà sul territorio. La nostra missione è garantire loro cure mediche, un ambiente sicuro e tanto amore in attesa di un'adozione. Lavoriamo ogni giorno per trasformare un passato di abbandono in un futuro sereno presso una nuova famiglia definitiva.
        </p>
        <div class="home-hero-buttons">
            <a href="ospiti.php" class="btn-solid-dark">Esplora i Nostri Ospiti</a>
            <a href="volontariato.php" class="btn-outline-dark">Diventa Volontario</a>
        </div>
    </article>
    <figure class="home-hero-image">
        <!-- Ricorda di salvare le immagini e sovrascrivere qui se necessario -->
        <img src="assets/img/gatto_hero.png" alt="Un dolce gatto in cerca di casa nel nostro rifugio">
    </figure>
</section>

<!-- 2. PROCESSO DI ADOZIONE -->
<section class="adoption-steps-wrapper" aria-label="Step di Adozione">
    <div class="adoption-steps">
        
        <header class="section-header w-100">
            <h2>Scopri Come Adottare</h2>
            <div class="paw-divider">
                <span class="line"></span>
                <span class="paw">🐾</span>
                <span class="line"></span>
            </div>
        </header>
        
        <div class="adoption-grid w-100">
            <article class="step">
                <!-- Il cerchietto beige è ricreato via CSS, salva l'immagine in PNG Trasparente! -->
                <div class="step-icon">
                    <img src="assets/img/icona_innamorati.png" alt="Icona Zampette">
                </div>
                <h3>1. Innamorati</h3>
                <p>Esplora la nostra galleria ospiti e trova il compagno di vita perfetto per te.</p>
            </article>
            
            <article class="step">
                <div class="step-icon">
                    <img src="assets/img/icona_incontralo.png" alt="Icona Incontro">
                </div>
                <h3>2. Incontralo</h3>
                <p>Seleziona i mici che ti interessano e prenota un incontro conoscitivo.</p>
            </article>
            
            <article class="step">
                <div class="step-icon">
                    <img src="assets/img/icona_casa.png" alt="Icona Casa">
                </div>
                <h3>3. Portalo a Casa</h3>
                <p>Completa l'iter di adozione responsabile e regalagli una famiglia per sempre.</p>
            </article>
        </div>

    </div>
</section>

<!-- 3. GLI ULTIMI ARRIVATI -->
<section class="home-arrivals-section section-padding" aria-label="Ultimi gatti arrivati">
    <header class="section-header">
        <h2>Gli Ultimi Arrivati</h2>
        <div class="paw-divider">
            <span class="line"></span>
            <span class="paw">🐾</span>
            <span class="line"></span>
        </div>
        <p class="header-subtitle">I nuovi opsiti del rifugio sono pronti a trovare una casa.</p>
    </header>
    
    <div class="gatti-grid-2">
        <?php
        $con = get_db_connection('lecture');
        $query = "SELECT nome, sesso, eta, razza, colore_mantello FROM gatti ORDER BY data_arrivo DESC LIMIT 2";
        
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $nome, $sesso, $eta, $razza, $colore);
            $gatti_trovati = false;

            while (mysqli_stmt_fetch($stmt)) {
                $gatti_trovati = true;
                $razza_mostrata = !empty($razza) ? htmlspecialchars($razza) : "Meticcio";
                $sesso_esteso = ($sesso == 'M') ? 'Maschio' : 'Femmina';
                
                echo "
                <article class='card-gatto-premium'>
                    <figure class='card-img-wrapper'>
                        <span class='badge-nuovo'>Appena Accolto</span>
                        <img src='assets/img/placeholder_gatto.png' alt='Foto di $nome'>
                    </figure>
                    <div class='card-body'>
                        <h3>" . htmlspecialchars($nome) . "</h3>
                        <p class='card-desc'>
                            <strong>Sesso:</strong> $sesso_esteso <br>
                            <strong>Età:</strong> $eta anni <br>
                            <strong>Razza:</strong> $razza_mostrata <br>
                            <strong>Manto:</strong> " . htmlspecialchars($colore) . "
                        </p>
                    </div>
                </article>";
            }

            if (!$gatti_trovati) {
                echo "<p class='text-center w-100'>Al momento non ci sono nuovi ospiti registrati.</p>";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_close($con);
        ?>
    </div>

    <div class="text-center mt-2">
        <a href="ospiti.php" class="btn-solid-dark">Scopri tutti i Gatti</a>
    </div>
</section>

<!-- 4. ADOZIONI SPECIALI E DONAZIONI -->
<section class="ruoli-volontariato home-special-section section-padding" aria-label="Supporto al gattile">
    <div class="ruoli-container">
        
        <header class="section-header">
            <h2>Un Aiuto a Distanza</h2>
            <div class="paw-divider">
                <span class="line"></span>
                <span class="paw">🐾</span>
                <span class="line"></span>
            </div>
        </header>

        <div class="ruoli-grid">
            <article class="ruolo-card">
                <img src="assets/img/icona_cuore.png" alt="Icona Cuore" class="icon-png-large">
                <h3>Adozioni del Cuore</h3>
                <p>Sostieni cure e terapie per gatti con disabilità o patologie, aiutandoli a ricevere l'assistenza necessaria.</p>
            </article>
            
            <article class="ruolo-card">
                <img src="assets/img/icona_distanza.png" alt="Icona Lettera" class="icon-png-large">
                <h3>Adozioni a Distanza</h3>
                <p>Contribuisci a cibo, cure e assistenza di un gatto, seguendone la crescita attraverso aggiornamenti dedicati.</p>
            </article>
            
            <article class="ruolo-card">
                <img src="assets/img/icona_dono.png" alt="Icona Regalo" class="icon-png-large">
                <h3>Sostienici</h3>
                <p>Aiutaci donando cibo, coperte, farmaci o un piccolo contributo. Ogni singolo gesto fa un'enorme differenza per il rifugio.</p>
            </article>
        </div>

        <!-- Link testuale unico centrato sotto la griglia -->
        <div class="text-center mt-2">
            <a href="contatti.php" class="scopri-link">Sostieni il gattile <span class="freccia">&rarr;</span></a>
        </div>
    </div>
</section>

<!-- 5. EMERGENZE -->
<section class="home-emergency-section section-padding" aria-label="Cosa fare in caso di emergenza">
    <div class="emergency-container">
        
        <article class="emergency-text">
            <h2>Animale in Difficoltà?</h2>
            
            <div class="emergency-item">
                <img src="assets/img/icona_ambulanza.png" alt="Icona 112" class="icon-png-small">
                <div class="emergency-item-content">
                    <h4>Contattare il 112</h4>
                    <p>Attiva le forze dell'ordine per il tempestivo soccorso.</p>
                </div>
            </div>
            
            <div class="emergency-item">
                <img src="assets/img/icona_comune.png" alt="Icona Comune" class="icon-png-small">
                <div class="emergency-item-content">
                    <h4>Ufficio Tutela Animali di Torino</h4>
                    <a href="https://www.comune.torino.it/tutelaanimali/faq" class="emergency-link" target="_blank" aria-label="Vai alle direttive ufficiali del Comune di Torino">Consulta le direttive ufficiali per la città di Torino.</a>
                </div>
            </div>
            
            <div class="emergency-item">
                <img src="assets/img/icona_selvatici.png" alt="Icona Albero" class="icon-png-small">
                <div class="emergency-item-content">
                    <h4>Ufficio Tutela Flora e Fauna</h4>
                    <a href="https://www.cittametropolitana.torino.it/cms/fauna-flora-parchi/fauna-e-flora" class="emergency-link" target="_blank" aria-label="Vai alle direttive per la flora e la fauna">Per le segnalazioni di animali selvatici in difficoltà.</a>
                </div>
            </div>
        </article>
        
        <figure class="emergency-image">
            <img src="assets/img/gatto_strada.png" alt="Gattino in difficoltà sul ciglio di una strada">
        </figure>

    </div>
</section>

<?php require 'includes/footer.php'; ?>