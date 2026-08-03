<?php
/**
 * Home page del sito.
 * Include l'intestazione, la configurazione del DB e mostra la presentazione
 * iniziale insieme a un test di connessione.
 */

// Inclusione dei componenti strutturali e di configurazione
require 'includes/header.php';
require 'includes/db_config.php';
?>

<!-- Sezione di presentazione iniziale -->
<section class="hero">
    <h2>Presentazione della Struttura</h2>
    <p>Ogni anno, centinaia di gatti vengono abbandonati o nascono in strada, necessitando di cure e di una famiglia. Allo stesso tempo, molte persone desiderano accogliere un felino o dedicare il proprio tempo come volontari. Questo sito nasce per facilitare le adozioni e organizzare il supporto attivo alla struttura ospitante.</p>
</section>

<!-- Box di verifica della connessione al database -->
<section class="test-db" style="margin-top: 2rem; background: #e8f5e9; padding: 1rem; border-left: 5px solid #4caf50;">
    <h3>Verifica Stato del Sistema</h3>
    <?php
    // Apertura di una connessione di sola lettura per testare il database
    $con = get_db_connection('lecture');
    if ($con) {
        echo "<p style='color: #2e7d32; font-weight: bold;'>Connessione al database riuscita.</p>";
        // Chiusura esplicita della connessione per liberare risorse
        mysqli_close($con);
    }
    ?>
</section>

<?php
// Inclusione del footer
require 'includes/footer.php';
?>