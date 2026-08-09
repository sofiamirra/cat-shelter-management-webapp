<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il Parco delle Fusa - Gattile</title>
    
    <!-- Favicon: il trucco time() forza il caricamento bypassando qualsiasi cache! -->
    <link rel="icon" type="image/png" href="assets/img/logo_gattile.png?v=<?php echo time(); ?>">
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Foglio di stile principale -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-container">
        
        <!-- Logo (Funge da tasto Home pulito) -->
        <div class="logo">
            <a href="home.php" aria-label="Torna alla Home">
                <img src="assets/img/logo_icona.png" alt="Logo Parco delle Fusa" class="logo-icona">
                Il Parco delle Fusa
            </a>
        </div>

        <!-- Navigazione Principale (Rimossa la voce Home/Gattile per UX) -->
        <nav class="main-nav">
            <ul>
                <li><a href="ospiti.php">I Nostri Ospiti</a></li>
                <li><a href="volontariato.php">Diventa Volontario</a></li>
                <li><a href="sostienici.php">Sostienici</a></li>
            </ul>
            </nav>

<!-- Area Utente (Tutto spinto a destra) -->
<div class="user-area">
    <?php if (isset($_SESSION['username'])): ?>
        
        <!-- Link Admin protetto (Ora è DENTRO user-area) -->
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true): ?>
            <a href="admin.php" class="btn-admin">+ Admin</a>
        <?php endif; ?>

        <div class="status-testo">
            <img src="assets/img/icona_utente.png" alt="Utente" class="icon-utente-canva">
            <?php echo htmlspecialchars($_SESSION['username']); ?>
        </div>
        <a href="logout.php" class="btn-outline">Logout</a>
        
    <?php else: ?>
        <span class="status-testo">Non loggato</span>
        <a href="login.php" class="btn-outline">Area Riservata</a>
    <?php endif; ?>
</div>

</div>
</header>

<main>