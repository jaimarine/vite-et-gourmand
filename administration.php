
<?php
/*
 * PAGE : Administration
 *
 * Accessible uniquement à l'administrateur.
 */

require __DIR__ . '/config/auth.php';

// Seul le rôle administrateur est autorisé.²
exigerRole(['administrateur']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Administration - Vite & Gourmand</title>

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

    <h1>Administration</h1>

    <p>
        Cet espace est réservé à l'administrateur.
    </p>

    <h2>Fonctionnalités prévues</h2>

    <ul>
        <li>Création des comptes employés</li>
        <li>Désactivation des comptes employés</li>
        <li>Consultation des statistiques</li>
        <li>Suivi du chiffre d'affaires</li>
    </ul>

    <p>
        Le tableau de bord sera développé
        dans les prochaines étapes.
    </p>

<!-- Accès à la gestion des comptes employés -->
<section>
    <h2>Gestion du personnel</h2>

    <p>
        Créer, consulter et désactiver
        les comptes des employés.
    </p>

    <a href="gestion-employes.php" class="bouton">
        Gérer les employés
    </a>
</section>


</main>

</body>
</html>
