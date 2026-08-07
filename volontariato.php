<?php
// Avvio della sessione per verificare lo stato di autenticazione corrente
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require 'includes/header.php';
?>

<!-- ==========================================
     1. INTRODUZIONE VOLONTARIATO
     ========================================== -->
<div class="page-wrapper volontariato-intro-wrapper">
    
    <!-- Titolo principale con decorazione -->
    <div class="section-header volontariato-header">
        <h2>Diventa Volontario</h2>
        <div class="paw-divider">
            <div class="line"></div>
            <i class="paw">🐾</i>
            <div class="line"></div>
        </div>
    </div>

 <!-- Contenuto compattato testuale e visivo -->
 <div class="volontariato-intro-content section-padding" style="padding-top: 0;">
        <div class="volontariato-intro-text">
            <p>
                Il Parco delle Fusa ha bisogno di persone appassionate per garantire il benessere quotidiano dei nostri ospiti felini. Diventare volontario significa donare una parte del proprio tempo per migliorare la vita dei gatti in attesa di adozione. Non serve esperienza pregressa, ma solo tanta affidabilità, costanza e un amore incondizionato per gli animali.
            </p>
            
            <!-- Wrapper dedicato per centratura e spacing perfetti -->
            <div class="volontariato-btn-wrapper">
                <a href="#prenota" class="btn-solid-dark">Prenota il tuo Turno</a>
            </div>
        </div>
        <div class="volontariato-intro-image">
            <img src="assets/img/volontario_gatto.png" alt="Volontario con gatto">
        </div>
    </div>
</div>

<!-- ==========================================
     2. MANSIONI DEL VOLONTARIO
     ========================================== -->
<section class="ruoli-volontariato section-padding">
    <div class="ruoli-container">
        
        <div class="section-header volontariato-header volontariato-header-mansioni">
            <h2>Le Mansioni del Volontario</h2>
            <div class="paw-divider">
                <div class="line"></div>
                <i class="paw">🐾</i>
                <div class="line"></div>
            </div>
        </div>

        <div class="ruoli-grid">
            <div class="ruolo-card">
                <img src="assets/img/icona_mattina.png" alt="Turno del mattino" class="icon-png-large">
                <h3>Turno del Mattino</h3>
                <p>Aiutaci a iniziare la giornata dei felini preparando il cibo e sistemando gli spazi.</p>
            </div>
            
            <div class="ruolo-card">
                <img src="assets/img/icona_sera.png" alt="Turno della sera" class="icon-png-large">
                <h3>Turno della Sera</h3>
                <p>Assicura ai mici una serena buonanotte. Rifornirai il cibo secco e gli dedicherai qualche coccola serale.</p>
            </div>
            
            <div class="ruolo-card">
                <img src="assets/img/icona_gioco.png" alt="Socializzazione" class="icon-png-large">
                <h3>Socializzazione</h3>
                <p>Disponibilità nella fascia pomeridiana per favorire la socializzazione dei mici più timorosi e diffidenti.</p>
            </div>
            
            <div class="ruolo-card">
                <img src="assets/img/icona_eventi.png" alt="Gestione eventi" class="icon-png-large">
                <h3>Gestione Eventi</h3>
                <p>Cerchiamo aiuto per la gestione degli eventi sul territorio, per i mercatini solidali e le raccolte fondi.</p>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     3. PRENOTAZIONE TURNI (O BANNER LOGIN)
     ========================================== -->
<div id="prenota"></div>

<div class="page-wrapper volontariato-form-wrapper section-padding">
    
    <div class="section-header volontariato-header">
        <h2>Prenota il tuo Turno</h2>
        <div class="paw-divider">
            <div class="line"></div>
            <i class="paw">🐾</i>
            <div class="line"></div>
        </div>
        <p class="header-subtitle">
            Il tuo tempo è il regalo più prezioso. Seleziona una data per scoprire le fasce orarie disponibili. Accettiamo un massimo di 2 volontari per turno.
        </p>
    </div>

    <div id="messaggio-esito" class="d-none alert-wrapper mb-4"></div>

    <?php if(isset($_SESSION['username'])): ?>
        <div class="prenotazione-wrapper volontariato-prenotazione-box">
            <form id="form-volontariato">
                <div class="form-group">
                    <label for="data_turno" class="form-label-title">Seleziona la data del turno:</label>
                    <input type="date" id="data_turno" name="data_turno" class="input-data-large input-data-full-width" required>
                </div>

                <!-- Il blocco fasce ora è visibile di default (rimossa classe d-none) -->
                <div class="form-group" id="sezione-fasce">
                    <label class="form-label-title mt-2">Fasce orarie disponibili (selezionane una o più):</label>
                    <div class="fasce-orarie-container" id="contenitore-orari">
                        
                        <label class="fascia-oraria-label" id="label-mattina">
                            <input type="checkbox" name="fasce[]" value="09:00:00" class="chk-fascia" disabled>
                            <span>Mattina (09 - 13)</span>
                        </label>
                        
                        <label class="fascia-oraria-label" id="label-pomeriggio">
                            <input type="checkbox" name="fasce[]" value="14:00:00" class="chk-fascia" disabled>
                            <span>Pomeriggio (14 - 18)</span>
                        </label>
                        
                        <label class="fascia-oraria-label" id="label-sera">
                            <input type="checkbox" name="fasce[]" value="18:00:00" class="chk-fascia" disabled>
                            <span>Sera (18 - 22)</span>
                        </label>
                        
                    </div>
                </div>

                <button type="submit" class="btn-solid-dark w-100 mt-2" id="btn-invia-turno" disabled>Conferma Disponibilità</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert-wrapper mb-4 text-center">
            <div class="auth-alert-success">
                Vuoi unirti alla nostra squadra di volontari? 🐾 <br>
                <a href="login.php?redirect=volontariato.php" class="auth-alert-link">Accedi o Registrati</a> per poter prenotare i tuoi turni.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ==========================================
     4. LOGICA JS ASINCRONA (SOLO PER LOGGATI)
     ========================================== -->
<?php if(isset($_SESSION['username'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dataInput = document.getElementById('data_turno');
    const btnInvia = document.getElementById('btn-invia-turno');
    const checkboxes = document.querySelectorAll('.chk-fascia');
    const form = document.getElementById('form-volontariato');
    const msgBox = document.getElementById('messaggio-esito');
    
    // Disabilita date passate
    const domani = new Date();
    domani.setDate(domani.getDate() + 1);
    dataInput.min = domani.toISOString().split('T')[0];

    // Ascolto cambio data per verifica AJAX
    dataInput.addEventListener('change', function() {
        const dataScelta = this.value;
        
        // Se l'utente cancella la data, disabilita e resetta i checkbox
        if (!dataScelta) {
             checkboxes.forEach(chk => {
                chk.disabled = true;
                chk.checked = false;
                chk.parentElement.classList.remove('pieno', 'selezionato');
            });
            btnInvia.disabled = true;
            return;
        }

        msgBox.classList.add('d-none');
        
        // Reset dei checkbox prima del caricamento
        checkboxes.forEach(chk => {
            chk.disabled = true;
            chk.checked = false;
            chk.parentElement.classList.remove('pieno', 'selezionato');
        });
        btnInvia.disabled = true;

        fetch('api_verifica_turni.php?data=' + dataScelta)
            .then(response => response.json())
            .then(conteggio => {
                checkboxes.forEach(chk => {
                    const fascia = chk.value;
                    const iscritti = conteggio[fascia] || 0;

                    if (iscritti >= 2) {
                        chk.disabled = true;
                        chk.parentElement.classList.add('pieno');
                    } else {
                        chk.disabled = false;
                    }
                });
            })
            .catch(err => {
                console.error("Errore nel recupero turni:", err);
                checkboxes.forEach(chk => chk.disabled = false);
            });
    });

    // Toggle selezione fasce orarie
    checkboxes.forEach(chk => {
        chk.addEventListener('change', function() {
            if (this.checked) {
                this.parentElement.classList.add('selezionato');
            } else {
                this.parentElement.classList.remove('selezionato');
            }

            const almenoUnaSelezionata = Array.from(checkboxes).some(c => c.checked);
            btnInvia.disabled = !almenoUnaSelezionata;
        });
    });

    // Sottomissione asincrona form
    form.addEventListener('submit', function(e) {
        e.preventDefault(); 

        const formData = new FormData(form);
        btnInvia.disabled = true;
        btnInvia.textContent = 'Elaborazione in corso...';

        fetch('processa_volontariato.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) 
        .then(data => {
            msgBox.classList.remove('d-none');
            
            if (data.status === 'success') {
                msgBox.innerHTML = `
                    <div class="auth-alert-success alert-dismissible">
                        <strong>${data.message} 🎉</strong><br>
                        Ti aspettiamo alla struttura, dove verrai istruito dagli altri volontari. A presto!
                        <button type="button" class="btn-close-alert" onclick="document.getElementById('messaggio-esito').classList.add('d-none')" aria-label="Chiudi">&times;</button>
                    </div>
                `;
                
                form.reset();
                checkboxes.forEach(chk => {
                    chk.disabled = true;
                    chk.parentElement.classList.remove('selezionato');
                });
            } else {
                msgBox.innerHTML = `
                    <div class="auth-alert-danger alert-dismissible">
                        <strong>Errore:</strong> ${data.message}
                        <button type="button" class="btn-close-alert" onclick="document.getElementById('messaggio-esito').classList.add('d-none')" aria-label="Chiudi">&times;</button>
                    </div>
                `;
            }
        })
        .catch(error => {
            msgBox.classList.remove('d-none');
            msgBox.innerHTML = `
                <div class="auth-alert-danger alert-dismissible">
                    <strong>Errore:</strong> Impossibile contattare il server. Riprova più tardi.
                    <button type="button" class="btn-close-alert" onclick="document.getElementById('messaggio-esito').classList.add('d-none')" aria-label="Chiudi">&times;</button>
                </div>
            `;
        })
        .finally(() => {
            btnInvia.disabled = false;
            btnInvia.textContent = 'Conferma Disponibilità';
        });
    });
});
</script>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>