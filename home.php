<?php
/**
 * Pagina iniziale (Home Page).
 * Presenta la struttura del gattile e mostra dinamicamente gli ultimi 
 * felini registrati nel database, prelevandoli in sola lettura.
 */

// Inclusione dei moduli di struttura e configurazione
require 'includes/header.php';
require 'includes/db_config.php';
?>

<!-- Sezione Hero: Layout diviso a metà -->
<section class="hero" id="chi-siamo">
    <div class="hero-container">
        
        <!-- Colonna di Sinistra: Testo -->
        <div class="hero-text">
            <h1>Un Rifugio d’Amore<br>per Gatti Bisognosi</h1>
            <p>Il Parco delle Fusa di Torino accoglie e si prende cura dei gatti in difficoltà, offrendo loro protezione, assistenza e nuove opportunità di adozione.</p>
            <div class="hero-buttons">
                <a href="#ospiti" class="btn-solid-dark">Esplora i Nostri Ospiti</a>
            </div>
        </div>

        <!-- Colonna di Destra: Immagine sfumata -->
        <div class="hero-image">
            <!-- Sostituisci il src con il percorso della tua foto -->
            <img src="assets/img/gatto_hero.jpg" alt="Un dolce gatto in cerca di casa">
        </div>

    </div>
</section>

<!-- Sezione Step Adozione (Il box bianco arrotondato) -->
<section class="adoption-steps-wrapper">
    <div class="adoption-steps">
        
        <div class="step">
            <div class="step-icon">🐾</div>
            <h3>1. Innamorati</h3>
            <p>Scopri i gatti ospitati nella nostra struttura.</p>
        </div>
        
        <div class="step">
            <div class="step-icon">📝</div>
            <h3>2. Compila il Modulo</h3>
            <p>Inizia il percorso di adozione responsabile.</p>
        </div>
        
        <div class="step">
            <div class="step-icon">🏡</div>
            <h3>3. Portalo a Casa</h3>
            <p>Regalagli una famiglia per sempre.</p>
        </div>

    </div>
</section>

<!-- Sezione Ultimi Accolti (Estrazione limitata a 2 record come da specifiche) -->
<section class="nuovi-arrivi" id="ospiti">
    
    <!-- Intestazione con Separatore "Zampetta" -->
    <div class="section-header">
        <h2>I Nostri Nuovi Ospiti</h2>
        <div class="paw-divider">
            <span class="line"></span>
            <span class="paw">🐾</span>
            <span class="line"></span>
        </div>
    </div>
    
    <div class="gatti-grid-2">
        <?php
        // Connessione al DB in sola lettura (lecture)
        $con = get_db_connection('lecture');
        
        // QUERY BLINDATA: Estraiamo ESATTAMENTE gli ultimi 2 inseriti per data
        $query = "SELECT nome, sesso, eta, razza, colore_mantello, data_arrivo, descrizione FROM gatti ORDER BY data_arrivo DESC LIMIT 2";
        
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $nome, $sesso, $eta, $razza, $colore, $data, $desc);
            
            $gatti_trovati = false;

            while (mysqli_stmt_fetch($stmt)) {
                $gatti_trovati = true;
                
                // Formattazione per la visualizzazione
                $razza_mostrata = !empty($razza) ? htmlspecialchars($razza) : "Meticcio";
                
                // Generazione della Card
                echo "
                <div class='card-gatto-premium'>
                    <div class='card-img-wrapper'>
                        <!-- Badge aggiornato con un testo più empatico -->
                        <span class='badge-nuovo'>Appena Accolto</span>
                        <img src='assets/img/placeholder_gatto.png' alt='Foto di $nome'>
                        
                        <!-- Cerchietto con icona sovrapposto -->
                        <div class='icon-circle'>
                            " . ($sesso == 'M' ? '♂️' : '♀️') . "
                        </div>
                    </div>
                    
                    <div class='card-body'>
                        <h3>" . htmlspecialchars($nome) . "</h3>
                        <p class='card-desc'>
                            Razza: $razza_mostrata <br>
                            Manto: " . htmlspecialchars($colore) . "<br>
                            Età: $eta anni
                        </p>
                        <a href='#' class='card-link'>SCOPRI DI PIÙ &rarr;</a>
                    </div>
                </div>";
            }

            if (!$gatti_trovati) {
                echo "<p class='no-cats'>Al momento non ci sono nuovi ospiti registrati.</p>";
            }

            mysqli_stmt_close($stmt);
        }
        mysqli_close($con);
        ?>
    </div>
</section>

<?php
// Inclusione del footer
require 'includes/footer.php';
?>