<?php
// Avvio della sessione per verificare lo stato di autenticazione corrente
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require 'includes/header.php'; 
?>

<div class="page-wrapper">
    
    <!-- ==========================================
         INTESTAZIONE PAGINA E TESTI
         ========================================== -->
    <div class="section-header">
        <h2>I Nostri Ospiti</h2>
        <div class="paw-divider">
            <div class="line"></div>
            <i class="paw">🐾</i>
            <div class="line"></div>
        </div>
        <p class="header-subtitle">
            Scopri i felini in cerca di casa. <strong>Usa i filtri per esplorare la galleria</strong> e conoscere le loro storie, particolarità e attitudini.
        </p>
    </div>

    <!-- ==========================================
         MESSAGGI DI FEEDBACK (Prenotazione)
         ========================================== -->
    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert-wrapper mb-4" id="banner-feedback">
            <div class="auth-alert-success alert-dismissible">
                <strong>Prenotazione confermata! 🎉</strong><br>
                Ti aspettiamo in struttura. I dettagli sono stati salvati correttamente.
                <!-- Tasto chiudi con JS Vanilla pulito -->
                <button type="button" class="btn-close-alert" onclick="document.getElementById('banner-feedback').classList.add('d-none')" aria-label="Chiudi">&times;</button>
            </div>
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
        <div class="alert-wrapper mb-4" id="banner-feedback">
            <div class="auth-alert-danger alert-dismissible">
                <strong>Si è verificato un errore!</strong> Riprova più tardi o contatta la struttura.
                <button type="button" class="btn-close-alert" onclick="document.getElementById('banner-feedback').classList.add('d-none')" aria-label="Chiudi">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- ==========================================
         FORM DI PRENOTAZIONE (SOLO PER AUTENTICATI)
         ========================================== -->
    <?php if(isset($_SESSION['username'])): ?>
        <div class="prenotazione-wrapper mb-4" id="sezione-prenotazione">
            <div class="prenotazione-header text-center mb-2">
                <h3>Prenota una visita in struttura</h3>
                <p style="color: #666;">Seleziona uno o più gatti dalle <strong>card in basso</strong> per compilare la tua richiesta.</p>
            </div>

            <form action="processa_prenotazione.php" method="POST" id="form-prenotazione-visita">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="data_visita" class="form-label-title">Data della visita</label>
                        <input type="date" id="data_visita" name="data_visita" class="input-data-large" required>
                        <span class="errore-js" id="err-data"></span>
                    </div>
                    <div class="form-group">
                        <label for="ora_visita" class="form-label-title">Ora della visita</label>
                        <input type="time" id="ora_visita" name="ora_visita" class="input-data-large" required>
                        <span class="errore-js" id="err-ora"></span>
                    </div>
                </div>

                <div class="form-group mt-2">
                    <label class="form-label-title">Gatti selezionati per l'incontro:</label>
                    <ul class="lista-selezionati" id="ui-lista-gatti">
                        <li class="nessun-gatto-selezionato">Nessun gatto selezionato al momento. Clicca sulle card in basso.</li>
                    </ul>
                </div>

                <!-- Campo nascosto che verrà inviato al server col form -->
                <input type="hidden" name="id_gatti_selezionati" id="input-hidden-gatti" value="">

                <!-- Il bottone parte disabilitato. Si abiliterà via JS solo se ci sono gatti -->
                <button type="submit" class="btn-solid-dark w-100 mt-1" id="btn-prenota" disabled>Conferma Prenotazione</button>
            </form>
        </div>

    <!-- ==========================================
         AVVISO PER I NON AUTENTICATI (Con Redirect)
         ========================================== -->
    <?php else: ?>
        <div class="alert-wrapper mb-4 text-center">
            <div class="auth-alert-success">
                <a href="login.php?redirect=ospiti.php" class="auth-alert-link">Accedi o Registrati</a> per selezionare i mici e prenotare la tua visita.
            </div>
        </div>
    <?php endif; ?>

    <!-- ==========================================
         LA TELA BIANCA DI REACT 
         ========================================== -->
    <div id="react-root"></div>

</div>

<!-- ==========================================
     IMPORTAZIONE LIBRERIE E SCRIPT
     ========================================== -->
<script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
<script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<!-- Passiamo a JavaScript lo stato della sessione PHP -->
<script>
    const IS_LOGGED_IN = <?php echo isset($_SESSION['username']) ? 'true' : 'false'; ?>;
</script>

<!-- Importazione del componente React -->
<script type="text/babel" src="assets/js/GattiApp.js"></script>

<!-- SCRIPT VANILLA JS: In ascolto del CustomEvent di React -->
<?php if(isset($_SESSION['username'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ulElement = document.getElementById('ui-lista-gatti');
        const hiddenInput = document.getElementById('input-hidden-gatti');
        const btnPrenota = document.getElementById('btn-prenota');
        
        // Impostiamo la data minima a domani per impedire prenotazioni nel passato
        const dateInput = document.getElementById('data_visita');
        if(dateInput) {
            const domani = new Date();
            domani.setDate(domani.getDate() + 1);
            dateInput.min = domani.toISOString().split('T')[0];
        }

        // Iscrizione all'evento DOM globale generato da React
        document.addEventListener('aggiornamentoGattiScelti', function(event) {
            // Estraiamo il pacchetto dati (l'array dei gatti scelti in React)
            const gattiSelezionati = event.detail; 
            
            // Puliamo la lista visiva (DOM Manipulation Vanilla)
            ulElement.innerHTML = '';

            if (gattiSelezionati.length === 0) {
                // Stato vuoto (CSS pseudo-class gestisce il grigio)
                ulElement.innerHTML = '<li class="nessun-gatto-selezionato">Nessun gatto selezionato al momento. Clicca sulle card in basso.</li>';
                hiddenInput.value = "";
                btnPrenota.disabled = true; 
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
                btnPrenota.disabled = false; // Sblocca il pulsante (CSS rimuove il grigio)
            }
        });
    });
</script>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>