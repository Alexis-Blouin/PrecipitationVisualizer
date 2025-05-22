// Récupère les input avec le nom 'donneeTableauChoisie'.
const boutonRadioDonnee = document.querySelectorAll('input[name="donneeTableauChoisie"]');

// Ajoute un listener sur les boutons radios.
ajouterEventListenerRadio(boutonRadioDonnee, 'donneeTableauChoisie');

// Récupère la division qui contiendra les tableaux.
const contenu = document.getElementById("contenu");

// Cette fonction affiche un tableau qui contiendra les précipitations.
function tableau(uniteTemperature, uniteLongueur, dateSelectionnee, donneeTableau) {
    // url de la requête AJAX, les informations sont envoyées avec la méthode GET.
    let url = "./ajax/donneesParjour.php?uniteTemperature=" + uniteTemperature + "&uniteLongueur=" + uniteLongueur + "&date=" + dateSelectionnee;
    // Selon l'unité de longueur, on change le texte de l'unité.
    if (uniteLongueur === "mm/cm") {
        uniteLongueur = "mm";
    } else {
        uniteLongueur = "po";
    }

    // Exécute la requête Ajax.
    fetch(url)
        .then((reponse) => {
            // S'assure que la réponse est OK.
            if (!reponse.ok) {
                throw new Error("Erreur !");
            }
            // Convertit la réponse en objet JSON.
            return reponse.json();
        })
        .then((donnees) => {
            if (donnees[0] == "Erreur BD") {
                // Si une erreur est retournée, on affiche un message d'erreur.
                erreurBD(donnees[1]);
            } else {
                // Affiche le form pour la date et pour le nombre de tableaux.
                const formDate = document.getElementById("formDate");
                formDate.removeAttribute("hidden");
                const formDonneeTableau = document.getElementById("formDonneeTableau");
                formDonneeTableau.removeAttribute("hidden");

                let donneesTempAffichees = donnees[0].length > 0;
                let donneesPrecipAffichees = donnees[1].length > 0;
                // S'il y a des données, on les affiche.
                if (donnees[0].length > 0 && donneeTableau.includes("temp")) {
                    ajouterTableau("tableDonneesTemperatures", "titresColonnesTemperatures", "donneesTemperature");
                    tableauTemp(donnees[0], uniteTemperature);
                    donneesTempAffichees = true;
                }
                if (donnees[1].length > 0 && donneeTableau.includes("précip")) {
                    ajouterTableau("tableDonneesLongueurs", "titresColonnesLongueurs", "donneesLongueurs")
                    tableauPrecip(donnees[1], uniteLongueur);
                    donneesPrecipAffichees = true;
                }
                // Si aucune données n'a été affichées, on affiche un message disant la situation.
                if (!donneesTempAffichees && !donneesPrecipAffichees) {
                    afficherAlerte(TABLEAU_VIDE + dateSelectionnee, false);
                } else if (!donneesTempAffichees && donneeTableau.includes("temp")) {
                    afficherAlerte(TABLEAU_VIDE_TEMP + dateSelectionnee, false, 'warning');
                } else if (!donneesPrecipAffichees && donneeTableau.includes("précip")) {
                    afficherAlerte(TABLEAU_VIDE_PRECIP + dateSelectionnee, false, 'warning');
                }
            }
        })
        .catch((erreur) => {
            console.log(erreur);
        });
}

// Fonction qui permet d'ajouter un tableau à la page.
function ajouterTableau(idTable, idTitreColonne, idDonnees) {
    // Création des éléments du tableau et paramétrage.
    const divTableaux = document.querySelector('.divTableaux');
    const div = document.createElement("div");
    div.classList.add("divTableau");
    const table = document.createElement("table");
    table.id = idTable;
    table.classList.add("table");
    const thead = document.createElement("thead");
    const tr = document.createElement("tr");
    tr.id = idTitreColonne;
    const tbody = document.createElement("tbody");
    tbody.id = idDonnees;

    // Ajoute le tableau à la page.
    thead.appendChild(tr);
    table.appendChild(thead);
    table.appendChild(tbody);
    div.appendChild(table);
    divTableaux.appendChild(div);
}

// Fonction qui ajoute un colonne au tableau.
function ajouterColonne(titresColonnes, nom) {
    // Création de l'élément colonne et paramétrage de celle-ci.
    const nouveauTitre = document.createElement("th");
    nouveauTitre.scope = "col";
    nouveauTitre.textContent = nom;

    // Ajoute la colonne au tableau.
    titresColonnes.appendChild(nouveauTitre);
}

// Fonction qui va insérer le données dans le tableau des températures.
function tableauTemp(donnees, uniteTemperature) {
    // Va cherche 'thead' du tableau pour lui ajouter les colonnes.
    let titresColonnes = document.getElementById("titresColonnesTemperatures");
    ajouterColonne(titresColonnes, "Température (°" + uniteTemperature + ")");
    ajouterColonne(titresColonnes, "Heure");

    // Va cherche 'tbody' du tableau.
    let tableau = document.getElementById("donneesTemperature");

    // Pour toutes les occurences, on ajoute une ligne au tableau.
    donnees.forEach(donnee => {
        const nouveauTr = document.createElement("tr");

        // Cette colonne contient la valeur captée.
        const tdDonnee = document.createElement("td");
        tdDonnee.textContent = donnee["Valeur"];
        // Cette colonne contient l'heure de l'enregistrement.
        const tdHeure = document.createElement("td");
        tdHeure.textContent = donnee["Heure"];

        // appendChild ajoute l'élément à la fin de la liste des enfants de l'élément.
        nouveauTr.appendChild(tdDonnee);
        nouveauTr.appendChild(tdHeure);

        tableau.appendChild(nouveauTr);
    });
}

// Fonction qui va insérer le données dans le tableau des précipitations.
function tableauPrecip(donnees, uniteLongueur) {
    // Va cherche 'thead' du tableau pour lui ajouter les colonnes.
    let titresColonnes = document.getElementById("titresColonnesLongueurs");
    ajouterColonne(titresColonnes, "Précipitation (" + uniteLongueur + ")");
    ajouterColonne(titresColonnes, "Heure");

    // Va cherche 'tbody' du tableau.
    let tableau = document.getElementById("donneesLongueurs");

    // Récupère la première valeur pour calculer la quantité de précipitation réelle.
    let premiereValeur = donnees[0]["Valeur"];

    // Pour toutes les occurences, on ajoute une ligne au tableau.
    donnees.forEach(donnee => {
        const nouveauTr = document.createElement("tr");

        // Cette colonne contient la quantité réelle de neige qui est tombée dans la journée selon l'heure.
        const tdPrecipitation = document.createElement("td");
        // La valeur ne peut pas être négative et on arrondit à 2 décimales.
        tdPrecipitation.textContent = Math.max((premiereValeur - donnee["Valeur"]).toFixed(2), 0);
        // Cette colonne contient l'heure de l'enregistrement.
        const tdHeure = document.createElement("td");
        tdHeure.textContent = donnee["Heure"];

        // appendChild ajoute l'élément à la fin de la liste des enfants de l'élément.
        nouveauTr.appendChild(tdPrecipitation);
        nouveauTr.appendChild(tdHeure);

        tableau.appendChild(nouveauTr);
    });
}