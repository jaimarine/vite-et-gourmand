
-- ============================================
-- PROJET : VITE & GOURMAND
-- Création des premières tables relationnelles
-- ============================================

-- Sélection de la base de données.
USE vite_et_gourmand;

-- ============================================
-- TABLE 1 : UTILISATEURS
-- ============================================

-- Cette table stocke les comptes du site.
-- Le rôle détermine les autorisations de chacun.

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,

    email VARCHAR(255) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    adresse TEXT,

    -- Le mot de passe sera stocké sous forme de hash.
    mot_de_passe VARCHAR(255) NOT NULL,

    -- Trois catégories de comptes.
    role ENUM(
        'utilisateur',
        'employe',
        'administrateur'
    ) NOT NULL DEFAULT 'utilisateur',

    -- Permet de désactiver un compte.
    actif BOOLEAN NOT NULL DEFAULT TRUE,

    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE 2 : MENUS
-- ============================================

-- Contient les menus proposés par le traiteur.

CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,

    titre VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,

    theme VARCHAR(100) NOT NULL,
    regime VARCHAR(100) NOT NULL,

    -- Nombre minimal de personnes à commander.
    personnes_minimum INT NOT NULL,

    -- Prix du menu pour ce nombre minimal.
    prix DECIMAL(10,2) NOT NULL,

    -- Nombre de commandes encore disponibles.
    stock INT NOT NULL DEFAULT 0,

    -- Conditions de commande et de conservation.
    conditions TEXT,

    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Empêche les valeurs négatives ou incohérentes.
    CONSTRAINT chk_personnes_min
        CHECK (personnes_minimum > 0),

    CONSTRAINT chk_prix
        CHECK (prix >= 0),

    CONSTRAINT chk_stock
        CHECK (stock >= 0)
);

-- ============================================
-- TABLE 3 : PLATS
-- ============================================

-- Un plat peut être utilisé dans plusieurs menus.

CREATE TABLE plats (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nom VARCHAR(150) NOT NULL,

    description TEXT,

    -- Catégorie du plat.
    type ENUM(
        'entree',
        'plat',
        'dessert'
    ) NOT NULL
);

-- ============================================
-- TABLE 4 : MENU_PLATS
-- ============================================

-- Table d'association entre menus et plats.
-- Elle matérialise une relation plusieurs-à-plusieurs.

CREATE TABLE menu_plats (
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,

    -- Empêche d'associer deux fois le même plat
    -- au même menu.
    PRIMARY KEY (menu_id, plat_id),

    -- Lien vers la table menus.
    CONSTRAINT fk_menu_plats_menu
        FOREIGN KEY (menu_id)
        REFERENCES menus(id)
        ON DELETE CASCADE,

    -- Lien vers la table plats.
    CONSTRAINT fk_menu_plats_plat
        FOREIGN KEY (plat_id)
        REFERENCES plats(id)
        ON DELETE CASCADE
);



-- ============================================
-- PROJET : VITE & GOURMAND
-- Gestion des allergènes alimentaires
-- ============================================

USE vite_et_gourmand;

-- TABLE : allergenes
-- Contient la liste des allergènes.
CREATE TABLE allergenes (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Nom de l'allergène.
    nom VARCHAR(100) NOT NULL UNIQUE
);

-- TABLE : plat_allergenes
-- Associe chaque plat à ses allergènes.
CREATE TABLE plat_allergenes (
    plat_id INT NOT NULL,
    allergene_id INT NOT NULL,

    -- Évite les associations en double.
    PRIMARY KEY (plat_id, allergene_id),

    -- Relation vers les plats.
    CONSTRAINT fk_pa_plat
        FOREIGN KEY (plat_id)
        REFERENCES plats(id)
        ON DELETE CASCADE,

    -- Relation vers les allergènes.
    CONSTRAINT fk_pa_allergene
        FOREIGN KEY (allergene_id)
        REFERENCES allergenes(id)
        ON DELETE CASCADE
);
