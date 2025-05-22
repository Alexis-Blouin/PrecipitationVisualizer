// Cette fonction affiche un graphique qui contiendra la température et les précipitations.
function unGraphique(uniteTemperature, uniteLongueur, typeGraphique, date, intervalle) {
    // url de la requête AJAX, les informations sont envoyées avec la méthode GET.
    let url = "./ajax/tempMoy7Jours.php?uniteTemperature=" + uniteTemperature + "&uniteLongueur=" + uniteLongueur + "&date=" + date + "&intervalle=" + intervalle;
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
                afficherAlerte(ERREUR_CONN_BD + donnees[1]);
            } else {
                activerForm();

                let dateParties = date.split("-");

                let annee = parseInt(dateParties[0], 10);
                let mois = parseInt(dateParties[1], 10) - 1;
                let jour = parseInt(dateParties[2], 10);

                // Récupère le date actuelle.
                let dateDebut = new Date(annee, mois, jour);
                jour = dateDebut.getDate();

                const valeursX = [];
                // Boucle pour la période et ajoute les valeurs à valeursX (les jours).
                for (let i = intervalle; i >= 0; i--) {
                    // Ajoute le décalage à la date.
                    let dateDecalee = new Date();
                    dateDecalee.setDate(jour - i);

                    // Ajoute la journée à valeursX.
                    valeursX.push(dateDecalee.getDate());
                }

                let valeursYTemp = [];
                let valeursYPrecip = [];
                let donneesTempAffichees = false;
                let donneesPrecipAffichees = false;
                // Récupère les valeurs de température et de précipitation et les ajoute aux valeurs Y.
                if (donnees[0][1] > 0) {
                    donnees[0][0].forEach(function (element) {
                        valeursYTemp.push(element);
                    });
                    let moyenneTemp = 0;
                    valeursYTemp.forEach(function (element) {
                        moyenneTemp += element;
                    });
                    moyenneTemp = moyenneTemp / valeursYTemp.length;
                    if (moyenneTemp < 0 && uniteLongueur !== "po") {
                        uniteLongueur = "cm";
                    }
                    donneesTempAffichees = true;
                }
                if (donnees[1][1] > 0) {
                    donnees[1][0].forEach(function (element) {
                        if (uniteLongueur === "cm") {
                            valeursYPrecip.push(element / 10);
                        } else {
                            valeursYPrecip.push(element);
                        }
                    });
                    donneesPrecipAffichees = true;
                }

                if (donneesTempAffichees || donneesPrecipAffichees) {
                    // Ajoute le canvas pour le graphique.
                    ajouterCanvas("graphiqueTempPrecip");
                }

                let ensembleDonnees = null; // Création des ensembles de données.
                if (donneesTempAffichees && donneesPrecipAffichees) {
                    ensembleDonnees = [ajouterEnsembleDonnees("Température (°" + uniteTemperature + ")", "rgba(255,0,0,0.2)", "rgba(255,0,0,1)", valeursYTemp),
                    ajouterEnsembleDonnees("Précipitation (" + uniteLongueur + ")", "rgba(0,0,255,0.2)", "rgba(0,0,255,1)", valeursYPrecip)];
                } else if (donneesTempAffichees) {
                    ensembleDonnees = [ajouterEnsembleDonnees("Température (°" + uniteTemperature + ")", "rgba(255,0,0,0.2)", "rgba(255,0,0,1)", valeursYTemp)];
                    afficherAlerte(GRAPH_VIDE_PRECIP, false, 'warning');
                } else if (donneesPrecipAffichees) {
                    ensembleDonnees = [ajouterEnsembleDonnees("Précipitation (" + uniteLongueur + ")", "rgba(0,0,255,0.2)", "rgba(0,0,255,1)", valeursYPrecip)];
                    afficherAlerte(GRAPH_VIDE_TEMP, false, 'warning');
                }
                else {
                    afficherAlerte(GRAPH_VIDE, false);
                }

                if (ensembleDonnees !== null) {
                    ajouterGraphique("graphiqueTempPrecip", typeGraphique, valeursX, ensembleDonnees);
                }
            }
        })
        .catch((erreur) => {
            console.log("Erreur : " + erreur);
        });
}

// Cette fonction affiche deux graphiques, un pour la température et un pour les précipitations.
function deuxGraphique(uniteTemperature, uniteLongueur, typeGraphique, date, intervalle) {

    let url = "./ajax/tempMoy7Jours.php?uniteTemperature=" + uniteTemperature + "&uniteLongueur=" + uniteLongueur + "&date=" + date + "&intervalle=" + intervalle;
    if (uniteLongueur === "mm/cm") {
        uniteLongueur = "mm";
    } else {
        uniteLongueur = "po";
    }

    fetch(url)
        .then((reponse) => {
            if (!reponse.ok) {
                throw new Error("Something went wrong!");
            }

            return reponse.json();

        })
        .then((donnees) => {
            if (donnees[0] == "Erreur BD") {
                afficherAlerte(ERREUR_CONN_BD + donnees[1]);
            } else {
                activerForm();

                let dateEnParties = date.split("-");

                let annee = parseInt(dateEnParties[0], 10);
                let mois = parseInt(dateEnParties[1], 10) - 1;
                let jour = parseInt(dateEnParties[2], 10);

                // Récupère le date actuelle.
                let dateDebut = new Date(annee, mois, jour);
                jour = dateDebut.getDate();

                const valeursX = [];
                for (let i = intervalle; i >= 0; i--) {
                    let dateDecalee = new Date();
                    dateDecalee.setDate(jour - i);

                    valeursX.push(dateDecalee.getDate());
                }

                let valeursYTemp = [];
                let valeursYPrecip = [];
                let donneesTempAffichees = false;
                let donneesPrecipAffichees = false;
                // Récupère les valeurs de température et de précipitation et les ajoute aux valeurs Y.
                if (donnees[0][1] > 0) {
                    donnees[0][0].forEach(function (element) {
                        valeursYTemp.push(element);
                    });
                    let moyenneTemp = 0;
                    valeursYTemp.forEach(function (element) {
                        moyenneTemp += element;
                    });
                    moyenneTemp = moyenneTemp / valeursYTemp.length;
                    if (moyenneTemp < 0 && uniteLongueur !== "po") {
                        uniteLongueur = "cm";
                    }
                    donneesTempAffichees = true;
                    ajouterCanvas("graphiqueTemperature");
                }
                if (donnees[1][1] > 0) {
                    donnees[1][0].forEach(function (element) {
                        if (uniteLongueur === "cm") {
                            valeursYPrecip.push(element / 10);
                        } else {
                            valeursYPrecip.push(element);
                        }
                    });
                    donneesPrecipAffichees = true;
                    ajouterCanvas("graphiquePrecipitation");

                }

                let ensembleDonneesTemps = null;
                let ensembleDonneesPrecip = null;
                // S'il y a des données qui on été récupérées, on crée les ensembles de données.
                if (donneesTempAffichees) {
                    ensembleDonneesTemps = [ajouterEnsembleDonnees("Température (°" + uniteTemperature + ")", "rgba(255,0,0,0.2)", "rgba(255,0,0,1)", valeursYTemp)];
                    if (!donneesPrecipAffichees) {
                        afficherAlerte(GRAPH_VIDE_PRECIP, false, 'warning');
                    }
                }
                if (donneesPrecipAffichees) {
                    ensembleDonneesPrecip = [ajouterEnsembleDonnees("Précipitation (" + uniteLongueur + ")", "rgba(0,0,255,0.2)", "rgba(0,0,255,1)", valeursYPrecip)];
                    if (!donneesTempAffichees) {
                        afficherAlerte(GRAPH_VIDE_TEMP, false, 'warning');
                    }
                }
                // Si aucune donnée n'a été affichée, on affiche une alerte.
                if (!donneesTempAffichees && !donneesPrecipAffichees) {
                    afficherAlerte(GRAPH_VIDE, false);
                }

                // Crée les graphiques avec les données récupérées. Ici, deux graphiques sont créés.
                if (ensembleDonneesTemps !== null) {
                    ajouterGraphique("graphiqueTemperature", typeGraphique, valeursX, ensembleDonneesTemps);
                }
                if (ensembleDonneesPrecip !== null) {
                    ajouterGraphique("graphiquePrecipitation", typeGraphique, valeursX, ensembleDonneesPrecip);
                }
            }
        })
        .catch((erreur) => {
            console.log("Erreur : " + erreur);
        });
}

// Cette fonction ajoute un canvas dans lequel on mettra un graphique.
function ajouterCanvas(idCanvas) {
    // Crée un canvas, lui donne un id et une classe.
    const div = document.querySelector('.canvas');
    const canvas = document.createElement("canvas");
    canvas.id = idCanvas;
    canvas.classList.add("graphique");

    // Ajoute le canvas à la fin de la division du contenu.
    div.appendChild(canvas);
}

// Fonction qui permet d'ajout une ensemble de données au graphique.
function ajouterEnsembleDonnees(titre, couleurFond, couleurLigne, donnees) {
    return {
        label: titre,
        fill: true,
        tension: 0.3,
        backgroundColor: couleurFond,
        borderColor: couleurLigne,
        data: donnees,
        borderWidth: 1
    };
}

/* Crée le graphique avec les données récupérées.
    Fait avec https://www.w3schools.com/ai/ai_chartjs.asp
    https://www.chartjs.org/docs/latest/getting-started/usage.html */
function ajouterGraphique(titreGraph, typeGraphique, valeursX, ensembleDonneesTemps) {
    if (typeGraphique === 'lignes') {
        typeGraphique = 'line';
    } else if (typeGraphique === 'bandes') {
        typeGraphique = 'bar';
    }
    new Chart(titreGraph, {
        type: typeGraphique,
        // Assignation des données au graphique. Il y aura deux lignes, une pour la température et une pour les précipitations.
        data: {
            labels: valeursX,
            datasets: ensembleDonneesTemps
        },

        options: {
        }
    });
}

// Fonction pour activer les forms, par exemple, si la requête à la base de données à fonctionnée.
function activerForm() {
    // Affiche le form pour la période.
    let formPeriode = document.getElementById("periodeGraphique");
    formPeriode.removeAttribute("hidden");
    // Affiche le form pour le nombre de graphiques.
    let formNombreGraphique = document.getElementById("formNombreGraphique");
    formNombreGraphique.removeAttribute("hidden");
    // Affiche le form pour le type de graphique.
    let formTypeGraphique = document.getElementById("formTypeGraphique");
    formTypeGraphique.removeAttribute("hidden");

    // Récupère les boutons radios.
    const radioNombreGraph = document.querySelectorAll('input[name="nombreGraphique"]');
    const radioTypeGraph = document.querySelectorAll('input[name="typeGraphique"]');

    // Ajoute un listener sur les boutons radios.
    ajouterEventListenerRadio(radioNombreGraph, 'nombreGraphique');
    ajouterEventListenerRadio(radioTypeGraph, 'typeGraphique');
}