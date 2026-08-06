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
            Il Parco delle Fusa di Torino è un rifugio dedicato all'accoglienza, cura e riabilitazione dei gatti in difficoltà. La nostra missione è garantire loro un ambiente sicuro e amorevole, curando le ferite fisiche e rassicurando gli animi più spaventati. Lavoriamo ogni giorno per trasformare il loro passato difficile in un futuro sereno, cercando per ognuno di loro una famiglia definitiva. Scopri le nostre attività e unisciti a noi.
        </p>
        <div class="home-hero-buttons">
            <a href="ospiti.php" class="btn-solid-dark">Esplora i Nostri Ospiti</a>
            <a href="volontariato.php" class="btn-outline">Diventa Volontario</a>
        </div>
    </article>
    <figure class="home-hero-image">
        <img src="assets/img/gatto_hero.png" alt="Un dolce gatto in cerca di casa">
    </figure>
</section>

<!-- 2. PROCESSO DI ADOZIONE (Box su sfondo beige) -->
<section class="adoption-steps-wrapper" aria-label="Step di Adozione">
    
    <header class="section-header">
        <h2>Scopri come adottare</h2>
        <div class="paw-divider">
            <span class="line"></span>
            <span class="paw">🐾</span>
            <span class="line"></span>
        </div>
    </header>
    
    <div class="adoption-steps">
        <article class="step">
            <div class="step-icon">🐾</div>
            <h3>1. Innamorati</h3>
            <p>Esplora la nostra galleria ospiti e trova il compagno di vita perfetto per le tue abitudini.</p>
        </article>
        
        <article class="step">
            <div class="step-icon">📝</div>
            <h3>2. Compila il Modulo</h3>
            <p>Seleziona i mici che ti interessano e prenota un incontro conoscitivo in struttura.</p>
        </article>
        
        <article class="step">
            <div class="step-icon">🏡</div>
            <h3>3. Portalo a Casa</h3>
            <p>Concludi l'iter di adozione responsabile e regala una famiglia per sempre al tuo nuovo amico.</p>
        </article>
    </div>
</section>

<!-- 3. GLI ULTIMI ARRIVATI (Requisito Docente: max 2) -->
<section class="home-arrivals-section" aria-label="Ultimi gatti arrivati">
    <header class="section-header">
        <h2>I Nostri Nuovi Ospiti</h2>
        <div class="paw-divider">
            <span class="line"></span>
            <span class="paw">🐾</span>
            <span class="line"></span>
        </div>
        <p class="header-subtitle">Loro sono entrati da poco in rifugio e cercano già una casa.</p>
    </header>
    
    <!-- Utilizziamo la grid a 2 colonne per i 2 record -->
    <div class="gatti-grid-2">
        <?php
        $con = get_db_connection('lecture');
        
        // Estraiamo esattamente gli ultimi 2 gatti come richiesto
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

<!-- 4. ADOZIONI SPECIALI E DONAZIONI (Riciclo classe .ruoli-volontariato per estetica identica) -->
<section class="ruoli-volontariato" aria-label="Supporto al gattile">
    <div class="ruoli-container">
        <!-- Nessun titolo, solo la grid con card bianche -->
        <div class="ruoli-grid">
            <article class="ruolo-card">
                <span class="ruolo-icona">❤️</span>
                <h4>Adozioni del Cuore</h4>
                <p>Un gesto d'amore verso mici con problemi di salute o disabilità. Richiedono cure speciali, ma sanno ricompensare con un affetto unico e profondo.</p>
            </article>
            
            <article class="ruolo-card">
                <span class="ruolo-icona">💌</span>
                <h4>Adozioni a Distanza</h4>
                <p>Solidarietà per gatti con difficoltà di adattamento. Sostieni le cure mediche e il cibo, restando aggiornato sui loro costanti progressi.</p>
            </article>
            
            <article class="ruolo-card">
                <span class="ruolo-icona">🎁</span>
                <h4>Sostienici</h4>
                <p>Aiutaci donando cibo, coperte, farmaci o con un piccolo contributo economico. Ogni gesto, anche il più piccolo, fa un'enorme differenza.</p>
            </article>
        </div>
    </div>
</section>

<!-- 5. EMERGENZE (Layout Affiancato e Spaziature Pulite) -->
<section class="home-emergency-section" aria-label="Cosa fare in caso di emergenza">
    <div class="emergency-container">
        
        <article class="emergency-text">
            <h2>Segnalazione animali in difficoltà</h2>
            
            <div class="emergency-item">
                <div class="emergency-item-icon">🚑</div>
                <div class="emergency-item-content">
                    <h4>Contattare il 112</h4>
                    <p>Saranno le forze dell'ordine a far intervenire gli enti di competenza territoriale per procedere al tempestivo soccorso.</p>
                </div>
            </div>
            
            <div class="emergency-item">
                <div class="emergency-item-icon">🏢</div>
                <div class="emergency-item-content">
                    <h4>Ufficio Tutela Animali di Torino</h4>
                    <p>Per le segnalazioni di animali in difficoltà sul territorio della città:<br>
                    <a href="https://www.comune.torino.it/tutelaanimali/faq" class="emergency-link" target="_blank">www.comune.torino.it/tutelaanimali/faq</a></p>
                </div>
            </div>
            
            <div class="emergency-item">
                <div class="emergency-item-icon">🌲</div>
                <div class="emergency-item-content">
                    <h4>Ufficio Tutela Flora e Fauna</h4>
                    <p>Per le segnalazioni di animali selvatici in difficoltà:<br>
                    <a href="https://www.cittametropolitana.torino.it/cms/fauna-flora-parchi/fauna-e-flora" class="emergency-link" target="_blank">www.cittametropolitana.torino.it/cms/...</a></p>
                </div>
            </div>
        </article>
        
        <figure class="emergency-image">
            <img src="assets/img/gatto_strada.png" alt="Gattino in difficoltà per strada">
        </figure>

    </div>
</section>

<?php require 'includes/footer.php'; ?>