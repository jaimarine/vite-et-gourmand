

/*
 * PROJET : Vite & Gourmand
 * FICHIER : menus.js
 *
 * OBJECTIF :
 * Filtrer les menus sans recharger la page.
 *
 * CORRECTION :
 * - Attendre le chargement du HTML.
 * - Vérifier que les éléments existent.
 * - Comparer les critères des menus.
 * - Masquer les cartes non correspondantes.
 * - Afficher le nombre de résultats.
 */

// Attendre que le document HTML soit prêt.
document.addEventListener("DOMContentLoaded", () => {

    // Récupération des filtres.
    const prixMax = document.getElementById("prix-max");
    const filtreTheme = document.getElementById("filtre-theme");
    const filtreRegime = document.getElementById("filtre-regime");
    const filtrePersonnes =
        document.getElementById("filtre-personnes");

    // Récupération des cartes et du compteur.
    const cartes = document.querySelectorAll(".carte-menu");
    const compteur =
        document.getElementById("nombre-resultats");

    // Vérification des éléments nécessaires.
    if (
        !prixMax ||
        !filtreTheme ||
        !filtreRegime ||
        !filtrePersonnes ||
        !compteur ||
        cartes.length === 0
    ) {
        console.error(
            "Erreur : éléments du catalogue introuvables."
        );
        return;
    }

    console.log("Filtres initialisés :", cartes.length);

    /*
     * Fonction de filtrage.
     * Tous les critères doivent être respectés.
     */
    function filtrerMenus() {

        let nombreVisible = 0;

        // Lecture des valeurs saisies.
        const prixSaisi = prixMax.value.trim();
        const themeChoisi = filtreTheme.value;
        const regimeChoisi = filtreRegime.value;
        const personnesSaisies =
            filtrePersonnes.value.trim();

        // Vérification de chaque menu.
        cartes.forEach((carte) => {

            // Informations issues des attributs data-*.
            const prix = Number(carte.dataset.prix);
            const theme = carte.dataset.theme;
            const regime = carte.dataset.regime;
            const personnes =
                Number(carte.dataset.personnes);

            // Prix inférieur ou égal au maximum demandé.
            const prixValide =
                prixSaisi === "" ||
                prix <= Number(prixSaisi);

            // Correspondance du thème.
            const themeValide =
                themeChoisi === "" ||
                theme === themeChoisi;

            // Correspondance du régime alimentaire.
            const regimeValide =
                regimeChoisi === "" ||
                regime === regimeChoisi;

            // Nombre minimal de personnes du menu.
            const personnesValides =
                personnesSaisies === "" ||
                personnes >= Number(personnesSaisies);

            // Combinaison des quatre critères.
            const afficher =
                prixValide &&
                themeValide &&
                regimeValide &&
                personnesValides;

            // Masquer ou afficher la carte.
            carte.hidden = !afficher;

            // Mise à jour du compteur.
            if (afficher) {
                nombreVisible++;
            }
        });

        // Affichage du nombre de résultats.
        compteur.textContent =
            `${nombreVisible} menu(s) trouvé(s).`;

        // Message de diagnostic dans la console.
        console.log("Menus visibles :", nombreVisible);
    }

    // Écoute des modifications des champs.
    prixMax.addEventListener("input", filtrerMenus);

    filtreTheme.addEventListener("change", filtrerMenus);

    filtreRegime.addEventListener("change", filtrerMenus);

    filtrePersonnes.addEventListener(
        "input",
        filtrerMenus
    );

    // Initialisation au chargement de la page.
    filtrerMenus();
});

