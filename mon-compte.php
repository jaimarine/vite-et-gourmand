
<?php
/*
 * PROJET : Vite & Gourmand
 * PAGE : Espace personnel
 *
 * Cette page nécessite une session active.
 */


// Chargement des fonctions d'authentification.
require __DIR__ . '/config/auth.php';

// Vérifie la session et l'état du compte en base.
exigerConnexion();


// Sécurisation de l'affichage.
function e(string $texte): string
{
    return htmlspecialchars(
        $texte,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Récupération des informations de session.
$prenom = $_SESSION['utilisateur_prenom'] ?? '';
$nom = $_SESSION['utilisateur_nom'] ?? '';
$role = $_SESSION['utilisateur_role'] ?? '';

/*
 * ÉTAPE 19.2.2 : IDENTIFICATION DU CLIENT
 *
 * Récupère l'identifiant du compte connecté.
 * Il servira à afficher uniquement ses commandes.
 */

// Identifiant de l'utilisateur connecté.
$utilisateurId = (int) ($_SESSION['utilisateur_id'] ?? 0);

/*
 * ÉTAPE 19.2.3 : RÉCUPÉRATION DES COMMANDES
 *
 * Charge les commandes du client connecté.
 * Les employés et administrateurs ne sont pas concernés.
 */

// Tableau qui contiendra les commandes du client.
$commandes = [];

// Exécution uniquement pour les comptes clients.
if ($role === 'utilisateur' && $utilisateurId > 0) {

    // Connexion à la base de données MariaDB.
    require_once __DIR__ . '/config/database.php';

    // Requête préparée pour récupérer les commandes du client.
    $requete = $pdo->prepare("
        SELECT
            c.id,
            c.quantite,
            c.prix_total,
            c.statut,
            c.date_commande,
            c.date_prestation,
            m.titre AS nom_menu
        FROM commandes AS c
        INNER JOIN menus AS m ON c.menu_id = m.id
        WHERE c.utilisateur_id = :utilisateur_id
        ORDER BY c.date_commande DESC, c.id DESC
    ");

    // Utilisation de l'identifiant du client connecté.
    $requete->execute([
        'utilisateur_id' => $utilisateurId
    ]);

    // Récupération des résultats sous forme de tableau.
    $commandes = $requete->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Mon compte - Vite & Gourmand</title>

    <!-- ÉTAPE 19.2.7.3 : Force le navigateur à charger le CSS actualisé. -->
<link rel="stylesheet" href="public/css/style.css?v=2">
</head>

<body>

<header>
    <nav class="navigation" aria-label="Navigation principale">
        <a class="logo" href="index.php">Vite & Gourmand</a>

        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="menus.php">Nos menus</a></li>
            <li>
                <a href="deconnexion.php">
                    Déconnexion
                </a>
            </li>
        </ul>
    </nav>
</header>

<main class="page-formulaire">

    <h1>
        Bienvenue
        <?= e($prenom) ?>
        <?= e($nom) ?> !
    </h1>

    <p>
        Vous êtes connecté à votre espace personnel.
    </p>

    <p>
        <strong>Votre rôle :</strong>
        <?= e($role) ?>
    </p>

<!--
    ÉTAPE 19.2.4 : AFFICHAGE DES COMMANDES

    Affiche uniquement les commandes du client connecté.
    Les employés et administrateurs ne voient pas cette rubrique.
-->

<?php if ($role === 'utilisateur'): ?>

    <section>
        <h2>Mes commandes</h2>

        <?php if (empty($commandes)): ?>

            <p>
                Vous n'avez pas encore passé de commande.
            </p>

        <?php else: ?>

            <table class="tableau-commandes">
                <thead>
                    <tr>
                        <th scope="col">N°</th>
                        <th scope="col">Menu</th>
                        <th scope="col">Quantité</th>
                        <th scope="col">Prix total</th>
                        <th scope="col">Date de prestation</th>
                        <th scope="col">Statut</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($commandes as $commande): ?>

                        <tr>
                            <td>
                                <?= (int) $commande['id'] ?>
                            </td>

                            <td>
                                <?= e($commande['nom_menu']) ?>
                            </td>

                            <td>
                                <?= (int) $commande['quantite'] ?>
                            </td>

                            <td>
                                <?= number_format(
                                    (float) $commande['prix_total'],
                                    2,
                                    ',',
                                    ' '
                                ) ?> €
                            </td>

<td>
    <?php
    /*
     * ÉTAPE 19.2.5 : FORMATAGE DE LA DATE
     *
     * Transforme la date enregistrée dans MariaDB
     * au format français JJ/MM/AAAA.
     */

    // Récupération de la date de prestation.
    $datePrestation = $commande['date_prestation'];

    // Vérifie qu'une date est renseignée.
    if (!empty($datePrestation)) {

        // Conversion de la date MariaDB en objet DateTime.
        $dateObjet = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $datePrestation
        );

        // Affichage au format français.
        echo $dateObjet
            ? e($dateObjet->format('d/m/Y'))
            : 'Date invalide';

    } else {

        // Cas des anciennes commandes sans date.
        echo 'Non renseignée';
    }
    ?>
</td>

<td>
    <?php
    /*
     * ÉTAPE 19.2.6 : AFFICHAGE DU STATUT
     *
     * Convertit les statuts techniques de MariaDB
     * en textes lisibles pour le client.
     */

    // Liste des statuts disponibles.
    $statuts = [
        'en_attente' => 'En attente',
        'confirmee' => 'Confirmée',
        'en_preparation' => 'En préparation',
        'livree' => 'Livrée',
        'annulee' => 'Annulée'
    ];

    // Récupération du statut enregistré.
    $statutCommande = $commande['statut'];

    // Recherche du texte correspondant.
    $libelleStatut = $statuts[$statutCommande]
        ?? 'Statut inconnu';

    // Affichage sécurisé.
    echo e($libelleStatut);
    ?>
</td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>

    </section>

<?php endif; ?>

    <a href="deconnexion.php" class="bouton">
        Se déconnecter
    </a>

</main>

<footer>
    <p>Vite & Gourmand — Bordeaux</p>
</footer>

</body>
</html>
