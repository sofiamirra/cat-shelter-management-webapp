<?php
/*
 * Area riservata agli amministratori
 * Fornisce l'accesso alla funzione prevista per la gestione dei nuovi ospiti
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Il controllo viene effettuato lato server e non dipende dalla visibilità del link nell'header
$is_admin = isset($_SESSION['username'])
    && isset($_SESSION['is_admin'])
    && (int) $_SESSION['is_admin'] === 1;

if (!$is_admin) {
    header('Location: home.php');
    exit;
}

require 'includes/header.php';
?>

<div class="page-wrapper">
    <header class="section-header">
        <h2>Area Amministratore</h2>
        <div class="paw-divider" aria-hidden="true">
            <span class="line"></span>
            <span class="paw">🐾</span>
            <span class="line"></span>
        </div>
        <p class="header-subtitle">Gestisci l'inserimento dei nuovi ospiti del rifugio.</p>
    </header>

    <!-- Il pannello espone soltanto la funzione amministrativa prevista dal progetto -->
    <section class="prenotazione-wrapper text-center">
        <h2>Gestione Ospiti</h2>
        <p class="mt-1">Da questa sezione puoi registrare la scheda di un nuovo gatto accolto nella struttura.</p>

        <div class="mt-2">
            <a href="inserimento_gatto.php" class="btn-solid-dark">Inserisci un Nuovo Gatto</a>
        </div>
    </section>
</div>

<?php require 'includes/footer.php'; ?>