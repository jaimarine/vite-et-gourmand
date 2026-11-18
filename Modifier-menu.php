
<?php
/*
 * PROJET : Vite & Gourmand
 * PAGE : Modification d'un menu
 *
 * Accessible aux employés et administrateurs.
 */

// Chargement de l'authentification.
require __DIR__ . '/config/auth.php';

// Vérification des autorisations.
exigerRole(['employe', 'administrateur']);

 // ==========================================
 // SÉCURITÉ : PROTECTION CSRF
 // ==========================================

 // Création du jeton s'il n'existe pas.
 if (empty($_SESSION['csrf_menus'])) {
     $_SESSION['csrf_menus'] = bin2hex(random_bytes(32));
 }


// Fonction de sécurisation de l'affichage HTML.
function e(string $valeur): string
{
    return htmlspecialchars(
        $valeur,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Récupération de l'identifiant du menu.
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

// Récupération du menu dans MariaDB.
$requete = $pdo->prepare(
    "SELECT id, titre, description, theme, regime,
            personnes_minimum, prix, stock, conditions
     FROM menus
     WHERE id = :id"
);

$requete->execute([
    'id' => $idMenu
]);

$menu = $requete->fetch(PDO::FETCH_ASSOC);

// Vérification de l'existence du menu.
if (!$menu) {
    http_response_code(404);
    exit('Menu introuvable.');

}

 // ==========================================
 // TRAITEMENT DE LA MODIFICATION DU MENU
 // ==========================================

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification du jeton CSRF.
    $jeton = $_POST['csrf_token'] ?? '';

    if (
        !is_string($jeton)
        || !isset($_SESSION['csrf_menus'])
        || !is_string($_SESSION['csrf_menus'])
        || !hash_equals($_SESSION['csrf_menus'], $jeton)
    ) {
        http_response_code(403);
        exit('Requête non autorisée.');
    }

    // Vérification de l'identifiant du menu.
    $idEnvoye = filter_input(
        INPUT_POST,
        'menu_id',
        FILTER_VALIDATE_INT
    );

    if ($idEnvoye === false || $idEnvoye === null
        || $idEnvoye !== $idMenu) {
        http_response_code(400);
        exit('Identifiant du menu incorrect.');
    }

    // Récupération des champs du formulaire.
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $theme = trim($_POST['theme'] ?? '');
    $regime = trim($_POST['regime'] ?? '');
    $conditions = trim($_POST['conditions'] ?? '');

    $personnes = filter_var(
        $_POST['personnes_minimum'] ?? null,
        FILTER_VALIDATE_INT
    );

    $stock = filter_var(
        $_POST['stock'] ?? null,
        FILTER_VALIDATE_INT
    );

    $prixTexte = trim($_POST['prix'] ?? '');

    // Vérification des champs obligatoires.
    if (
        $titre === ''
        || $description === ''
        || $theme === ''
        || $regime === ''
    ) {
        $erreurs[] = 'Veuillez remplir les champs obligatoires.';
    }

    // Vérification des longueurs.
    if (
        mb_strlen($titre) > 150
        || mb_strlen($theme) > 100
        || mb_strlen($regime) > 100
    ) {
        $erreurs[] = 'Un champ texte est trop long.';
    }

    // Vérification des valeurs numériques.
    if ($personnes === false || $personnes < 1) {
        $erreurs[] = 'Le nombre minimum doit être supérieur à zéro.';
    }

    if ($stock === false || $stock < 0) {
        $erreurs[] = 'Le stock ne peut pas être négatif.';
    }

    if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $prixTexte)) {
        $erreurs[] = 'Le prix doit être un nombre positif valide.';
    }

    // Enregistrement si aucune erreur n'est détectée.
    if (empty($erreurs)) {

        try {
            $requete = $pdo->prepare(
                "UPDATE menus
                 SET titre = :titre,
                     description = :description,
                     theme = :theme,
                     regime = :regime,
                     personnes_minimum = :personnes,
                     prix = :prix,
                     stock = :stock,
                     conditions = :conditions
                 WHERE id = :id"
            );

            $requete->execute([
                'titre' => $titre,
                'description' => $description,
                'theme' => $theme,
                'regime' => $regime,
                'personnes' => $personnes,
                'prix' => $prixTexte,
                'stock' => $stock,
                'conditions' => $conditions ?: null,
                'id' => $idMenu
            ]);

            // Retour à la gestion après modification.
            header('Location: gestion-menus.php?succes=modification');
            exit;

        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $erreurs[] = 'Impossible de modifier le menu.';
        }
    }

    // Conservation des valeurs en cas d'erreur.
    $menu['titre'] = $titre;
    $menu['description'] = $description;
    $menu['theme'] = $theme;
    $menu['regime'] = $regime;
    $menu['personnes_minimum'] = $personnes ?: 0;
    $menu['prix'] = $prixTexte;
    $menu['stock'] = $stock === false ? 0 : $stock;
    $menu['conditions'] = $conditions;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Modifier un menu - Vite & Gourmand</title>

    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<main>
    <h1>Modifier un menu</h1>

    <p>
        Vous modifiez le menu :
        <strong><?= e($menu['titre']) ?></strong>
    </p>

    <!-- Formulaire prérempli avec les données du menu -->
    <!-- L'enregistrement sera ajouté à l'étape 17B.3 -->

<!-- Formulaire de modification d'un menu -->
<form
    method="POST"
    action="Modifier-menu.php?id=<?= (int) $menu['id'] ?>"
    class="formulaire-modification"
>
        <label for="titre">Titre du menu</label>
        <input
            type="text"
            id="titre"
            name="titre"
            value="<?= e($menu['titre']) ?>"
            required
        >

        <label for="description">Description</label>
        <textarea
            id="description"
            name="description"
            required
        ><?= e($menu['description']) ?></textarea>

        <label for="theme">Thème</label>
        <input
            type="text"
            id="theme"
            name="theme"
            value="<?= e($menu['theme']) ?>"
            required
        >

        <label for="regime">Régime alimentaire</label>
        <input
            type="text"
            id="regime"
            name="regime"
            value="<?= e($menu['regime']) ?>"
            required
        >

        <label for="personnes">Nombre minimum de personnes</label>
        <input
            type="number"
            id="personnes"
            name="personnes_minimum"
            min="1"
            value="<?= (int) $menu['personnes_minimum'] ?>"
            required
        >

        <label for="prix">Prix (€)</label>
        <input
            type="number"
            id="prix"
            name="prix"
            min="0"
            step="0.01"
            value="<?= e((string) $menu['prix']) ?>"
            required
        >

        <label for="stock">Stock</label>
        <input
            type="number"
            id="stock"
            name="stock"
            min="0"
            value="<?= (int) $menu['stock'] ?>"
            required
        >

        <label for="conditions">Conditions particulières</label>
        <textarea
            id="conditions"
            name="conditions"
        ><?= e((string) ($menu['conditions'] ?? '')) ?></textarea>

    </form>

<!-- Enregistrement des modifications -->
<button type="submit" class="bouton">
    Enregistrer les modifications
</button>

    
<!-- Protection contre les requêtes non autorisées -->
<input
    type="hidden"
    name="csrf_token"
    value="<?= e($_SESSION['csrf_menus']) ?>"
>

<!-- Identifiant du menu à modifier -->
<input
    type="hidden"
    name="menu_id"
    value="<?= (int) $menu['id'] ?>"
>



    <p>
        <a href="gestion-menus.php">
            Retour à la gestion des menus
        </a>
    </p>
</main>

</body>
</html>
