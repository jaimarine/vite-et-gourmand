
<?php
/*
 * PROJET : Vite & Gourmand
 * PAGE : Connexion des utilisateurs
 */

require __DIR__ . '/config/session.php';
require __DIR__ . '/config/database.php';

// Si l'utilisateur est déjà connecté,
// il est redirigé vers son espace personnel.
if (isset($_SESSION['utilisateur_id'])) {
    header('Location: mon-compte.php');
    exit;
}

// Variables utilisées par le formulaire.
$erreur = '';
$email = '';

// Traitement de la connexion.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    // Vérification des informations saisies.
    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL)
        || $motDePasse === ''
    ) {
        $erreur = 'Identifiants incorrects.';
    } else {

        // Recherche de l'utilisateur dans la base.
        $requete = $pdo->prepare(
            "SELECT id, nom, prenom, email,
                    mot_de_passe, role, actif
             FROM utilisateurs
             WHERE email = :email
             LIMIT 1"
        );

        $requete->execute(['email' => $email]);

        $utilisateur = $requete->fetch();

        // Vérification du mot de passe haché
        // et de l'état du compte.
        if (
            $utilisateur
            && (int) $utilisateur['actif'] === 1
            && password_verify(
                $motDePasse,
                $utilisateur['mot_de_passe']
            )
        ) {

            // Création d'un nouvel identifiant de session
            // pour limiter la fixation de session.
            session_regenerate_id(true);

            // Enregistrement des informations utiles.
            $_SESSION['utilisateur_id'] =
                (int) $utilisateur['id'];

            $_SESSION['utilisateur_nom'] =
                $utilisateur['nom'];

            $_SESSION['utilisateur_prenom'] =
                $utilisateur['prenom'];

            $_SESSION['utilisateur_role'] =
                $utilisateur['role'];

            // Redirection vers l'espace personnel.
            header('Location: mon-compte.php');
            exit;

        } else {

            // Message volontairement générique.
            $erreur = 'Identifiants incorrects.';
        }
    }
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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Connexion - Vite & Gourmand</title>

    <link rel="stylesheet" href="public/css/style.css">
</head>

<body>

<header>
    <nav class="navigation" aria-label="Navigation principale">
        <a class="logo" href="index.php">Vite & Gourmand</a>

        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="menus.php">Nos menus</a></li>
            <li><a href="inscription.php">Inscription</a></li>
        </ul>
    </nav>
</header>

<main class="page-formulaire">

    <h1>Connexion</h1>

    <p>Connectez-vous à votre espace personnel.</p>

    <!-- Affichage d'une erreur éventuelle -->
    <?php if ($erreur !== ''): ?>
        <div class="message-erreur" role="alert">
            <?= e($erreur) ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire de connexion -->
    <form method="POST"
          action="connexion.php"
          class="formulaire-client">

        <label for="email">
            Adresse e-mail
        </label>

        <input
            type="email"
            id="email"
            name="email"
            value="<?= e($email) ?>"
            autocomplete="email"
            required
        >

        <label for="mot_de_passe">
            Mot de passe
        </label>

        <input
            type="password"
            id="mot_de_passe"
            name="mot_de_passe"
            autocomplete="current-password"
            required
        >

        <button type="submit" class="bouton">
            Se connecter
        </button>

    </form>

    <p>
        Pas encore de compte ?
        <a href="inscription.php">Créer un compte</a>
    </p>

</main>

<footer>
    <p>Vite & Gourmand — Bordeaux</p>
</footer>

</body>
</html>
