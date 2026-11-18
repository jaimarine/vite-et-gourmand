
<?php
/*
 * PROJET : Vite & Gourmand
 * PAGE : Déconnexion
 *
 * Supprime les informations de session
 * et invalide le cookie correspondant.
 */

require __DIR__ . '/config/session.php';

// Suppression des données de session.
$_SESSION = [];

// Suppression du cookie de session.
if (ini_get('session.use_cookies')) {

    $parametres = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 3600,
            'path' => $parametres['path'],
            'domain' => $parametres['domain'],
            'secure' => $parametres['secure'],
            'httponly' => $parametres['httponly'],
            'samesite' => $parametres['samesite']
        ]
    );
}

// Destruction de la session côté serveur.
session_destroy();

// Retour vers la page de connexion.
header('Location: connexion.php');
exit;
