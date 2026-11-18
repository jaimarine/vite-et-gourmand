
<?php
/*
 * PROJET : Vite & Gourmand
 * FICHIER : config/session.php
 *
 * Gestion centralisée des sessions PHP.
 */

// Renforcement des paramètres du cookie de session.
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

// Démarrage de la session uniquement si nécessaire.
if (session_status() === PHP_SESSION_NONE) {

    // Configuration du cookie de session.
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}
