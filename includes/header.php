<?php
// includes/header.php
// Avviamo la sessione in modo sicuro (Slide 24)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gattile PoliTo</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="logo">
            <h1>🐾 Gattile PoliTo</h1>
        </div>
        <nav class="user-status">
            <?php
            // Controllo dello stato utente come da specifiche di progetto
            if (isset($_SESSION['username'])) {
                echo "<span>Benvenuto, <strong>" . htmlspecialchars($_SESSION['username']) . "</strong></span>";
                echo " | <a href='logout.php'>Logout</a>";
            } else {
                echo "<span><em>Non loggato</em></span>";
                echo " | <a href='login.php'>Login</a> o <a href='registrazione.php'>Registrati</a>";
            }
            ?>
        </nav>
    </header>
    <main> <!-- Inizio del contenuto principale -->