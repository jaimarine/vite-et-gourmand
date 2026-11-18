
<?php
/*
 * PROJET : Vite & Gourmand
 * PAGE : Gestion des employés
 *
 * Réservée aux administrateurs.
 * Fonctionnalités :
 * - Création d'un compte employé
 * - Liste des employés
 * - Désactivation et réactivation
 * - Protection contre les requêtes CSRF
 */

require __DIR__ . '/config/auth.php';

// Vérification des droits côté serveur.
exigerRole(['administrateur']);

// Création d'un jeton CSRF propre à la session.
if (empty($_SESSION['csrf_employes'])) {
    $_SESSION['csrf_employes'] = bin2hex(random_bytes(32));
}

// Fonction de sécurisation de l'affichage.
function e(string $valeur): string
{
    return htmlspecialchars(
        $valeur,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Messages de retour.
$erreurs = [];
$message = '';

// Récupération du message après une redirection.
if (isset($_GET['succes'])) {
    $messages = [
        'creation' => 'Compte employé créé avec succès.',
        'etat' => 'État du compte modifié avec succès.'
    ];

    $message = $messages[$_GET['succes']] ?? '';
}

// Traitement des formulaires.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification du jeton CSRF.
    $jeton = $_POST['csrf_token'] ?? '';

    if (
        !is_string($jeton)
        || !hash_equals(
            $_SESSION['csrf_employes'],
            $jeton
        )
    ) {
        http_response_code(403);
        exit('Requête non autorisée.');
    }

    $action = $_POST['action'] ?? '';

    // =========================================
    // ACTION : CRÉATION D'UN EMPLOYÉ
    // =========================================
    if ($action === 'creer') {

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        $confirmation = $_POST['confirmation'] ?? '';

        // Contrôle des informations obligatoires.
        if (
            $nom === ''
            || $prenom === ''
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $erreurs[] = 'Informations obligatoires invalides.';
        }

        // Contrôle des longueurs.
        if (
            mb_strlen($nom) > 100
            || mb_strlen($prenom) > 100
            || strlen($email) > 255
            || mb_strlen($telephone) > 20
        ) {
            $erreurs[] = 'Un champ dépasse la longueur autorisée.';
        }

        // Vérification de la robustesse du mot de passe.
        $motDePasseValide =
            strlen($motDePasse) >= 12
            && preg_match('/[a-z]/', $motDePasse)
            && preg_match('/[A-Z]/', $motDePasse)
            && preg_match('/[0-9]/', $motDePasse)
            && preg_match('/[^a-zA-Z0-9]/', $motDePasse);

        if (!$motDePasseValide) {
            $erreurs[] =
                'Le mot de passe doit contenir au moins '
                . '12 caractères, une majuscule, une minuscule, '
                . 'un chiffre et un caractère spécial.';
        }

        if ($motDePasse !== $confirmation) {
            $erreurs[] = 'Les mots de passe ne correspondent pas.';
        }

        if (empty($erreurs)) {

            // Vérification d'une adresse e-mail existante.
            $requete = $pdo->prepare(
                "SELECT id FROM utilisateurs
                 WHERE email = :email"
            );

            $requete->execute(['email' => $email]);

            if ($requete->fetch()) {
                $erreurs[] = 'Cette adresse e-mail est déjà utilisée.';
            } else {

                // Hachage sécurisé du mot de passe.
                $hash = password_hash(
                    $motDePasse,
                    PASSWORD_DEFAULT
                );

                try {
                    // Le rôle employé est imposé côté serveur.
                    $requete = $pdo->prepare(
                        "INSERT INTO utilisateurs
                         (nom, prenom, email, telephone,
                          mot_de_passe, role, actif)
                         VALUES
                         (:nom, :prenom, :email, :telephone,
                          :mot_de_passe, 'employe', 1)"
                    );

                    $requete->execute([
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'email' => $email,
                        'telephone' => $telephone ?: null,
                        'mot_de_passe' => $hash
                    ]);

                    // Redirection pour éviter une double création
                    // lors d'un rafraîchissement de la page.
                    header(
                        'Location: gestion-employes.php?succes=creation'
                    );
                    exit;

                } catch (PDOException $exception) {
                    error_log($exception->getMessage());
                    $erreurs[] =
                        'Impossible de créer le compte.';
                }
            }
        }
    }

    // =========================================
    // ACTION : MODIFICATION DE L'ÉTAT
    // =========================================
    elseif ($action === 'changer_etat') {

        // Validation de l'identifiant.
        $idEmploye = filter_var(
            $_POST['employe_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$idEmploye || $idEmploye < 1) {
            $erreurs[] = 'Identifiant employé invalide.';
        } else {

            // La requête ne peut modifier que les employés.
            // Elle ne peut pas désactiver un administrateur.
            $requete = $pdo->prepare(
                "UPDATE utilisateurs
                 SET actif = CASE
                     WHEN actif = 1 THEN 0
                     ELSE 1
                 END
                 WHERE id = :id
                   AND role = 'employe'"
            );

            $requete->execute(['id' => $idEmploye]);

            if ($requete->rowCount() === 1) {
                header(
                    'Location: gestion-employes.php?succes=etat'
                );
                exit;
            }

            $erreurs[] = 'Employé introuvable.';
        }
    } else {
        $erreurs[] = 'Action inconnue.';
    }
}

// =========================================
// RÉCUPÉRATION DES EMPLOYÉS
// =========================================

// Seuls les comptes employés sont affichés.
$requete = $pdo->query(
    "SELECT id, nom, prenom, email, actif, date_creation
     FROM utilisateurs
     WHERE role = 'employe'
     ORDER BY date_creation DESC, id DESC"
);

$employes = $requete->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Gestion des employés - Vite & Gourmand</title>

    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<header>
    <nav class="navigation" aria-label="Navigation principale">
        <a class="logo" href="index.php">Vite & Gourmand</a>

        <ul>
            <li><a href="administration.php">Administration</a></li>
            <li><a href="mon-compte.php">Mon compte</a></li>
        </ul>
    </nav>
</header>

<main class="page-formulaire gestion-employes">

    <h1>Gestion des employés</h1>

    <p>
        Cet espace permet de créer et gérer
        les comptes des employés du traiteur.
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
        <h2>Créer un compte employé</h2>

        <form method="POST"
              action="gestion-employes.php"
              class="formulaire-client">

            <!-- Jeton de protection CSRF -->
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_employes']) ?>"
            >

            <!-- Action demandée au serveur -->
            <input type="hidden" name="action" value="creer">

            <label for="nom">Nom *</label>
            <input
                type="text"
                id="nom"
                name="nom"
                maxlength="100"
                required
            >

            <label for="prenom">Prénom *</label>
            <input
                type="text"
                id="prenom"
                name="prenom"
                maxlength="100"
                required
            >

            <label for="email">Adresse e-mail *</label>
            <input
                type="email"
                id="email"
                name="email"
                maxlength="255"
                required
            >

            <label for="telephone">Téléphone</label>
            <input
                type="tel"
                id="telephone"
                name="telephone"
                maxlength="20"
            >

            <label for="mot_de_passe">
                Mot de passe temporaire *
            </label>
            <input
                type="password"
                id="mot_de_passe"
                name="mot_de_passe"
                minlength="12"
                autocomplete="new-password"
                required
            >

            <p class="aide-champ">
                12 caractères minimum, avec majuscule,
                minuscule, chiffre et caractère spécial.
            </p>

            <label for="confirmation">
                Confirmer le mot de passe *
            </label>
            <input
                type="password"
                id="confirmation"
                name="confirmation"
                minlength="12"
                autocomplete="new-password"
                required
            >

            <button type="submit" class="bouton">
                Créer l'employé
            </button>
        </form>
    </section>

    <section>
        <h2>Liste des employés</h2>

        <?php if (empty($employes)): ?>

            <p>Aucun employé enregistré.</p>

        <?php else: ?>

            <div class="tableau-defilant">
                <table class="tableau-employes">
                    <thead>
                        <tr>
                            <th scope="col">Nom</th>
                            <th scope="col">E-mail</th>
                            <th scope="col">État</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($employes as $employe): ?>
                            <tr>
                                <td>
                                    <?= e($employe['prenom']) ?>
                                    <?= e($employe['nom']) ?>
                                </td>

                                <td>
                                    <?= e($employe['email']) ?>
                                </td>

                                <td>
                                    <?php if ((int) $employe['actif'] === 1): ?>
                                        Actif
                                    <?php else: ?>
                                        Désactivé
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <form method="POST"
                                          action="gestion-employes.php">

                                        <!-- Protection CSRF -->
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e($_SESSION['csrf_employes']) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="changer_etat"
                                        >

                                        <input
                                            type="hidden"
                                            name="employe_id"
                                            value="<?= (int) $employe['id'] ?>"
                                        >

                                        <button type="submit"
                                                class="bouton">
                                            <?php if ((int) $employe['actif'] === 1): ?>
                                                Désactiver
                                            <?php else: ?>
                                                Réactiver
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </section>

</main>

<footer>
    <p>Vite & Gourmand — Bordeaux</p>
</footer>

</body>
</html>
