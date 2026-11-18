

\# Vite \& Gourmand



\## Présentation



Vite \& Gourmand est une application web de gestion de commandes de menus pour une entreprise de traiteur.



Ce projet est réalisé dans le cadre de l'Évaluation en Cours de Formation (ECF) du titre professionnel Développeur Web et Web Mobile (DWWM) chez Studi.



\## Technologies utilisées



\- HTML5

\- CSS3

\- JavaScript

\- PHP

\- MariaDB

\- PDO pour les interactions avec la base de données

\- Git et GitHub pour le versionnement

\- XAMPP pour l'environnement local



\## Fonctionnalités développées



\### Visiteur

\- Consulter le catalogue des menus

\- Consulter les détails d'un menu

\- Créer un compte

\- Se connecter



\### Client

\- Accéder à son espace personnel

\- Commander un menu

\- Consulter son historique de commandes

\- Se déconnecter



\### Employé

\- Accéder à son espace employé

\- Gérer les menus

\- Consulter les commandes

\- Modifier le statut des commandes



\### Administrateur

\- Accéder à son espace administrateur

\- Gérer les comptes employés

\- Accéder aux fonctionnalités de gestion



\## Installation en local



\### Prérequis



\- PHP

\- MariaDB

\- Apache

\- XAMPP ou environnement équivalent



\### Étapes



1\. Cloner le dépôt :



&#x20;  git clone https://github.com/jaimarine/vite-et-gourmand.git



2\. Placer le projet dans le dossier `htdocs` de XAMPP.



3\. Démarrer Apache et MariaDB depuis XAMPP.



4\. Créer une base de données nommée `vite\_et\_gourmand`.



5\. Importer les scripts SQL présents dans `database/` :

&#x20;  - `schema.sql`

&#x20;  - `migrations/001\_ajout\_actif\_menus.sql`

&#x20;  - `donnees.sql`



6\. Configurer la connexion à la base de données avec les variables d'environnement :

&#x20;  - `DB\_HOST`

&#x20;  - `DB\_NAME`

&#x20;  - `DB\_USER`

&#x20;  - `DB\_PASSWORD`



&#x20;  En local, la configuration utilise par défaut :

&#x20;  - Hôte : localhost

&#x20;  - Base : vite\_et\_gourmand

&#x20;  - Utilisateur : root

&#x20;  - Mot de passe : vide



7\. Ouvrir l'application :



&#x20;  http://localhost/vite-et-gourmand/



\## Organisation du projet



\- `config/` : connexion à la base de données, sessions et authentification

\- `database/` : structure SQL, données initiales et migrations

\- `public/css/` : feuilles de style

\- `public/Js/` : scripts JavaScript

\- Fichiers PHP à la racine : pages et traitements de l'application



\## Sécurité



L'application utilise notamment :



\- Le hachage des mots de passe

\- La vérification des rôles utilisateurs

\- Des requêtes SQL préparées

\- Des protections CSRF sur les formulaires concernés

\- Des sessions PHP pour l'authentification



\## Gestion des versions



Le projet utilise deux branches Git :



\- `main` : branche principale

\- `develop` : branche de développement



\## État du projet



Application en cours de finalisation dans le cadre de l'ECF DWWM.



Certaines fonctionnalités et la documentation complémentaire restent à finaliser avant la livraison.



