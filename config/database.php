
<?php

/*
 * ÉTAPE 21.4 : CONFIGURATION DE LA BASE DE DONNÉES
 *
 * Les variables d'environnement permettent
 * de configurer la connexion en production.
 *
 * En local, les valeurs XAMPP sont conservées
 * pour ne pas perturber le développement.
 */

$host = getenv('DB_HOST') ?: 'localhost';

$dbname = getenv('DB_NAME') ?: 'vite_et_gourmand';

$username = getenv('DB_USER') ?: 'root';

$password = getenv('DB_PASSWORD');
if ($password === false) {
    $password = '';
}


try {
    // Création de la connexion PDO.
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            // Les erreurs SQL déclenchent des exceptions.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            // Les résultats sont retournés en tableaux associatifs.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // Utilisation de requêtes préparées natives.
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    // Enregistrer le détail technique dans les journaux.
    error_log($e->getMessage());

    // Ne pas afficher les identifiants ou détails SQL.
    http_response_code(500);
    exit('Erreur de connexion à la base de données.');
}
