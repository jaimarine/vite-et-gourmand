
<?php
/*
 * PAGE : Espace employé
 *
 * Accessible aux employés et administrateurs.
 */

require __DIR__ . '/config/auth.php';

// Contrôle des rôles autorisés.
exigerRole(['employe', 'administrateur']);

// Échappement des données affichées.
function e(string $texte): string
{
    return htmlspecialchars(
        $texte,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Espace employé - Vite & Gourmand</title>

    <link rel="stylesheet" href="public/css/style.css">
</head>

<body>

<header>
    <nav class="navigation" aria-label="Navigation principale">
        <a class="logo" href="index.php">Vite & Gourmand</a>

        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="mon-compte.php">Mon compte</a></li>
        </ul>
    </nav>
</header>

<main class="page-formulaire">

    <h1>Espace employé</h1>

    <p>
        Bienvenue
        <?= e($_SESSION['utilisateur_prenom'] ?? '') ?>.
    </p>

    <h2>Gestion du traiteur</h2>


    <!-- Accès à la gestion des menus -->
    <p>
        <a href="gestion-menus.php" class="bouton">
            Gérer les menus
        </a>
    </p>


<!--
    ÉTAPE 20.3 : GESTION DES COMMANDES

    Prépare l'accès à la page permettant aux
    employés de consulter et suivre les commandes.
-->

<p>
    <a href="gestion-commandes.php" class="bouton">
        Gérer les commandes
    </a>
</p>

<!-- Fonctionnalités à développer ultérieurement -->
<ul>
    <li>Gestion des avis clients</li>
    <li>Gestion des horaires</li>
</ul>

    <p>
        Ces fonctionnalités seront ajoutées
        au cours du développement.
    </p>

</main>

</body>
</html>
