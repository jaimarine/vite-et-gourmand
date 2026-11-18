
-- VITE & GOURMAND
-- Création de la table commandes
-- À exécuter après schema.sql

CREATE TABLE IF NOT EXISTS commandes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    utilisateur_id INT(11) NOT NULL,
    menu_id INT(11) NOT NULL,
    quantite INT(11) NOT NULL DEFAULT 1,
    prix_total DECIMAL(10,2) NOT NULL,

    statut ENUM(
        'en_attente',
        'confirmee',
        'en_preparation',
        'livree',
        'annulee'
    ) NOT NULL DEFAULT 'en_attente',

    date_commande DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    nombre_personnes INT(11) NOT NULL DEFAULT 1,
    date_prestation DATE DEFAULT NULL,
    heure_livraison TIME DEFAULT NULL,
    adresse_livraison TEXT DEFAULT NULL,
    telephone_contact VARCHAR(20) DEFAULT NULL,

    PRIMARY KEY (id),

    KEY idx_commandes_menu (menu_id),
    KEY idx_commandes_utilisateur (utilisateur_id),

    CONSTRAINT fk_commandes_menu
        FOREIGN KEY (menu_id)
        REFERENCES menus(id)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT,

    CONSTRAINT fk_commandes_utilisateur
        FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs(id)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
