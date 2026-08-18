<?php
/*
 * Piè di pagina comune del sito
 * Chiude il main aperto nell'header e contiene contatti, collegamenti e note conclusive
 */
?>

<!-- Fine del contenuto principale specifico della pagina -->
</main>

<!-- Piè di pagina comune -->
<footer class="site-footer">
    <div class="footer-container">

        <!-- Le colonne del footer sono sezioni di supporto alla pagina e utilizzano h2 coerenti con la gerarchia del documento -->
        <section class="footer-col">
            <h2>Il Parco delle Fusa</h2>

            <p>Doniamo una seconda occasione ai felini in difficoltà. Un ambiente sicuro, cure mediche e tanto amore in attesa di una famiglia.</p>

            <!-- I recapiti del rifugio vengono raggruppati nell'elemento semantico address -->
            <address>
                <ul class="footer-contact">

                    <!-- Le icone sono decorative perché le informazioni sono già presenti in forma testuale -->
                    <li>
                        <img src="assets/img/icona_posizione_footer.png" width="128" height="128" alt="" class="footer-contact-icon">
                        Strada Val Salice 123, 10131 Torino
                    </li>

                    <li>
                        <img src="assets/img/icona_email_footer.png" width="128" height="128" alt="" class="footer-contact-icon">
                        <a href="mailto:info@parcodellefusa.it">info@parcodellefusa.it</a>
                    </li>

                    <li>
                        <img src="assets/img/icona_telefono_footer.png" width="128" height="128" alt="" class="footer-contact-icon">
                        <a href="tel:+390111234567">+39 011 123 4567</a>
                    </li>
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

            <!-- Le icone social vengono mostrate come informazioni visive perché non sono disponibili profili reali da collegare -->
            <div class="footer-social">
                <img src="assets/img/icona_instagram.png" width="128" height="128" alt="Instagram">
                <img src="assets/img/icona_facebook.png" width="128" height="128" alt="Facebook">
            </div>
        </section>

        <!-- Documenti informativi e note legali -->
        <section class="footer-col">
            <h2>Note Legali</h2>

            <!-- target="_blank" apre i PDF in una nuova scheda mentre rel="noopener" impedisce alla nuova pagina di accedere alla finestra originale -->
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
            <p class="academic-credit">Progetto accademico per il corso di <strong>Progettazione di Applicazioni Internet</strong> - Politecnico di Torino.</p>
        </div>
    </div>
</footer>

</body>
</html>