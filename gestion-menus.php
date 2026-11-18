
<?php
/*
 * PROJET : Vite & Gourmand
 * PAGE : Gestion des menus
 *
 * Accessible aux employés et administrateurs.
 * Fonctionnalités :
 * - Afficher les menus
 * - Ajouter un nouveau menu
 */

require __DIR__ . '/config/auth.php';

// Vérification des autorisations.
exigerRole(['employe', 'administrateur']);

// Création d'un jeton de protection CSRF.
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

// Messages affichés dans la page.
$erreurs = [];
$message = '';

// Message après une création réussie.

// Messages de confirmation après redirection.
$messagesSucces = [
    'creation' => 'Le menu a été créé avec succès.',
    'etat' => 'L’état du menu a été modifié avec succès.'
];

$codeSucces = $_GET['succes'] ?? '';

$message = is_string($codeSucces)
    ? ($messagesSucces[$codeSucces] ?? '')
    : '';




    // ==========================================
    // TRAITEMENT DU FORMULAIRE
    // ==========================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Identification de l'action demandée.
        $action = $_POST['action'] ?? '';

        // ==========================================
        // SÉCURITÉ : VÉRIFICATION DU JETON CSRF
        // ==========================================

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

        // ==========================================
        // DÉSACTIVATION / RÉACTIVATION D'UN MENU
        // ==========================================

        if ($action === 'changer_etat') {

            // Vérification de l'identifiant transmis.
            $idMenu = filter_var(
                $_POST['menu_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($idMenu === false || $idMenu < 1) {
                $erreurs[] = 'Identifiant du menu invalide.';
            } else {

                // Modification de l'état en base de données.
                // Le menu n'est jamais supprimé.
                $requete = $pdo->prepare(
                    "UPDATE menus
                     SET actif = CASE
                         WHEN actif = 1 THEN 0
                         ELSE 1
                     END
                     WHERE id = :id"
                );

                $requete->execute([
                    'id' => $idMenu
                ]);

                if ($requete->rowCount() === 1) {

                    // Redirection après modification.
                    header(
                        'Location: gestion-menus.php?succes=etat'
                    );
                    exit;

                } else {
                    $erreurs[] = 'Menu introuvable.';
                }
            }

        // ==========================================
        // CRÉATION D'UN NOUVEAU MENU
        // ==========================================

        } elseif ($action === 'creer') {

            // Récupération des champs.
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $theme = trim($_POST['theme'] ?? '');
            $regime = trim($_POST['regime'] ?? '');
            $conditions = trim($_POST['conditions'] ?? '');

            // Validation des valeurs numériques.
            $personnes = filter_var(
                $_POST['personnes_minimum'] ?? null,
                FILTER_VALIDATE_INT
            );

            $prixTexte = trim($_POST['prix'] ?? '');

            $stock = filter_var(
                $_POST['stock'] ?? null,
                FILTER_VALIDATE_INT
            );

            // Vérification des champs obligatoires.
            if (
                $titre === ''
                || $description === ''
                || $theme === ''
                || $regime === ''
            ) {
                $erreurs[] =
                    'Veuillez remplir les champs obligatoires.';
            }

            // Vérification des longueurs.
            if (
                mb_strlen($titre) > 150
                || mb_strlen($theme) > 100
                || mb_strlen($regime) > 100
            ) {
                $erreurs[] = 'Un champ texte est trop long.';
            }

            // Vérification du nombre minimum.
            if ($personnes === false || $personnes < 1) {
                $erreurs[] =
                    'Le nombre minimum doit être supérieur à zéro.';
            }

            // Vérification du stock.
            if ($stock === false || $stock < 0) {
                $erreurs[] = 'Le stock ne peut pas être négatif.';
            }

            // Vérification du prix.
            if (
                !preg_match(
                    '/^\d{1,8}(\.\d{1,2})?$/',
                    $prixTexte
                )
            ) {
                $erreurs[] =
                    'Le prix doit être un nombre positif valide.';
            }

            // Insertion si toutes les données sont valides.
            if (empty($erreurs)) {

                try {
                    $requete = $pdo->prepare(
                        "INSERT INTO menus
                         (titre, description, theme, regime,
                          personnes_minimum, prix, stock,
                          conditions, actif)
                         VALUES
                         (:titre, :description, :theme, :regime,
                          :personnes, :prix, :stock,
                          :conditions, 1)"
                    );

                    $requete->execute([
                        'titre' => $titre,
                        'description' => $description,
                        'theme' => $theme,
                        'regime' => $regime,
                        'personnes' => $personnes,
                        'prix' => $prixTexte,
                        'stock' => $stock,
                        'conditions' => $conditions ?: null
                    ]);

                    // Redirection après création.
                    header(
                        'Location: gestion-menus.php?succes=creation'
                    );
                    exit;

                } catch (PDOException $exception) {
                    error_log($exception->getMessage());

                    $erreurs[] =
                        'Impossible de créer le menu pour le moment.';
                }
            }

        } else {

            // Refus des actions inconnues.
            $erreurs[] = 'Action non reconnue.';
        }

    } // Fin du traitement POST


// Récupération de tous les menus.
$requete = $pdo->query(
    "SELECT id, titre, theme, regime,
            personnes_minimum, prix, stock, actif
     FROM menus
     ORDER BY id DESC"
);

$menus = $requete->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Gestion des menus - Vite & Gourmand</title>

    <link rel="stylesheet" href="public/css/style.css">
</head>

<body>

<header>
    <nav class="navigation" aria-label="Navigation principale">
        <a class="logo" href="index.php">Vite & Gourmand</a>

        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="espace-employe.php">Espace employé</a></li>
            <li><a href="mon-compte.php">Mon compte</a></li>
        </ul>
    </nav>
</header>

<main class="page-formulaire gestion-menus">

    <h1>Gestion des menus</h1>

    <p>
        Créez et consultez les menus proposés
        par Vite & Gourmand.
    </p>

    <!-- Message de réussite -->
    <?php if ($message !== ''): ?>
        <div class="message-succes" role="status">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <!-- Messages d'erreur -->
    <?php if (!empty($erreurs)): ?>
        <div class="message-erreur" role="alert">
            <ul>
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?= e($erreur) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section>
        <h2>Ajouter un menu</h2>

 
        <!-- Formulaire de création d'un menu -->
        <form method="POST"
            action="gestion-menus.php"
            class="formulaire-client">

            <!-- Action demandée : création d'un menu -->
            <input
                type="hidden"
                name="action"
                value="creer"
            >

            <!-- Jeton de protection CSRF -->
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_menus']) ?>"
            >

            <!-- Champ : nom du menu -->
            <label for="titre">Nom du menu *</label>
            <input
                type="text"
                id="titre"
                name="titre"
                maxlength="150"
                required
            >

            <label for="description">Description *</label>
            <textarea
                id="description"
                name="description"
                required
            ></textarea>

            <label for="theme">Thème *</label>
            <input
                type="text"
                id="theme"
                name="theme"
                maxlength="100"
                placeholder="Exemple : Anniversaire"
                required
            >

            <label for="regime">Régime alimentaire *</label>
            <select id="regime" name="regime" required>
                <option value="">Choisir un régime</option>
                <option value="classique">Classique</option>
                <option value="vegetarien">Végétarien</option>
                <option value="vegan">Végan</option>
            </select>

            <label for="personnes_minimum">
                Nombre minimum de personnes *
            </label>
            <input
                type="number"
                id="personnes_minimum"
                name="personnes_minimum"
                min="1"
                step="1"
                required
            >

            <label for="prix">Prix du menu (€) *</label>
            <input
                type="number"
                id="prix"
                name="prix"
                min="0"
                max="99999999.99"
                step="0.01"
                required
            >

            <label for="stock">Stock disponible *</label>
            <input
                type="number"
                id="stock"
                name="stock"
                min="0"
                step="1"
                required
            >

            <label for="conditions">
                Conditions de commande
            </label>
            <textarea
                id="conditions"
                name="conditions"
            ></textarea>

            <button type="submit" class="bouton">
                Ajouter le menu
            </button>
        </form>
    </section>

    <section>
        <h2>Menus enregistrés</h2>

        <div class="tableau-defilant">
            <table class="tableau-employes">
                <thead >
                    <tr>
                        <th scope="col">Menu</th>
                        <th scope="col">Thème</th>
                        <th scope="col">Personnes min.</th>
                        <th scope="col">Prix</th>
                        <th scope="col">Stock</th>
                        <th scope="col">État</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>

                            <tbody>
                    <?php foreach ($menus as $menu): ?>
                        <tr>
                            <!-- Nom du menu -->
                            <td>
                                <?= e($menu['titre']) ?>
                            </td>

                            <!-- Thème -->
                            <td>
                                <?= e($menu['theme']) ?>
                            </td>

                            <!-- Nombre minimum de personnes -->
                            <td>
                                <?= (int) $menu['personnes_minimum'] ?>
                            </td>

                            <!-- Prix du menu -->
                            <td>
                                <?= number_format(
                                    (float) $menu['prix'],
                                    2,
                                    ',',
                                    ' '
                                ) ?> €
                            </td>

                            <!-- Stock -->
                            <td>
                                <?= (int) $menu['stock'] ?>
                            </td>

                            <!-- État actuel -->
                            <td>
                                <?php if ((int) $menu['actif'] === 1): ?>
                                    Actif
                                <?php else: ?>
                                    Inactif
                                <?php endif; ?>
                            </td>

                            <!-- Action de désactivation / réactivation -->
                            <td>
                                <form method="POST"
                                    action="gestion-menus.php">

                                    <!-- Jeton CSRF -->
                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= e($_SESSION['csrf_menus']) ?>"
                                    >

                                    <!-- Action demandée -->
                                    <input
                                        type="hidden"
                                        name="action"
                                        value="changer_etat"
                                    >

                                    <!-- Identifiant du menu -->
                                    <input
                                        type="hidden"
                                        name="menu_id"
                                        value="<?= (int) $menu['id'] ?>"
                                    >

                                    <!-- Bouton selon l'état du menu -->
                                    <button type="submit" class="bouton">
                                        <?php if ((int) $menu['actif'] === 1): ?>
                                            Désactiver
                                        <?php else: ?>
                                            Réactiver
                                        <?php endif; ?>

                                    </button>
                                </form>

                                <!-- Bouton permettant de modifier le menu -->
                                <a
                                    href="modifier-menu.php?id=<?= (int) $menu['id'] ?>"
                                    class="bouton"
                                >
                                    Modifier
                                </a>

                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </section>

</main>

<footer>
    <p>Vite & Gourmand — Bordeaux</p>
</footer>

</body>
</html>
