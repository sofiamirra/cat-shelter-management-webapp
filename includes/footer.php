<?php
/*
 * Piè di pagina comune del sito
 * Chiude il tag principale del documento e organizza le informazioni di supporto. 
 */
?>
</main>

<!-- Piè di pagina comune -->
<footer class="site-footer">
    <div class="footer-container">

        <!-- Informazioni e recapiti del gattile -->
        <section class="footer-col">
            <h2>Il Parco delle Fusa</h2>

            <p>Doniamo una seconda occasione ai felini in difficoltà. Un ambiente sicuro, cure mediche e tanto amore in attesa di una famiglia.</p>
            
            <!-- I recapiti vengono raggruppati semanticamente come informazioni di contatto -->
            <address>
                <ul class="footer-contact">
                    <li><img src="assets/img/icona_posizione_footer.png" alt="" class="footer-contact-icon"> Strada Val Salice 123, 10131 Torino (TO)</li>
                    <li><img src="assets/img/icona_email_footer.png" alt="" class="footer-contact-icon"> <a href="mailto:info@parcodellefusa.it">info@parcodellefusa.it</a></li>
                    <li><img src="assets/img/icona_telefono_footer.png" alt="" class="footer-contact-icon"> <a href="tel:+390111234567">+39 011 123 4567</a></li>
                </ul>
            </address>
        </section>

         <!-- Collegamenti alle principali sezioni del sito -->
        <section class="footer-col">
            <h2>Esplora</h2>

            <ul class="footer-links">
                <li><a href="home.php">Home</a></li>
                <li><a href="ospiti.php">I Nostri Ospiti</a></li>
                <li><a href="volontariato.php">Diventa Volontario</a></li>
                <li><a href="sostienici.php">Sostienici</a></li>
            </ul>

            <!-- Icone come contenuto informativo per indisponibilità URL reali -->
            <div class="footer-social">
                <img src="assets/img/icona_instagram.png" alt="Instagram">
                <img src="assets/img/icona_facebook.png" alt="Facebook">
            </div>
        </section>

        <!-- Documenti informativi e note legali -->
        <section class="footer-col">
            <h2>Note Legali</h2>

            <!-- I PDF vengono aperti in una nuova scheda senza interrompere la navigazione del sito -->
            <ul class="footer-note-list">
                <li><a href="assets/docs/privacy_policy.pdf" target="_blank" rel="noopener">Privacy Policy</a></li>
                <li><a href="assets/docs/cookie_policy.pdf" target="_blank" rel="noopener">Cookie Policy</a></li>
                <li><a href="assets/docs/termini_condizioni.pdf" target="_blank" rel="noopener">Termini e Condizioni</a></li>
                <li><a href="assets/docs/statuto_associativo.pdf" target="_blank" rel="noopener">Statuto Associazione</a></li>
            </ul>
        </section>

    </div>

    <!-- Informazioni conclusive sul progetto -->
    <div class="footer-bottom">
        <div class="footer-bottom-content">
            <p>&copy; 2026 Il Parco delle Fusa. Tutti i diritti riservati.</p>
            <p class="academic-credit">Progetto accademico per il corso di <strong>Progettazione di Applicazioni Internet 2026</strong> - Politecnico di Torino.</p>
        </div>
    </div>
</footer>

</body>
</html>