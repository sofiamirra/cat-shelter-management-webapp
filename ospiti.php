<?php
// Avvio della sessione per verificare lo stato di autenticazione corrente
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require 'includes/header.php'; 
?>

<!-- Messaggi di Feedback Prenotazione -->
<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
    <div class="alert-wrapper">
        <div class="auth-alert-success">
            <strong>Prenotazione confermata! 🎉</strong><br>
            Ti aspettiamo in struttura. I dettagli sono stati salvati correttamente.
        </div>
    </div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
    <div class="alert-wrapper">
        <div class="auth-alert-danger">
            <strong>Si è verificato un errore!</strong> Riprova più tardi o contatta la struttura.
        </div>
    </div>
<?php endif; ?>

<!-- Sezione Intestazione della Pagina -->
<div class="page-wrapper">
    
    <div class="section-header">
        <h2>I Nostri Ospiti</h2>
        <div class="paw-divider">
            <div class="line"></div>
            <i class="paw">🐾</i>
            <div class="line"></div>
        </div>
        <p class="header-subtitle">
            Scopri i felini in cerca di casa. Usa i filtri per trovare il tuo nuovo compagno.<br>
            <strong>Accedi o registrati per poter selezionare un gatto e prenotare una visita!</strong>
        </p>
    </div>

    <!-- Qui c'è il div dove React inietta le card -->
    <div id="react-root"></div>

<!-- 
    LA TELA BIANCA DI REACT 
    React cercherà questo div con id="react-root" e ci inietterà dentro tutta l'interfaccia 
-->
<div id="react-root"></div>

<!-- ==========================================
     IMPORTAZIONE LIBRERIE REACT (Tramite CDN)
     ========================================== -->
<!-- React Core: La libreria principale -->
<script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
<!-- React DOM: Permette a React di modificare l'HTML -->
<script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
<!-- Babel: Traduce la sintassi JSX di React in JavaScript normale che il browser può leggere -->
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<!-- Passiamo a JavaScript lo stato della sessione PHP -->
<script>
    const IS_LOGGED_IN = <?php echo isset($_SESSION['username']) ? 'true' : 'false'; ?>;
</script>

<!-- Importazione del nostro componente React -->
<!-- Nota: type="text/babel" è fondamentale affinché Babel traduca il file -->
<script type="text/babel" src="assets/js/GattiApp.js"></script>

<!-- 
    LA SEZIONE VANILLA JS (Prenotazione Visita) 
    Verrà implementata successivamente come richiesto dalle specifiche
-->
<!-- ==========================================
     FORM DI PRENOTAZIONE (SOLO PER AUTENTICATI)
     ========================================== -->
     <?php if(isset($_SESSION['username'])): ?>
    <div class="prenotazione-wrapper" id="sezione-prenotazione">
        <div class="prenotazione-header">
            <h3>Prenota una visita in struttura</h3>
            <p>Seleziona uno o più gatti dalle card in alto per compilare la tua richiesta.</p>
        </div>

        <form action="processa_prenotazione.php" method="POST" id="form-prenotazione-visita">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="data_visita">Data della visita</label>
                    <input type="date" id="data_visita" name="data_visita" required>
                    <span class="errore-js" id="err-data"></span>
                </div>
                <div class="form-group">
                    <label for="ora_visita">Ora della visita</label>
                    <input type="time" id="ora_visita" name="ora_visita" required>
                    <span class="errore-js" id="err-ora"></span>
                </div>
            </div>

            <!-- Interfaccia visiva aggiornata da Vanilla JS -->
            <label>Gatti selezionati per l'incontro:</label>
            <ul class="lista-selezionati" id="ui-lista-gatti">
                <li class="nessun-gatto-selezionato">Nessun gatto selezionato al momento. Clicca sulle card in alto.</li>
            </ul>

            <!-- Campo nascosto che verrà inviato al server col form -->
            <input type="hidden" name="id_gatti_selezionati" id="input-hidden-gatti" value="">

            <button type="submit" class="btn-solid-dark w-100" id="btn-prenota" disabled>Conferma Prenotazione</button>
        </form>
    </div>

    <!-- SCRIPT VANILLA JS: In ascolto del CustomEvent di React -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ulElement = document.getElementById('ui-lista-gatti');
            const hiddenInput = document.getElementById('input-hidden-gatti');
            const btnPrenota = document.getElementById('btn-prenota');
            
            // Impostiamo la data minima a domani per impedire prenotazioni nel passato
            const dateInput = document.getElementById('data_visita');
            const domani = new Date();
            domani.setDate(domani.getDate() + 1);
            dateInput.min = domani.toISOString().split('T')[0];

            // Iscrizione all'evento DOM globale generato da React
            document.addEventListener('aggiornamentoGattiScelti', function(event) {
                // Estraiamo il pacchetto dati (l'array dei gatti scelti in React)
                const gattiSelezionati = event.detail; 
                
                // Puliamo la lista visiva (DOM Manipulation Vanilla)
                ulElement.innerHTML = '';

                if (gattiSelezionati.length === 0) {
                    // Stato vuoto
                    ulElement.innerHTML = '<li class="nessun-gatto-selezionato">Nessun gatto selezionato al momento. Clicca sulle card in alto.</li>';
                    hiddenInput.value = "";
                    btnPrenota.disabled = true; // Blocca il form se non ci sono gatti
                    btnPrenota.style.opacity = "0.5";
                } else {
                    // Popoliamo la lista con i nomi scelti
                    const arrayID = [];
                    gattiSelezionati.forEach(gatto => {
                        const li = document.createElement('li');
                        li.textContent = '🐾 ' + gatto.nome + ' (Razza: ' + gatto.razza + ')';
                        ulElement.appendChild(li);
                        
                        arrayID.push(gatto.id);
                    });
                    
                    // Salviamo gli ID nel campo hidden per inviarli al backend PHP
                    hiddenInput.value = JSON.stringify(arrayID);
                    btnPrenota.disabled = false; // Sblocca il pulsante
                    btnPrenota.style.opacity = "1";
                }
            });
        });
    </script>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>