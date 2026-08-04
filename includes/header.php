<?php
/**
 * Header comune della pagina.
 * Avvia la sessione PHP se non attiva e gestisce la visualizzazione
 * dinamica dello stato di autenticazione nella barra di navigazione.
 */

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
    <title>Gattile PoliTo</title>
    <!-- Collegamento al foglio di stile esterno -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="logo">
            <h1>Gattile PoliTo</h1>
        </div>
        <!-- Menu di navigazione con indicatore di stato login -->
        <nav class="user-status">
            <?php
            // Link alla Home visibile a tutti
            echo "<a href='index.php'>Home</a> | ";

            // Verifica se l'utente è autenticato in sessione
            if (isset($_SESSION['username'])) {
                
                // Controllo dei privilegi: mostra il link di inserimento solo se l'utente è admin
                if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                    // Uso di uno stile in linea leggermente diverso per evidenziare il pannello admin
                    echo "<a href='inserimento_gatto.php' style='color: #ffe082; font-weight: bold;'>+ Inserisci Felino</a> | ";
                }
                
                // Utente loggato: mostra nome e link di logout
                echo "<span>Utente: <strong>" . htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') . "</strong></span>";
                echo " | <a href='logout.php'>Logout</a>";
            } else {
                // Utente anonimo: mostra stato e link di accesso/registrazione
                echo "<span>Non loggato</span>";
                echo " | <a href='login.php'>Login</a> o <a href='registrazione.php'>Registrati</a>";
            }
            ?>
        </nav>
    </header>
    <!-- Inizio del contenuto principale -->
    <main>