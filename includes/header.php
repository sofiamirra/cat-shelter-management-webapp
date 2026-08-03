<?php
/**
 * Header comune della pagina.
 * Avvia la sessione PHP se non attiva e gestisce la visualizzazione
 * dinamica dello stato di autenticazione nella barra di navigazione.
 */

// Avvio sicuro della sessione PHP per gestire lo stato utente
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
    <!-- Collegamento al foglio di stile esterno -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Intestazione visiva della pagina -->
    <header>
        <div class="logo">
            <h1>Gattile PoliTo</h1>
        </div>
        <!-- Barra di navigazione per lo stato utente (loggato / non loggato) -->
        <nav class="user-status">
            <?php
            // Controllo se l'utente ha una sessione attiva
            if (isset($_SESSION['username'])) {
                // Se loggato, mostra username e link di logout
                echo "<span>Utente: <strong>" . htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') . "</strong></span>";
                echo " | <a href='logout.php'>Logout</a>";
            } else {
                // Se anonimo, mostra i link per accedere o registrarsi
                echo "<span>Non loggato</span>";
                echo " | <a href='login.php'>Login</a> o <a href='registrazione.php'>Registrati</a>";
            }
            ?>
        </nav>
    </header>
    <!-- Apertura del blocco principale del contenuto -->
    <main>