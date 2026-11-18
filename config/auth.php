
<?php
/*
 * PROJET : Vite & Gourmand
 * FICHIER : config/auth.php
 *
 * Gestion de l'authentification et des autorisations.
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/database.php';

/*
 * Vérifie qu'un utilisateur est authentifié.
 * Sinon, il est redirigé vers la connexion.
 */
function exigerConnexion(): void
{
    global $pdo;

    if (!isset($_SESSION['utilisateur_id'])) {
        header('Location: connexion.php');
        exit;
    }

    // Vérification du compte dans la base à chaque accès.
    // Un compte désactivé perd ainsi son accès.
    $requete = $pdo->prepare(
        "SELECT id, nom, prenom, role, actif
         FROM utilisateurs
         WHERE id = :id
         LIMIT 1"
    );

    $requete->execute([
        'id' => $_SESSION['utilisateur_id']
    ]);

    $utilisateur = $requete->fetch();

    if (!$utilisateur || (int) $utilisateur['actif'] !== 1) {

        // Suppression des données de session.
        $_SESSION = [];

        // Renouvellement de l'identifiant de session.
        session_regenerate_id(true);

        header('Location: connexion.php');
        exit;
    }

    // Actualisation des informations depuis MariaDB.
    $_SESSION['utilisateur_role'] = $utilisateur['role'];
    $_SESSION['utilisateur_nom'] = $utilisateur['nom'];
    $_SESSION['utilisateur_prenom'] = $utilisateur['prenom'];
}

/*
 * Vérifie que l'utilisateur possède un rôle autorisé.
 *
 * Exemple :
 * exigerRole(['employe', 'administrateur']);
 */
function exigerRole(array $rolesAutorises): void
{
    // Vérification préalable de la connexion.
    exigerConnexion();

    $role = $_SESSION['utilisateur_role'] ?? '';

    // Refus d'accès si le rôle n'est pas autorisé.
    if (!in_array($role, $rolesAutorises, true)) {
        http_response_code(403);
        exit('Accès interdit : droits insuffisants.');
    }
}
