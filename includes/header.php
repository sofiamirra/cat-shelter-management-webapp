<?php
/*
 * Intestazione comune del sito
 * Gestisce la sessione, la navigazione principale e lo stato dell'utente
 */

// La sessione viene inizializzata prima di qualsiasi output HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lo stato amministratore determina la presenza delle funzioni riservate
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Percorso di base utilizzato per la favicon del progetto
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">

    <!-- La viewport permette al layout di adattarsi dinamicamente ai dispositivi -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Il Parco delle Fusa - Gattile</title>

    <!-- Icona identificativa del sito -->
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($base_path, ENT_QUOTES, 'UTF-8'); ?>/assets/img/favoriteicon_parco.png?v=31">

    <!-- Preconnect per i font esterni -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Foglio di stile globale -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<header class="site-header">
    <div class="header-container">

        <!-- Logo e collegamento alla home -->
        <a href="home.php" class="logo-link" aria-label="Torna alla Home">
            <img src="assets/img/logo_icona.png" alt="" class="logo-icona">
            <span>Il Parco delle Fusa</span>
        </a>

        <!-- Navigazione principale -->
        <nav class="main-nav" aria-label="Navigazione principale">
            <ul>
                <li><a href="ospiti.php">I Nostri Ospiti</a></li>
                <li><a href="volontariato.php">Diventa Volontario</a></li>
                <li><a href="sostienici.php">Sostienici</a></li>
            </ul>
        </nav>

        <!-- Area relativa allo stato di autenticazione -->
        <div class="user-area">
            <?php if (isset($_SESSION['username'])): ?>

                <!-- Avatar e username aprono le funzioni associate all'account -->
                <details class="account-menu">
                    <summary class="account-summary">
                        <img src="assets/img/icona_utente.png" alt="" class="icona-utente">
                        <span><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </summary>

                    <div class="account-dropdown">
                        <a href="area_personale.php">Le Mie Attività</a>

                        <?php if ($is_admin): ?>
                            <a href="admin.php">Pannello Amministratore</a>
                        <?php endif; ?>
                    </div>
                </details>

                <!-- Il logout resta immediatamente disponibile -->
                <a href="logout.php" class="btn-outline">Logout</a>

            <?php else: ?>

                <span class="status-testo">Non loggato</span>
                <a href="login.php" class="btn-outline">Area Riservata</a>

            <?php endif; ?>
        </div>

    </div>
</header>

<main>