
<?php
/*
 * PROJET : Vite & Gourmand
 * PAGE : Inscription d'un client
 *
 * Fonctionnalités :
 * - Validation des champs
 * - Vérification de l'adresse e-mail
 * - Hachage sécurisé du mot de passe
 * - Enregistrement dans MariaDB
 */

require __DIR__ . '/config/database.php';

// Messages affichés à l'utilisateur.
$erreurs = [];
$succes = false;

// Valeurs conservées après une erreur de formulaire.
$nom = '';
$prenom = '';
$email = '';
$telephone = '';
$adresse = '';

// Traitement uniquement après envoi du formulaire.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage des champs textuels.
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');

    // Les mots de passe ne sont pas réaffichés.
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    // Vérification des champs obligatoires.
    if ($nom === '' || $prenom === '' ||
        $email === '' || $adresse === '') {
        $erreurs[] = 'Veuillez remplir tous les champs obligatoires.';
    }

    // Validation du format de l'adresse e-mail.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'Adresse e-mail invalide.';
    }

    // Vérification de la longueur du mot de passe.
    if (strlen($motDePasse) < 12) {
        $erreurs[] =
            'Le mot de passe doit contenir au moins 12 caractères.';
    }

    // Vérification de la confirmation.
    if ($motDePasse !== $confirmation) {
        $erreurs[] = 'Les mots de passe ne correspondent pas.';
    }

    // Limitation de la longueur des champs.
    if (mb_strlen($nom) > 100 ||
        mb_strlen($prenom) > 100 ||
        mb_strlen($email) > 255 ||
        mb_strlen($telephone) > 20) {
        $erreurs[] = 'Un ou plusieurs champs sont trop longs.';
    }

    // Exécution uniquement si les validations sont réussies.
    if (empty($erreurs)) {

        // Recherche d'un compte avec cette adresse e-mail.
        $requete = $pdo->prepare(
            'SELECT id FROM utilisateurs WHERE email = :email'
        );

        $requete->execute(['email' => $email]);

        if ($requete->fetch()) {
            $erreurs[] = 'Cette adresse e-mail est déjà utilisée.';
        } else {

            // Hachage du mot de passe.
            // PASSWORD_DEFAULT utilise un algorithme adapté
            // à la version de PHP installée.
            $hash = password_hash(
                $motDePasse,
                PASSWORD_DEFAULT
            );

            // Requête préparée pour éviter les injections SQL.
            $requete = $pdo->prepare(
                "INSERT INTO utilisateurs
                (nom, prenom, email, telephone,
                 adresse, mot_de_passe, role)
                VALUES
                (:nom, :prenom, :email, :telephone,
                 :adresse, :mot_de_passe, 'utilisateur')"
            );

            try {
                // Enregistrement du compte.
                $requete->execute([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'telephone' => $telephone ?: null,
                    'adresse' => $adresse,
                    'mot_de_passe' => $hash
                ]);

                $succes = true;

                // Réinitialisation après inscription réussie.
                $nom = $prenom = $email = '';
                $telephone = $adresse = '';

            } catch (PDOException $exception) {

                // Enregistrement technique de l'erreur.
                error_log($exception->getMessage());

                // Message générique pour le visiteur.
                $erreurs[] =
                    'Impossible de créer le compte pour le moment.';
            }
        }
    }
}

// Fonction d'échappement HTML.
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

    <!-- Adaptation aux appareils mobiles -->
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Inscription - Vite & Gourmand</title>

    <!-- Styles communs au projet -->
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
                <li><a href="connexion.php">Connexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="page-formulaire">

        <h1>Créer un compte client</h1>

        <p>
            Inscrivez-vous pour préparer vos futures commandes.
        </p>

        <!-- Affichage des erreurs de validation -->
        <?php if (!empty($erreurs)): ?>
            <div class="message-erreur" role="alert">
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?= e($erreur) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Message après inscription réussie -->
        <?php if ($succes): ?>
            <div class="message-succes" role="status">
                <p>Votre compte a été créé avec succès !</p>
                <p>
                    <a href="connexion.php">
                        Se connecter
                    </a>
                </p>
            </div>
        <?php endif; ?>

        <!-- Formulaire envoyé à la même page en POST -->
        <form method="POST" action="inscription.php"
              class="formulaire-client">

            <label for="nom">Nom *</label>
            <input type="text" id="nom" name="nom"
                   value="<?= e($nom) ?>"
                   maxlength="100" autocomplete="family-name"
                   required>

            <label for="prenom">Prénom *</label>
            <input type="text" id="prenom" name="prenom"
                   value="<?= e($prenom) ?>"
                   maxlength="100" autocomplete="given-name"
                   required>

            <label for="email">Adresse e-mail *</label>
            <input type="email" id="email" name="email"
                   value="<?= e($email) ?>"
                   maxlength="255" autocomplete="email"
                   required>

            <label for="telephone">Téléphone</label>
            <input type="tel" id="telephone" name="telephone"
                   value="<?= e($telephone) ?>"
                   maxlength="20" autocomplete="tel">

            <label for="adresse">Adresse postale *</label>
            <textarea id="adresse" name="adresse"
                      autocomplete="street-address"
                      required><?= e($adresse) ?></textarea>

            <label for="mot_de_passe">Mot de passe *</label>
            <input type="password" id="mot_de_passe"
                   name="mot_de_passe"
                   minlength="12"
                   autocomplete="new-password"
                   required>

            <p class="aide-champ">
                Utilisez au moins 12 caractères.
            </p>

            <label for="confirmation">
                Confirmer le mot de passe *
            </label>
            <input type="password" id="confirmation"
                   name="confirmation"
                   minlength="12"
                   autocomplete="new-password"
                   required>

            <button type="submit" class="bouton">
                Créer mon compte
            </button>
        </form>

        <p>
            Déjà inscrit ?
            <a href="connexion.php">Se connecter</a>
        </p>
    </main>

    <footer>
        <p>Vite & Gourmand — Bordeaux</p>
        <p>Horaires à compléter</p>
    </footer>

</body>
</html>
