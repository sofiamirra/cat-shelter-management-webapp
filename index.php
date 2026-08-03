<?php
// index.php
require 'includes/header.php';
require 'includes/db_config.php';
?>

<section class="hero">
    <h2>Benvenuti al Gattile</h2>
    <p>Ogni anno, centinaia di gatti vengono abbandonati o nascono in strada, necessitando di cure e di una famiglia. Allo stesso tempo, molte persone desiderano accogliere un felino o dedicare il proprio tempo come volontari. Questo sito nasce per facilitare le adozioni e organizzare il supporto attivo alla struttura ospitante.</p>
</section>

<section class="test-db" style="margin-top: 2rem; background: #e8f5e9; padding: 1rem; border-left: 5px solid #4caf50;">
    <h3>Test di Connessione Backend</h3>
    <?php
    // Testiamo la connessione con l'utente base (lecture)
    $con = get_db_connection('lecture');
    if ($con) {
        echo "<p style='color: green; font-weight: bold;'>✅ Sistema operativo: Connessione al database 'gattile_db' riuscita!</p>";
        mysqli_close($con);
    }
    ?>
</section>

<?php
require 'includes/footer.php';
?>