

<?php
/*
 * PAGE : Détail d'un menu
 *
 * Les informations proviennent maintenant de MySQL.
 */

require __DIR__ . '/config/database.php';

// Validation de l'identifiant reçu dans l'URL.
$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

// Valeurs par défaut.
$menu = null;
$plats = [];

// Recherche uniquement si l'identifiant est valide.
if ($id !== false && $id !== null && $id > 0) {

    // Requête préparée contre les injections SQL.
    $requete = $pdo->prepare(
        "SELECT
            id, titre, description, theme, regime,
            personnes_minimum AS personnes,
            prix, stock, conditions
         FROM menus
         WHERE id = :id AND actif = 1"
    );

    // Exécution avec le paramètre utilisateur.
    $requete->execute(['id' => $id]);

    // Récupération du menu.
    $menu = $requete->fetch() ?: null;

    if ($menu !== null) {


// Récupération des plats et de leurs allergènes.
// LEFT JOIN conserve les plats même si aucun
// allergène n'est encore renseigné.
$requetePlats = $pdo->prepare(
    "SELECT
        p.id,
        p.nom,
        p.type,

        -- Regroupe les allergènes d'un même plat.
        GROUP_CONCAT(
            DISTINCT a.nom
            ORDER BY a.nom
            SEPARATOR ', '
        ) AS allergenes

     FROM plats AS p

     INNER JOIN menu_plats AS mp
        ON mp.plat_id = p.id

     LEFT JOIN plat_allergenes AS pa
        ON pa.plat_id = p.id

     LEFT JOIN allergenes AS a
        ON a.id = pa.allergene_id

     WHERE mp.menu_id = :id

     GROUP BY p.id, p.nom, p.type

     ORDER BY FIELD(
        p.type, 'entree', 'plat', 'dessert'
     )"
);


        $requetePlats->execute(['id' => $id]);

        $plats = $requetePlats->fetchAll();
    }
}

// Réponse 404 si le menu n'existe pas.
if ($menu === null) {
    http_response_code(404);
}

// Sécurisation de l'affichage HTML.
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

    <!-- Adaptation aux écrans mobiles -->
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= $menu ? e($menu['titre']) : 'Menu introuvable' ?>
        - Vite & Gourmand
    </title>

    <!-- Chargement du CSS commun -->
    <link rel="stylesheet" href="public/css/style.css">
</head>

<body>

    <!-- Navigation principale -->
    <header>
        <nav class="navigation" aria-label="Navigation principale">
            <a class="logo" href="index.php">Vite & Gourmand</a>

            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="menus.php">Nos menus</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="connexion.php">Connexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="detail-menu">

        <?php if ($menu === null): ?>

            <!-- Message affiché pour un identifiant inconnu -->
            <h1>Menu introuvable</h1>

            <p>Le menu demandé n'existe pas.</p>

            <a class="bouton" href="menus.php">
                Retour aux menus
            </a>

        <?php else: ?>

            <!-- Informations principales du menu -->
            <h1><?= e($menu['titre']) ?></h1>

            <p><?= e($menu['description']) ?></p>

            <div class="infos-menu">

                <p>
                    <strong>Thème :</strong>
                    <?= e($menu['theme']) ?>
                </p>

                <p>
                    <strong>Régime :</strong>
                    <?= e($menu['regime']) ?>
                </p>

                <p>
                    <strong>Minimum :</strong>
                    <?= (int) $menu['personnes'] ?> personnes
                </p>

                <p>
                    <strong>Prix de départ :</strong>
                    <?= number_format(
                        $menu['prix'],
                        2,
                        ',',
                        ' '
                    ) ?> €
                </p>

                <p>
                    <strong>Stock disponible :</strong>
                    <?= (int) $menu['stock'] ?>
                </p>
            </div>

            <!-- Liste des plats du menu -->
            <section aria-labelledby="titre-plats">
                <h2 id="titre-plats">Composition du menu</h2>

                <?php foreach ($plats as $plat): ?>

                    <article class="plat-menu">
                        <h3>
                            <?= e($plat['type']) ?> :
                            <?= e($plat['nom']) ?>
                        </h3>

                        <!-- Affichage des allergènes -->

                    <!-- Affichage des allergènes récupérés depuis MySQL -->
                    <p>
                        <strong>Allergènes renseignés :</strong>

                        <?php if (!empty($plat['allergenes'])): ?>

                            <!-- Affichage sécurisé des allergènes -->
                            <?= e($plat['allergenes']) ?>

                        <?php else: ?>

                            <!-- Ne pas affirmer qu'un plat est sans allergènes
                                si aucune donnée n'a été enregistrée. -->
                            Informations non renseignées.

                        <?php endif; ?>
                    </p>

                    </article>

                <?php endforeach; ?>
            </section>

            <!-- Conditions importantes de commande -->
            <section class="conditions-menu"
                     aria-labelledby="titre-conditions">

                <h2 id="titre-conditions">
                    Conditions de commande
                </h2>

                <p><?= e($menu['conditions']) ?></p>

                <p>
                    Veuillez prendre connaissance des
                    conditions avant de commander.
                </p>
            </section>

            <!-- Bouton de commande -->
            <!-- La fonctionnalité sera développée ensuite -->
            <a class="bouton"
               href="commande.php?menu=<?= (int) $menu['id'] ?>">
                Commander ce menu
            </a>

        <?php endif; ?>

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
