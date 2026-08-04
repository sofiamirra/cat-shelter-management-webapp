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

<!-- Sezione di presentazione della struttura -->
<section class="presentazione">
    <h2>Benvenuti al Gattile PoliTo</h2>
    <p>Ogni anno, centinaia di gatti vengono abbandonati o nascono in strada, necessitando di cure e di una famiglia. Allo stesso tempo, molte persone desiderano accogliere un felino o dedicare il proprio tempo come volontari. Questo sito nasce per facilitare le adozioni e organizzare il supporto attivo alla struttura ospitante.</p>
</section>

<!-- Vetrina dei gatti in cerca di adozione -->
<section class="nuovi-arrivi">
    <h2 style="color: var(--colore-primario); margin-bottom: 1.5rem; text-align: center;">I Nostri Nuovi Arrivi</h2>
    
    <div class="gatti-grid">
        <?php
        // Connessione al DB con privilegi di sola lettura (lecture)
        $con = get_db_connection('lecture');
        
        // Seleziono i gatti ordinandoli per data di arrivo (dal più recente)
        $query = "SELECT nome, sesso, eta, razza, colore_mantello, data_arrivo FROM gatti ORDER BY data_arrivo DESC LIMIT 6";
        
        // Utilizzo il prepared statement per mantenere lo standard di sicurezza richiesto
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_execute($stmt);
            
            // Associazione delle colonne estratte a variabili PHP
            mysqli_stmt_bind_result($stmt, $nome_db, $sesso_db, $eta_db, $razza_db, $colore_db, $data_db);
            
            // Variabile di controllo per verificare se ci sono gatti
            $gatti_trovati = false;

            // Ciclo iterativo per scorrere i risultati e generare le card HTML
            while (mysqli_stmt_fetch($stmt)) {
                $gatti_trovati = true;
                
                // Formattazione della data in formato italiano (GG/MM/AAAA)
                $data_formattata = date("d/m/Y", strtotime($data_db));
                
                // Gestione dei campi opzionali (se vuoti mostro "Non specificata")
                $razza_mostrata = !empty($razza_db) ? htmlspecialchars($razza_db) : "Meticcio";
                $eta_mostrata = !empty($eta_db) ? $eta_db . " anni" : "Cucciolo/Sconosciuta";

                // Generazione della Card
                echo "
                <div class='gatto-card'>
                    <!-- Il placeholder viene assegnato staticamente lato frontend -->
                    <img src='assets/img/placeholder_gatto.png' alt='Foto di $nome_db' class='gatto-img'>
                    <div class='gatto-info'>
                        <h3>" . htmlspecialchars($nome_db) . "</h3>
                        <p><strong>Sesso:</strong> " . htmlspecialchars($sesso_db) . "</p>
                        <p><strong>Età:</strong> $eta_mostrata</p>
                        <p><strong>Razza:</strong> $razza_mostrata</p>
                        <p><strong>Manto:</strong> " . htmlspecialchars($colore_db) . "</p>
                        <p class='data-arrivo'>In struttura dal: $data_formattata</p>
                    </div>
                </div>";
            }

            if (!$gatti_trovati) {
                echo "<p style='text-align: center; grid-column: 1 / -1;'>Al momento non ci sono gatti registrati nel sistema.</p>";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "<p style='color: red; text-align: center; grid-column: 1 / -1;'>Errore nel caricamento del database.</p>";
        }
        
        // Rilascio delle risorse
        mysqli_close($con);
        ?>
    </div>
</section>

<?php
// Inclusione del footer
require 'includes/footer.php';
?>