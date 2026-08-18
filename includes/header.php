<?php
/*
 * Intestazione comune del sito
 * Gestisce la sessione, la navigazione principale e lo stato dell'utente
 */

// La sessione viene inizializzata prima di qualsiasi output HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// La variabile serve a mostrare il collegamento al pannello solo agli amministratori
// L'autorizzazione viene comunque verificata nuovamente nelle pagine riservate
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Il Parco delle Fusa è un rifugio per gatti in difficoltà: scopri i nostri ospiti, prenota una visita e sostieni le attività del rifugio.">

    <!-- La viewport permette al layout di adattarsi dinamicamente ai dispositivi -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Il Parco delle Fusa - Gattile</title>

    <!-- Favicon del sito -->
    <link rel="icon" type="image/png" href="assets/img/favoriteicon_parco.png">

    <!-- Le connessioni ai server dei font vengono preparate in anticipo per ridurre l'attesa durante il caricamento -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Foglio di stile esterno per il font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Foglio di stile globale condiviso da tutte le pagine -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<header class="site-header">
    <div class="header-container">

        <!-- Il testo visibile identifica già il collegamento alla Home, quindi non è necessario aggiungere aria-label -->
        <a href="home.php" class="logo-link">
            <img src="assets/img/logo_icona.png" alt="" class="logo-icona">
            <span>Il Parco delle Fusa</span>
        </a>

        <!-- Navigazione principale del sito -->
        <nav class="main-nav" aria-label="Navigazione principale">
            <ul>
                <li><a href="ospiti.php">I Nostri Ospiti</a></li>
                <li><a href="volontariato.php">Diventa Volontario</a></li>
                <li><a href="sostienici.php">Sostienici</a></li>
            </ul>
        </nav>

        <!-- Area relativa allo stato di autenticazione -->
        <div class="user-area">

            <!-- La presenza dello username in sessione distingue l'utente autenticato dal visitatore -->
            <?php if (isset($_SESSION['username'])): ?>

                <!-- details e summary creano il menu account espandibile direttamente tramite HTML senza richiedere JavaScript -->
                <details class="account-menu">
                    <summary class="account-summary">
                        <img src="assets/img/icona_utente.png" width="128" height="128" alt="" class="icona-utente">

                        <!-- Lo username viene codificato prima dell'output HTML per evitare l'interpretazione di eventuale markup e prevenire XSS -->
                        <span><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </summary>

                    <div class="account-dropdown">

                        <!-- Tutti gli utenti autenticati possono consultare le proprie attività -->
                        <a href="area_personale.php">Le Mie Attività</a>

                        <?php if ($is_admin): ?>
                            <!-- Il collegamento amministrativo viene mostrato soltanto agli account amministratori -->
                            <a href="admin.php">Pannello Amministratore</a>
                        <?php endif; ?>
                    </div>
                </details>

                <!-- Il logout resta immediatamente disponibile fuori dal menu account -->
                <a href="logout.php" class="btn-outline">Logout</a>

            <?php else: ?>

                <!-- Se lo username non è presente in sessione viene mostrato lo stato non autenticato -->
                <span class="status-testo">Non loggato</span>
                <a href="login.php" class="btn-outline">Area Riservata</a>

            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Inizio del contenuto principale specifico di ciascuna pagina -->
<main>