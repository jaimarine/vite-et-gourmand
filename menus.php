

<?php
/*
 * PAGE : Catalogue des menus
 *
 * Les menus proviennent maintenant de MariaDB.
 */

// Connexion à la base de données.
require __DIR__ . '/config/database.php';

// Récupération de tous les menus.
// Le tri alphabétique facilite la consultation.
// Requête SQL pour récupérer uniquement les menus actifs.

// Requête SQL pour récupérer uniquement les menus actifs.
$sql = "SELECT
            id,
            titre,
            description,
            theme,
            regime,
            personnes_minimum AS personnes,
            prix,
            stock
        FROM menus
        WHERE actif = 1
        ORDER BY titre ASC";

// Exécution de la requête SQL.
$requete = $pdo->query($sql);

// Récupération des menus dans un tableau PHP.
$menus = $requete->fetchAll();

// Fermeture du bloc PHP avant le HTML.
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <!-- Affichage adapté aux mobiles -->
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">


    <title>Nos menus - Vite & Gourmand</title>

    <!-- Feuille de style commune au site -->
    <link rel="stylesheet" href="public/css/style.css">

    <!-- Script pour les filtres dynamiques -->
    <script src="public/js/menus.js" defer></script>
</head>

<body>

    <!-- Navigation principale -->
    <header>
        <nav class="navigation" aria-label="Navigation principale">
            <a class="logo" href="index.php">Vite & Gourmand</a>

            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="menus.php" aria-current="page">Nos menus</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="connexion.php">Connexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="catalogue">

        <h1>Découvrez nos menus</h1>

        <p>
            Choisissez le menu idéal pour votre événement.
        </p>

        <!-- Zone de filtrage des menus -->
        <section class="filtres" aria-labelledby="titre-filtres">
            <h2 id="titre-filtres">Filtrer les menus</h2>

            <!-- Filtre sur le prix maximum -->
            <label for="prix-max">Prix maximum (€)</label>
            <input
                type="number"
                id="prix-max"
                min="0"
                placeholder="Exemple : 150"
            >

            <!-- Filtre sur le thème -->
            <label for="filtre-theme">Thème</label>
            <select id="filtre-theme">
                <option value="">Tous les thèmes</option>
                <option value="Noël">Noël</option>
                <option value="Pâques">Pâques</option>
                <option value="Classique">Classique</option>
            </select>

            <!-- Filtre sur le régime alimentaire -->
            <label for="filtre-regime">Régime</label>
            <select id="filtre-regime">
                <option value="">Tous les régimes</option>
                <option value="classique">Classique</option>
                <option value="vegetarien">Végétarien</option>
            </select>

            <!-- Filtre sur le nombre minimal de personnes -->
            <label for="filtre-personnes">
                Nombre minimal de personnes du menu
            </label>
            <input
                type="number"
                id="filtre-personnes"
                min="1"
                placeholder="Exemple : 4"
            >
        </section>

        <!-- Résultats du catalogue -->
        <section aria-labelledby="titre-catalogue">
            <h2 id="titre-catalogue">Les menus disponibles</h2>

            <p id="nombre-resultats" aria-live="polite"></p>

            <div class="grille-menus">

                <?php foreach ($menus as $menu): ?>

                    <!--
                        Chaque menu possède des attributs data-*.
                        JavaScript les utilisera pour filtrer
                        les cartes sans recharger la page.
                    -->
                    <article
                        class="carte-menu"
                        data-prix="<?= $menu['prix'] ?>"
                        data-theme="<?= htmlspecialchars($menu['theme'], ENT_QUOTES, 'UTF-8') ?>"
                        data-regime="<?= htmlspecialchars($menu['regime'], ENT_QUOTES, 'UTF-8') ?>"
                        data-personnes="<?= $menu['personnes'] ?>"
                    >

                        <h3>
                            <?= htmlspecialchars($menu['titre'], ENT_QUOTES, 'UTF-8') ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars($menu['description'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <p>
                            <strong>Thème :</strong>
                            <?= htmlspecialchars($menu['theme'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <p>
                            <strong>Régime :</strong>
                            <?= htmlspecialchars($menu['regime'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <p>
                            <strong>À partir de :</strong>
                            <?= $menu['personnes'] ?> personnes
                        </p>

                        <p class="prix-menu">
                            <?= number_format($menu['prix'], 2, ',', ' ') ?> €
                        </p>

                        <!-- La page de détail sera créée plus tard -->
                        <a
                            class="bouton"
                            href="detail-menu.php?id=<?= $menu['id'] ?>"
                        >
                            Voir le détail
                        </a>

                    </article>

                <?php endforeach; ?>

            </div>
        </section>

    </main>

    <footer>
        <p>Vite & Gourmand — Bordeaux</p>
        <p>Horaires à compléter</p>

        <p>
            <a href="mentions-legales.php">Mentions légales</a>
            |
            <a href="cgv.php">CGV</a>
        </p>
    </footer>

</body>
</html>
