<?php
// Avvio della sessione per la gestione dello stato utente
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il Parco delle Fusa - Gattile PoliTo</title>
    
    <!-- Importazione del font Poppins (lo stesso stile dello screenshot) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Foglio di stile principale -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header scuro con layout a linea singola -->
    <header class="site-header">
        <div class="header-container">
            
            <div class="logo">
                <a href="home.php">Il Parco delle Fusa</a>
            </div>
            
            <!-- Menu di navigazione centrale -->
            <nav class="main-nav">
                <ul>
                    <li><a href="home.php#chi-siamo">Il Gattile</a></li>
                    <li><a href="home.php#ospiti">I Nostri Ospiti</a></li>
                    <li><a href="home.php#sostienici">Sostienici</a></li>
                    <?php
                    // Link admin visibile solo agli amministratori
                    if (isset($_SESSION['username']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                        echo "<li><a href='inserimento_gatto.php' class='link-admin'>+ Admin</a></li>";
                    }
                    ?>
                </ul>
            </nav>

            <!-- Area utente a destra (Stile bottone a pillola + Stato) -->
            <div class="user-area">
                <?php
                if (isset($_SESSION['username'])) {
                    // Requisito: indicazione dello username se autenticato
                    echo "<span class='status-testo'>👤 " . htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') . "</span>";
                    echo "<a href='logout.php' class='btn-outline'>Logout</a>";
                } else {
                    // Requisito tassativo: scritta "non loggato" se non autenticato
                    echo "<span class='status-testo'>Non loggato</span>";
                    echo "<a href='login.php' class='btn-outline'>Area Riservata</a>";
                }
                ?>
            </div>

        </div>
    </header>
    
    <main>