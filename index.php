
<?php
// Définition du titre de la page.
// Cette variable pourra être réutilisée sur les autres pages.
$pageTitle = "Vite & Gourmand - Traiteur à Bordeaux";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Encodage UTF-8 pour afficher correctement les accents -->
    <meta charset="UTF-8">

    <!-- Adaptation de l'affichage aux téléphones et tablettes -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Description de la page pour les moteurs de recherche -->
    <meta name="description"
          content="Vite & Gourmand, traiteur à Bordeaux depuis 25 ans.">

    <!-- Affichage sécurisé du titre défini en PHP -->
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Chargement de notre feuille de style CSS -->
    <link rel="stylesheet" href="public/css/style.css">
</head>

<body>

    <!-- EN-TÊTE : logo et navigation principale -->
    <header>
        <nav class="navigation" aria-label="Navigation principale">

            <!-- Logo textuel permettant de revenir à l'accueil -->
            <a class="logo" href="index.php">Vite & Gourmand</a>

            <!-- Liens vers les différentes pages du site -->
            <ul>
                <li><a href="index.php" aria-current="page">Accueil</a></li>
                <li><a href="menus.php">Nos menus</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="connexion.php">Connexion</a></li>
            </ul>
        </nav>
    </header>

    <!-- MAIN : contenu principal de la page -->
    <main>

        <!-- SECTION 1 : présentation principale -->
        <section class="hero">
            <h1>Bienvenue chez Vite & Gourmand</h1>

            <p>
                Depuis 25 ans, nous accompagnons vos
                événements avec passion à Bordeaux.
            </p>

            <!-- Bouton vers le catalogue des menus -->
            <a class="bouton" href="menus.php">
                Découvrir nos menus
            </a>
        </section>

        <!-- SECTION 2 : présentation de l'entreprise -->
        <section class="presentation">
            <h2>Notre savoir-faire</h2>

            <p>
                Julie et José mettent leur expérience
                au service de vos repas et événements.
                Découvrez une cuisine gourmande et des
                menus adaptés à vos occasions.
            </p>
        </section>

        <!-- SECTION 3 : futurs avis clients -->
        <!-- Les avis seront récupérés en base de données. -->
        <!-- Seuls les avis validés seront affichés. -->
        <section class="avis">
            <h2>Les avis de nos clients</h2>

            <p>
                Retrouvez prochainement les avis
                validés de nos clients.
            </p>
        </section>
    </main>

    <!-- PIED DE PAGE : informations et liens obligatoires -->
    <footer>
        <h2>Nos horaires</h2>

        <!-- Les horaires réels seront ajoutés plus tard -->
        <p>Les horaires seront bientôt disponibles.</p>

        <!-- Liens vers les pages réglementaires -->
        <p>
            <a href="mentions-legales.php">Mentions légales</a>
            |
            <a href="cgv.php">Conditions générales de vente</a>
        </p>

        <!-- PHP affiche automatiquement l'année actuelle -->
        <p>&copy; <?= date('Y') ?> Vite & Gourmand</p>
    </footer>

</body>
</html>
