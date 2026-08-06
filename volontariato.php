<?php
session_start();

// Protezione della pagina: solo utenti registrati
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
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
    <div class="volontariato-intro-content">
        <div class="volontariato-intro-text">
            <!-- Testo unico senza spazi a capo -->
            <p>
                Il Parco delle Fusa ha bisogno di persone appassionate per garantire il benessere quotidiano dei nostri ospiti felini. Diventare volontario significa donare una parte del proprio tempo per migliorare la vita dei gatti in attesa di adozione. Non serve esperienza pregressa, ma solo tanta affidabilità, costanza e un amore incondizionato per gli animali.
            </p>
            <!-- Invito esplicito -->
            <span class="testo-invito">
                Leggi le mansioni e prenota il tuo turno!👇
            </span>
        </div>
        <div class="volontariato-intro-image">
            <!-- Inserisci qui il nome del file della tua immagine -->
            <img src="assets/img/volontario_gatto.png" alt="Volontario con gatto">
        </div>
    </div>
</div>

<!-- ==========================================
     2. MANSIONI DEL VOLONTARIO
     ========================================== -->
<section class="ruoli-volontariato">
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
                <span class="ruolo-icona">🧹</span>
                <h4>Turno del Mattino</h4>
                <p>Aiutaci al risveglio dei felini! Ti occuperai di igienizzare le lettiere, preparare il cibo e riordinare gli spazi comuni.</p>
            </div>
            <div class="ruolo-card">
                <span class="ruolo-icona">🥣</span>
                <h4>Turno della Sera</h4>
                <p>Assicura ai mici una serena buonanotte. Le mansioni includono il ripristino del cibo secco e tante coccole serali.</p>
            </div>
            <div class="ruolo-card">
                <span class="ruolo-icona">🧶</span>
                <h4>Socializzazione</h4>
                <p>Disponibilità di un pomeriggio a settimana per far socializzare i mici più selvatici e timorosi.</p>
            </div>
            <div class="ruolo-card">
                <span class="ruolo-icona">🎪</span>
                <h4>Gestione Eventi</h4>
                <p>Cerchiamo aiuto per la gestione degli eventi sul territorio, per i mercatini solidali e le raccolte fondi.</p>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     3. PRENOTAZIONE TURNI
     ========================================== -->
<div class="page-wrapper volontariato-form-wrapper">
    
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

    <div class="prenotazione-wrapper volontariato-prenotazione-box">
        
        <!-- Box Messaggi generato via JS -->
        <div id="messaggio-esito" class="d-none"></div>

        <form id="form-volontariato">
            <div class="form-group">
                <label for="data_turno" class="form-label-title">Seleziona la data del turno:</label>
                <input type="date" id="data_turno" name="data_turno" class="input-data-large" required>
            </div>

            <!-- Il blocco fasce parte invisibile con classe d-none -->
            <div class="form-group d-none" id="sezione-fasce">
                <label class="form-label-title">Fasce orarie disponibili (selezionane una o più):</label>
                <div class="fasce-orarie-container" id="contenitore-orari">
                    
                    <label class="fascia-oraria-label" id="label-mattina">
                        <input type="checkbox" name="fasce[]" value="09:00:00" class="chk-fascia">
                        Mattina (09 - 13)
                    </label>
                    
                    <label class="fascia-oraria-label" id="label-pomeriggio">
                        <input type="checkbox" name="fasce[]" value="14:00:00" class="chk-fascia">
                        Pomeriggio (14 - 18)
                    </label>
                    
                    <label class="fascia-oraria-label" id="label-sera">
                        <input type="checkbox" name="fasce[]" value="18:00:00" class="chk-fascia">
                        Sera (18 - 22)
                    </label>
                    
                </div>
            </div>

            <button type="submit" class="btn-solid-dark w-100 mt-1" id="btn-invia-turno" disabled>Conferma Disponibilità</button>
        </form>
    </div>
</div>

<!-- ==========================================
     4. LOGICA JS ASINCRONA (MISSIONE 6)
     ========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dataInput = document.getElementById('data_turno');
    const sezioneFasce = document.getElementById('sezione-fasce');
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
        if (!dataScelta) return;

        sezioneFasce.classList.remove('d-none');
        msgBox.classList.add('d-none');
        
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

    // Sottomissione asincrona
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
                msgBox.className = 'auth-alert-success'; 
                // Messaggio con classe CSS esterna per l'indirizzo (0 inline CSS)
                msgBox.innerHTML = '<strong>' + data.message + ' 🎉</strong><br><span class="msg-indirizzo">Ti aspettiamo in 📍 <strong>Via Roma 123, 10100 Torino (TO)</strong> dove verrai accolto dagli altri volontari. A presto!</span>';
                
                form.reset();
                sezioneFasce.classList.add('d-none'); 
                checkboxes.forEach(chk => chk.parentElement.classList.remove('selezionato'));
            } else {
                msgBox.className = 'auth-alert-danger';
                msgBox.innerHTML = '<strong>Errore:</strong> ' + data.message;
            }
        })
        .catch(error => {
            msgBox.classList.remove('d-none');
            msgBox.className = 'auth-alert-danger';
            msgBox.innerHTML = '<strong>Errore:</strong> Impossibile contattare il server. Riprova più tardi.';
        })
        .finally(() => {
            btnInvia.disabled = false;
            btnInvia.textContent = 'Conferma Disponibilità';
        });
    });
});
</script>

<?php require 'includes/footer.php'; ?>