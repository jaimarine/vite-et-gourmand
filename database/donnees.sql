
-- ============================================
-- VITE & GOURMAND
-- Insertion des données de démonstration
-- ============================================

USE vite_et_gourmand;

-- Création des trois menus.
INSERT INTO menus
(titre, description, theme, regime,
 personnes_minimum, prix, stock, conditions)
VALUES
(
    'Menu de Noël',
    'Un repas festif pour vos fêtes.',
    'Noël',
    'classique',
    4,
    120.00,
    5,
    'Commander au moins 7 jours avant.'
),
(
    'Menu de Pâques',
    'Un menu gourmand et printanier.',
    'Pâques',
    'classique',
    6,
    180.00,
    8,
    'Commander au moins 5 jours avant.'
),
(
    'Menu végétarien',
    'Une sélection de plats végétariens.',
    'Classique',
    'vegetarien',
    2,
    50.00,
    10,
    'Commander au moins 3 jours avant.'
);

-- Création des plats.
INSERT INTO plats (nom, description, type)
VALUES
('Velouté de potimarron', 'Entrée de saison', 'entree'),
('Volaille rôtie', 'Volaille et légumes', 'plat'),
('Bûche au chocolat', 'Dessert de Noël', 'dessert'),
('Salade printanière', 'Légumes de saison', 'entree'),
('Agneau rôti', 'Agneau et légumes', 'plat'),
('Tarte aux fruits', 'Dessert fruité', 'dessert'),
('Salade de saison', 'Entrée végétarienne', 'entree'),
('Risotto aux champignons', 'Plat végétarien', 'plat'),
('Salade de fruits', 'Dessert frais', 'dessert');

-- Association des plats aux menus.
-- On recherche les identifiants par leur nom
-- plutôt que de supposer que les ID valent 1, 2, 3.
INSERT INTO menu_plats (menu_id, plat_id)
SELECT m.id, p.id
FROM menus AS m
JOIN plats AS p
WHERE
    (m.titre = 'Menu de Noël'
        AND p.nom IN (
            'Velouté de potimarron',
            'Volaille rôtie',
            'Bûche au chocolat'
        ))
    OR
    (m.titre = 'Menu de Pâques'
        AND p.nom IN (
            'Salade printanière',
            'Agneau rôti',
            'Tarte aux fruits'
        ))
    OR
    (m.titre = 'Menu végétarien'
        AND p.nom IN (
            'Salade de saison',
            'Risotto aux champignons',
            'Salade de fruits'
        ));



-- ============================================
-- DONNÉES DE DÉMONSTRATION
-- ============================================

USE vite_et_gourmand;

-- Création de quelques allergènes.
INSERT INTO allergenes (nom)
VALUES
('Lait'),
('Œufs'),
('Céréales contenant du gluten');

-- Association des allergènes aux plats.
-- Les identifiants sont retrouvés par leurs noms.
INSERT INTO plat_allergenes (plat_id, allergene_id)
SELECT p.id, a.id
FROM plats AS p
JOIN allergenes AS a
WHERE
    (p.nom = 'Velouté de potimarron'
        AND a.nom = 'Lait')
    OR
    (p.nom = 'Bûche au chocolat'
        AND a.nom IN (
            'Lait',
            'Œufs',
            'Céréales contenant du gluten'
        ))
    OR
    (p.nom = 'Tarte aux fruits'
        AND a.nom IN (
            'Œufs',
            'Céréales contenant du gluten'
        ))
    OR
    (p.nom = 'Risotto aux champignons'
        AND a.nom = 'Lait');
