<?php
/*
 * Intestazione comune del sito.
 * Gestisce la sessione, la navigazione principale e lo stato dell'utente
 */

// La sessione viene inizializzata prima di qualsiasi output HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Controllo dello stato di amministratore. Viene riutilizzato per determinare
// il rendering condizionale di link sensibili e layout della navigazione.
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">

    <!-- La viewport permette al layout di adattarsi dinamicamente ai dispositivi (Mobile-First) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Il Parco delle Fusa - Gattile</title>

    <link rel="icon" type="image/png" href="assets/img/favicon.png">

    <!-- Preconnect ottimizza i tempi di caricamento DNS per i font esterni -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Foglio di stile globale -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-container">

        <!-- Logo e collegamento alla home dotati di aria-label per conformità WCAG -->
        <a href="home.php" class="logo-link" aria-label="Torna alla Home">
            <img src="assets/img/logo_icona.png" alt="" class="logo-icona">
            <span>Il Parco delle Fusa</span>
        </a>

        <!-- Navigazione principale. Rendering condizionale della classe per spaziatura Admin -->
        <nav class="main-nav<?php echo $is_admin ? ' nav-admin-logged' : ''; ?>" aria-label="Navigazione principale">
            <ul>
                <li><a href="ospiti.php">I Nostri Ospiti</a></li>
                <li><a href="volontariato.php">Diventa Volontario</a></li>
                <li><a href="sostienici.php">Sostienici</a></li>
            </ul>
        </nav>

        <!-- Area relativa allo stato di autenticazione -->
        <div class="user-area">
            <?php if (isset($_SESSION['username'])): ?>

                <?php if ($is_admin): ?>
                    <a href="admin.php" class="btn-admin">+ Admin</a>
                <?php endif; ?>

                <span class="status-testo">
                    <img src="assets/img/icona_utente.png" alt="" class="icona-utente">
                    <?php
                    // Prevenzione vulnerabilità XSS: lo username, pur derivando dalla sessione,
                    // viene sempre sanificato prima del rendering in HTML.
                    echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
                    ?>
                </span>

                <a href="logout.php" class="btn-outline">Logout</a>

            <?php else: ?>

                <span class="status-testo">Non loggato</span>
                <a href="login.php" class="btn-outline">Area Riservata</a>

            <?php endif; ?>
        </div>

    </div>
</header>

<main>