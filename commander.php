

<?php
/*
 * PROJET : Vite & Gourmand
 * FICHIER : commander.php
 *
 * Gestion de la commande d'un menu par un client.
 */

// Chargement de l'authentification et de MariaDB.
require_once __DIR__ . '/config/auth.php';

// Seuls les clients connectés peuvent commander.
exigerRole(['utilisateur']);

// Création du jeton de sécurité CSRF.
if (empty($_SESSION['csrf_commande'])) {
    $_SESSION['csrf_commande'] = bin2hex(random_bytes(32));
}

/*
 * ÉTAPE 1 : RÉCUPÉRATION DU MENU
 */

// Récupération de l'identifiant depuis l'URL.
$idMenu = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

// Vérification de l'identifiant.
if ($idMenu === false || $idMenu === null || $idMenu < 1) {
    http_response_code(400);
    exit('Identifiant du menu invalide.');
}

// Recherche du menu actif dans MariaDB.
$requete = $pdo->prepare(
    "SELECT id, titre, description,
            personnes_minimum, prix, stock
     FROM menus
     WHERE id = :id AND actif = 1
     LIMIT 1"
);

$requete->execute([
    'id' => $idMenu
]);

$menu = $requete->fetch();

// Vérification de l'existence du menu.
if (!$menu) {
    http_response_code(404);
    exit('Menu introuvable ou indisponible.');
}

/*
 * ÉTAPE 2 : TRAITEMENT DU FORMULAIRE
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /*
     * Vérification du jeton CSRF.
     */
    $jeton = $_POST['csrf_token'] ?? '';

    if (
        !is_string($jeton) ||
        !hash_equals($_SESSION['csrf_commande'], $jeton)
    ) {
        http_response_code(403);
        exit('Requête non autorisée.');
    }

    /*
     * Validation de la quantité commandée.
     */
    $quantite = filter_var(
        $_POST['quantite'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($quantite === false || $quantite === null || $quantite < 1) {
        http_response_code(400);
        exit('La quantité doit être supérieure à zéro.');
    }

    /*
     * Vérification du stock disponible.
     */
    if ($quantite > (int) $menu['stock']) {
        http_response_code(400);
        exit('Stock insuffisant pour cette commande.');
    }

    /*
     * Vérification du nombre minimum de personnes.
     */
    $nombrePersonnes = filter_var(
        $_POST['nombre_personnes'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        $nombrePersonnes === false ||
        $nombrePersonnes === null ||
        $nombrePersonnes < (int) $menu['personnes_minimum']
    ) {
        http_response_code(400);
        exit('Le nombre minimum de personnes n’est pas respecté.');
    }


    /*
     * ÉTAPE 18.7.9.2 : VALIDATION DE L'HEURE
     *
     * Vérifie que l'heure de livraison est
     * renseignée et respecte le format HH:MM.
     */

    // Récupération de l'heure envoyée.
    $heureLivraison = $_POST['heure_livraison'] ?? '';

    // Vérification du type et du format.
    if (
        !is_string($heureLivraison) ||
        !preg_match(
            '/^([01][0-9]|2[0-3]):[0-5][0-9]$/',
            $heureLivraison
        )
    ) {
        http_response_code(400);
        exit('Heure de livraison invalide.');
    }


    /*
     * ÉTAPE 18.7.9.4 : VALIDATION DU TÉLÉPHONE
     *
     * Vérifie que le numéro de téléphone est
     * composé de 10 chiffres et commence par 0.
     */

    // Récupération du téléphone envoyé par le formulaire.
    $telephoneContact = $_POST['telephone_contact'] ?? '';

    // Vérification du type de donnée.
    if (!is_string($telephoneContact)) {
        http_response_code(400);
        exit('Numéro de téléphone invalide.');
    }

    // Suppression des espaces au début et à la fin.
    $telephoneContact = trim($telephoneContact);

    // Vérification du format français à 10 chiffres.
    if (!preg_match('/^0[1-9][0-9]{8}$/', $telephoneContact)) {
        http_response_code(400);
        exit('Le numéro de téléphone doit contenir 10 chiffres et commencer par 0.');
    }


    /*
     * ÉTAPE 18.7.10 : CALCUL DU PRIX TOTAL
     *
     * Le prix unitaire provient directement
     * de la base de données MariaDB.
     *
     * La quantité a déjà été vérifiée.
     */

    // Récupération du prix du menu depuis MariaDB.
    $prixUnitaire = (float) $menu['prix'];

    // Calcul du prix total de la commande.
    $prixTotal = round($prixUnitaire * $quantite, 2);

    /*
     * ÉTAPE 18.7.11.2 : ENREGISTREMENT DE LA COMMANDE
     *
     * Une transaction permet d'enregistrer la commande
     * et de diminuer le stock de manière sécurisée.
     */
  /*
         * VALIDATION DE LA DATE DE PRESTATION
         *
   
    /*
     * ÉTAPE 18.7.11.10 : VALIDATION DE LA DATE
     *
     * Vérifie que la date de prestation est valide
     * et qu'elle n'est pas située dans le passé.
     */

    // Récupération de la date envoyée par le formulaire.
    $datePrestation = $_POST['date_prestation'] ?? '';

    // Récupération de l'adresse pour sa validation suivante.
    $adresseLivraison = $_POST['adresse_livraison'] ?? '';

    // Vérification du type de donnée reçu.
    if (!is_string($datePrestation)) {
        http_response_code(400);
        exit('Date de prestation invalide.');
    }

    // Conversion de la date au format YYYY-MM-DD.
    $date = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $datePrestation,
        new DateTimeZone('Europe/Paris')
    );

    // Vérification du format et de l'existence de la date.
    if (
        $date === false ||
        $date->format('Y-m-d') !== $datePrestation
    ) {
        http_response_code(400);
        exit('Date de prestation invalide.');
    }

    // Récupération de la date actuelle en France.
    $aujourdhui = new DateTimeImmutable(
        'today',
        new DateTimeZone('Europe/Paris')
    );

    // Refus d'une date passée.
    if ($date < $aujourdhui) {
        http_response_code(400);
        exit('La date de prestation ne peut pas être passée.');
    }



    /*
     * ÉTAPE 18.7.11.9 : VALIDATION DE L'ADRESSE
     *
     * Vérifie que l'adresse de livraison est valide
     * avant l'enregistrement dans MariaDB.
     */

    // Vérification du type de donnée reçu.
    if (!is_string($adresseLivraison)) {
        http_response_code(400);
        exit('Adresse de livraison invalide.');
    }

    // Suppression des espaces inutiles.
    $adresseLivraison = trim($adresseLivraison);

    // Vérification de la longueur de l'adresse.
    $longueurAdresse = mb_strlen($adresseLivraison, 'UTF-8');

    if ($longueurAdresse < 10 || $longueurAdresse > 255) {
        http_response_code(400);
        exit(
            'L’adresse de livraison doit contenir '
            . 'entre 10 et 255 caractères.'
        );
    }


    try {
        // Démarrage de la transaction SQL.
        $pdo->beginTransaction();

        /*
         * Réservation du stock.
         *
         * La quantité est déduite uniquement si
         * le menu est actif et que le stock est suffisant.
         */
        $requeteStock = $pdo->prepare(
            "UPDATE menus
             SET stock = stock - :quantite
             WHERE id = :menu_id
               AND actif = 1
               AND stock >= :stock_minimum"
        );

        $requeteStock->execute([
            'quantite' => $quantite,
            'menu_id' => $menu['id'],
            'stock_minimum' => $quantite
        ]);

        // Vérification que le stock a bien été réservé.
        if ($requeteStock->rowCount() !== 1) {
            $pdo->rollBack();
            http_response_code(400);
            exit('Stock insuffisant ou menu indisponible.');
        }

        /*
         * Enregistrement de la commande dans MariaDB.
         *
         * Les données sont transmises avec une requête
         * préparée pour éviter les injections SQL.
         */
        $requeteCommande = $pdo->prepare(
            "INSERT INTO commandes (
                utilisateur_id,
                menu_id,
                quantite,
                prix_total,
                statut,
                nombre_personnes,
                date_prestation,
                heure_livraison,
                adresse_livraison,
                telephone_contact
            ) VALUES (
                :utilisateur_id,
                :menu_id,
                :quantite,
                :prix_total,
                'en_attente',
                :nombre_personnes,
                :date_prestation,
                :heure_livraison,
                :adresse_livraison,
                :telephone_contact
            )"
        );
        /*
         * Transmission des données à la requête préparée.
         */
        $requeteCommande->execute([
            'utilisateur_id' => $_SESSION['utilisateur_id'],
            'menu_id' => $menu['id'],
            'quantite' => $quantite,
            'prix_total' => $prixTotal,
            'nombre_personnes' => $nombrePersonnes,
            'date_prestation' => $datePrestation,
            'heure_livraison' => $heureLivraison,
            'adresse_livraison' => $adresseLivraison,
            'telephone_contact' => $telephoneContact
        ]);

        /*
         * ÉTAPE 18.7.11.12 : VALIDATION DE LA TRANSACTION
         *
         * Confirme l'enregistrement de la commande
         * et la diminution du stock dans MariaDB.
         */

        // Récupération du numéro de la nouvelle commande.
        $numeroCommande = $pdo->lastInsertId();

        // Validation définitive des modifications SQL.
        $pdo->commit();



        // Affichage du message de confirmation.
        echo 'Commande enregistrée avec succès ! Numéro : '
            . htmlspecialchars(
                (string) $numeroCommande,
                ENT_QUOTES,
                'UTF-8'
            );



    } catch (PDOException $erreur) {

        // Annulation des modifications en cas d'erreur.
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Enregistrement technique de l'erreur.
        error_log($erreur->getMessage());

        // Message destiné au client.
        http_response_code(500);
        exit(
            'Une erreur est survenue lors de '
            . 'l’enregistrement de la commande.'
        );
    }


}

/*
 * ÉTAPE 3 : SÉCURISATION DE L'AFFICHAGE HTML
 */

// Protection contre l'injection de code HTML.
function e(string $valeur): string
{
    return htmlspecialchars(
        $valeur,
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
        <link rel="stylesheet" href="public/css/Style.css">

    <title>Commander un menu - Vite & Gourmand</title>
</head>
<body class="page-commande">
    <main class="conteneur-commande">

    <h1>Commander un menu</h1>

    <h2><?= e($menu['titre']) ?></h2>

    <p>
        <?= nl2br(e($menu['description'])) ?>
    </p>

    <p>
        <strong>Prix du menu :</strong>
        <?= number_format((float) $menu['prix'], 2, ',', ' ') ?> €
    </p>

    <p>
        <strong>Nombre minimum de personnes :</strong>
        <?= (int) $menu['personnes_minimum'] ?>
    </p>

    <p>
        <strong>Stock disponible :</strong>
        <?= (int) $menu['stock'] ?>
    </p>

    <p>

<!-- Formulaire de commande du menu sélectionné -->
<h2>Informations de commande</h2>

<form action="" method="post" class="formulaire-commande">
    <!-- Jeton CSRF envoyé avec le formulaire -->
<input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars($_SESSION['csrf_commande'], ENT_QUOTES, 'UTF-8') ?>"
>

    <!-- Nombre de menus commandés -->
    <div>
        <label for="quantite">
            Quantité de menus :
        </label>

        <input
            type="number"
            id="quantite"
            name="quantite"
            min="1"
            max="<?= (int) $menu['stock'] ?>"
            value="1"
            required
        >
    </div>

    <!-- Nombre de personnes -->
    <div>
        <label for="nombre_personnes">
            Nombre de personnes :
        </label>

        <input
            type="number"
            id="nombre_personnes"
            name="nombre_personnes"
            min="<?= (int) $menu['personnes_minimum'] ?>"
            required
        >
    </div>

    <!-- Date de la prestation -->
    <div>
        <label for="date_prestation">
            Date de prestation :
        </label>

        <input
            type="date"
            id="date_prestation"
            name="date_prestation"
            min="<?= date('Y-m-d') ?>"
            required
        >
    </div>

    <!-- Heure de livraison -->
    <div>
        <label for="heure_livraison">
            Heure de livraison :
        </label>

        <input
            type="time"
            id="heure_livraison"
            name="heure_livraison"
            required
        >
    </div>

    <!-- Adresse de livraison -->
    <div>
        <label for="adresse_livraison">
            Adresse de livraison :
        </label>

        <textarea
            id="adresse_livraison"
            name="adresse_livraison"
            rows="3"
            required
        ></textarea>
    </div>

    <!-- Téléphone de contact -->
    <div>
        <label for="telephone_contact">
            Téléphone :
        </label>

        <input
            type="tel"
            id="telephone_contact"
            name="telephone_contact"
            maxlength="20"
            required
        >
    </div>

    <!-- Bouton d'envoi -->
    <div>
        <button type="submit" class="bouton-commande">
            Valider la commande
        </button>
    </div>

</form>

    </p>

    <a href="menus.php">Retour aux menus</a>
</main>
</body>
</html>
