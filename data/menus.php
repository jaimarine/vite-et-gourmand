
<?php
/*
 * FICHIER : data/menus.php
 *
 * Ce fichier contient les menus de démonstration.
 * Plus tard, ces données viendront de MySQL.
 *
 * Chaque menu possède un identifiant unique.
 */

return [
    1 => [
        "id" => 1,
        "titre" => "Menu de Noël",
        "description" => "Un repas festif pour vos fêtes.",
        "theme" => "Noël",
        "regime" => "classique",
        "personnes" => 4,
        "prix" => 120,
        "stock" => 5,
        "conditions" => "Commander au moins 7 jours avant la prestation.",
        "plats" => [
            [
                "type" => "Entrée",
                "nom" => "Velouté de potimarron",
                "allergenes" => ["Lait"]
            ],
            [
                "type" => "Plat",
                "nom" => "Volaille rôtie et légumes",
                "allergenes" => []
            ],
            [
                "type" => "Dessert",
                "nom" => "Bûche au chocolat",
                "allergenes" => ["Lait", "Œufs", "Gluten"]
            ]
        ]
    ],

    2 => [
        "id" => 2,
        "titre" => "Menu de Pâques",
        "description" => "Un menu gourmand et printanier.",
        "theme" => "Pâques",
        "regime" => "classique",
        "personnes" => 6,
        "prix" => 180,
        "stock" => 8,
        "conditions" => "Commander au moins 5 jours avant la prestation.",
        "plats" => [
            [
                "type" => "Entrée",
                "nom" => "Salade printanière",
                "allergenes" => []
            ],
            [
                "type" => "Plat",
                "nom" => "Agneau et légumes de saison",
                "allergenes" => []
            ],
            [
                "type" => "Dessert",
                "nom" => "Tarte aux fruits",
                "allergenes" => ["Gluten", "Œufs"]
            ]
        ]
    ],

    3 => [
        "id" => 3,
        "titre" => "Menu végétarien",
        "description" => "Une sélection de plats végétariens.",
        "theme" => "Classique",
        "regime" => "vegetarien",
        "personnes" => 2,
        "prix" => 50,
        "stock" => 10,
        "conditions" => "Commander au moins 3 jours avant la prestation.",
        "plats" => [
            [
                "type" => "Entrée",
                "nom" => "Salade de saison",
                "allergenes" => []
            ],
            [
                "type" => "Plat",
                "nom" => "Risotto aux champignons",
                "allergenes" => ["Lait"]
            ],
            [
                "type" => "Dessert",
                "nom" => "Salade de fruits",
                "allergenes" => []
            ]
        ]
    ]
];
