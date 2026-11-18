<?php
/*
 * PAGE : Gestion des commandes
 *
 * Cette page sera utilisée par les employés
 * et les administrateurs pour suivre les commandes.
 */

// Chargement du système d'authentification.
require __DIR__ . '/config/auth.php';

// Accès réservé aux employés et administrateurs.
exigerRole(['employe', 'administrateur']);

/*
 * ÉTAPE 20.9.2.2 : PROTECTION CSRF
 *
 * Création d'un jeton de sécurité unique
 * pour protéger les formulaires de modification
 * des commandes.
 */

// Génération du jeton s'il n'existe pas encore.
if (empty($_SESSION['csrf_commandes'])) {
    $_SESSION['csrf_commandes'] = bin2hex(random_bytes(32));
}

/*
 * ÉTAPE 20.6 : RÉCUPÉRATION DES COMMANDES
 *
 * Connexion à MariaDB et récupération des
 * commandes avec le nom du menu associé.
 */

// Chargement de la connexion à la base de données.
require_once __DIR__ . '/config/database.php';


/*
 * ÉTAPE 20.9.2.3 : MODIFICATION SÉCURISÉE DU STATUT
 *
 * Vérifie les données du formulaire avant
 * d'enregistrer le nouveau statut dans MariaDB.
 */

// Liste des statuts autorisés.
$statutsAutorises = [
    'en_attente',
    'confirmee',
    'en_preparation',
    'livree',
    'annulee'
];

// Traitement uniquement lors de l'envoi du formulaire.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification de l'action demandée.
    $action = (string) ($_POST['action'] ?? '');

    if ($action !== 'modifier_statut') {
        http_response_code(400);
        exit('Action non reconnue.');
    }

    // Vérification du jeton CSRF.
    $jetonRecu = (string) ($_POST['csrf_token'] ?? '');
    $jetonSession = (string) ($_SESSION['csrf_commandes'] ?? '');

    if (
        $jetonSession === ''
        || !hash_equals($jetonSession, $jetonRecu)
    ) {
        http_response_code(403);
        exit('Jeton CSRF invalide.');
    }

    // Vérification de l'identifiant de la commande.
    $commandeId = filter_var(
        $_POST['commande_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($commandeId === false || $commandeId === null || $commandeId <= 0) {
        http_response_code(400);
        exit('Identifiant de commande invalide.');
    }

    // Vérification du nouveau statut.
    $nouveauStatut = (string) ($_POST['nouveau_statut'] ?? '');

    if (!in_array($nouveauStatut, $statutsAutorises, true)) {
        http_response_code(400);
        exit('Statut non autorisé.');
    }

    // Requête préparée pour éviter les injections SQL.
    $requeteModification = $pdo->prepare("
        UPDATE commandes
        SET statut = :statut
        WHERE id = :commande_id
    ");

    // Enregistrement du nouveau statut.
    $requeteModification->execute([
        'statut' => $nouveauStatut,
        'commande_id' => $commandeId
    ]);

    // Redirection pour éviter un nouvel envoi du formulaire
    // lorsque l'utilisateur actualise la page.
    header('Location: gestion-commandes.php');
    exit;
}


// Requête SQL pour récupérer les commandes.
$sql = "
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
    ORDER BY c.date_commande DESC, c.id DESC
";

// Exécution de la requête SQL.
$requete = $pdo->query($sql);

// Récupération des résultats dans un tableau PHP.
$commandes = $requete->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Gestion des commandes - Vite & Gourmand</title>

    <link rel="stylesheet" href="public/css/style.css?v=2">
</head>

<body>

<main class="page-formulaire">

<!--
    ÉTAPE 20.7 : AFFICHAGE DES COMMANDES

    Affiche les commandes enregistrées dans MariaDB.
    Si aucune commande n'existe, un message est affiché.
-->

<h2>Liste des commandes clients</h2>

<?php if (empty($commandes)): ?>

    <p>Aucune commande enregistrée pour le moment.</p>

<?php else: ?>

    <table class="tableau-commandes">

        <thead>
            <tr>
                <th>N°</th>
                <th>Menu</th>
                <th>Quantité</th>
                <th>Prix total</th>
                <th>Date de prestation</th>
                <th>Statut</th>
                <!-- Étape 20.9.1 : colonne permettant de modifier le statut -->
                <th>Modifier le statut</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($commandes as $commande): ?>

                <tr>
                    <!-- Numéro de commande -->
                    <td>
                        <?= (int) $commande['id'] ?>
                    </td>

                    <!-- Nom du menu : protection contre le HTML -->
                    <td>
                        <?= htmlspecialchars(
                            (string) $commande['nom_menu'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </td>

                    <!-- Quantité commandée -->
                    <td>
                        <?= (int) $commande['quantite'] ?>
                    </td>

                    <!-- Montant total -->
                    <td>
                        <?= number_format(
                            (float) $commande['prix_total'],
                            2,
                            ',',
                            ' '
                        ) ?> €
                    </td>

                    <!-- Date de prestation -->
                    <!--
                        ÉTAPE 20.8.1 : FORMATAGE DE LA DATE

                        Convertit la date enregistrée dans MariaDB
                        au format français : jour/mois/année.
                    -->
                    <td>
                        <?php
                        // Récupération de la date depuis MariaDB.
                        $datePrestation = (string) $commande['date_prestation'];

                        // Conversion de la date au format français.
                        $date = DateTimeImmutable::createFromFormat(
                            '!Y-m-d',
                            $datePrestation
                        );

                        // Affichage de la date si elle est valide.
                        if ($date !== false
                            && $date->format('Y-m-d') === $datePrestation) {
                            echo $date->format('d/m/Y');
                        } else {
                            echo 'Date indisponible';
                        }
                        ?>
                    </td>

                    <!-- Statut de la commande -->
                    <!--
                        ÉTAPE 20.8.2 : AFFICHAGE DU STATUT

                        Transforme le statut technique enregistré
                        dans MariaDB en texte lisible en français.
                    -->
                    <td>
                        <?php

                        // Récupération du statut de la commande.
                        $statut = (string) $commande['statut'];

                        // Correspondance entre les valeurs SQL
                        // et les intitulés affichés aux employés.
                        $statutsFrancais = [
                            'en_attente' => 'En attente',
                            'confirmee' => 'Confirmée',
                            'en_preparation' => 'En préparation',
                            'livree' => 'Livrée',
                            'annulee' => 'Annulée'
                        ];

                        // Utilise le libellé français s'il existe.
                        // Sinon, affiche le statut d'origine.
                        $statutAffiche = $statutsFrancais[$statut] ?? $statut;

                        // Protection contre l'injection de HTML.
                        echo htmlspecialchars(
                            $statutAffiche,
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </td>
                    <!--
                        ÉTAPE 20.9.1 : FORMULAIRE DE MODIFICATION DU STATUT

                        Chaque commande possède son propre formulaire.
                        Son identifiant permettra de retrouver la bonne
                        commande lors de l'enregistrement.
                    -->
                    <td>
                        <form method="post">

                    <!--
                        Jeton CSRF transmis avec le formulaire.
                        Il sera vérifié avant toute modification
                        dans la base de données.
                    -->
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars(
                                    $_SESSION['csrf_commandes'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <!-- Identifiant de la commande -->
                            <input
                                type="hidden"
                                name="commande_id"
                                value="<?= (int) $commande['id'] ?>"
                            >

                            <!-- Sélection du nouveau statut -->
                            <select name="nouveau_statut" required>

                                <option value="en_attente">En attente</option>
                                <option value="confirmee">Confirmée</option>
                                <option value="en_preparation">En préparation</option>
                                <option value="livree">Livrée</option>
                                <option value="annulee">Annulée</option>

                            </select>

                            <!-- Bouton de validation -->
                            <button type="submit" name="action" value="modifier_statut">
                                Enregistrer
                            </button>

                        </form>
                    </td>
                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

<?php endif; ?>
    <p>
        Cette page permettra de consulter
        et de suivre les commandes des clients.
    </p>

    <p>
        <a href="espace-employe.php" class="bouton">
            Retour à l'espace employé
        </a>
    </p>

</main>

</body>
</html>