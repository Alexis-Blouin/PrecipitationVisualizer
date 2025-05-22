// Fonction pour afficher les données de MQTT.
function afficherDonneesMQTT(idParametre, uniteTemp, unitePrecip) {
    // Variable utilisée pour la dernière température reçue.
    let derniereTemperature = 0;

    // url de la requête AJAX avec le paremètre voulu.
    let url = "./ajax/mqtt.php?topic=" + idParametre;

    // Exécute la requête Ajax.
    fetch(url)
        .then((reponse) => {
            // S'assure que la réponse est OK.
            if (!reponse.ok) {
                throw new Error("Erreur !");
            }
            //console.log(reponse.json());
            // Convertit la réponse en objet JSON.
            return reponse.json();

        })
        .then((donnees) => {
            if (donnees[0] == "Erreur BD") {
                // Si une erreur est retournée, on affiche un message d'erreur.
                afficherAlerte(ERREUR_CONN_BD + donnees[1]);
            } else if (donnees[0] == "erreur") {
                // Arrete l'intervalle qui appelle en continue cette fonction.
                arreterIntervalle();

                // Récupération des widgets de la page.
                const widgets = document.getElementsByClassName("widget");
                let widgetsSupprimes = [];
                let messageErreur;
                if (widgets.length === 0) {
                    let texteChargement = document.getElementById("chargement");
                    divContenu.removeChild(texteChargement);
                    messageErreur = ERREUR_CONN_ARDUINO;
                }
                else {
                    // Supprime les widgets pour ensuite les réinsérer, mais après l'alerte.
                    for (let i = widgets.length - 1; i >= 0; i--) {
                        // On met les widgets supprimés dans un tableau pour les réinsérer après,
                        //  car quand on les supprime, ils sont aussi supprimés de la variable widgets.
                        widgetsSupprimes.push(widgets[i]);
                        divContenu.removeChild(widgets[i]);
                    }
                    messageErreur = PERTE_CONN_ARDUINO;
                }

                // Ajout de l'alerte.
                afficherAlerte(messageErreur);

                // Réinsertion des widgets.
                if (widgetsSupprimes.length > 0) {
                    for (let widget of widgetsSupprimes) {
                        divContenu.appendChild(widget);
                    }
                }
            } else {
                // Suppression du message de chargement des données.
                const texteChargement = document.getElementById("chargement");
                if (texteChargement !== null) { divContenu.removeChild(texteChargement); }

                // Récupération de l'heure actuelle.
                const heureActuelle = recupererHeureActuelle();

                // Selon le paramètre, le message du widget est différent.
                if (donnees["id_parametre"] === 1) {
                    const divTemp = document.getElementById("temp");
                    derniereTemperature = donnees["valeur"];

                    if (uniteTemp === "F") {
                        donnees["valeur"] = celciusVersFahrenheit(donnees["valeur"]);
                    }

                    // Si c'est la première données, on doit créer le widget.
                    if (divTemp == null) {
                        const divTemp = document.createElement("div");
                        divTemp.id = "temp";
                        divTemp.classList.add("widget");
                        const titreTemp = document.createElement("h3");
                        const texteTemp = document.createElement("p");
                        const texteHeure = document.createElement("p");

                        // Récupération de la donnée.
                        titreTemp.textContent = donnees["valeur"] + " " + String.fromCharCode(176) + uniteTemp;
                        texteTemp.textContent = "Température actuelle";

                        texteHeure.textContent = "Enregistrée à : " + heureActuelle;

                        // Ajout du widget à la page.
                        divTemp.appendChild(titreTemp);
                        divTemp.appendChild(texteTemp);
                        divTemp.appendChild(texteHeure);
                        divContenu.appendChild(divTemp);
                    } else { // Si le widget existe déjà, on ne fait que mettre à jour les données.
                        const children = divTemp.children;
                        children[0].textContent = donnees["valeur"] + " " + String.fromCharCode(176) + uniteTemp;

                        children[2].textContent = "Enregistrée à : " + heureActuelle;
                    }
                } else {
                    const divPrecipitation = document.getElementById("precipitation");

                    if (unitePrecip.includes("mm")) {
                        if (derniereTemperature >= 0) {
                            unitePrecip = "mm";
                        } else {
                            donnees["valeur"] = donnees["valeur"] / 10
                            unitePrecip = "cm";
                        }
                    } else {
                        donnees["valeur"] = millimetreVersPouce(donnees["valeur"]);
                    }

                    if (divPrecipitation == null) {
                        const divPrecipitation = document.createElement("div");
                        divPrecipitation.id = "precipitation";
                        divPrecipitation.classList.add("widget");
                        const titrePrecipitation = document.createElement("h3");
                        const textePrecipitation = document.createElement("p");
                        const texteHeure = document.createElement("p");

                        titrePrecipitation.textContent = donnees["valeur"] + " " + unitePrecip;
                        textePrecipitation.textContent = "Précipitation de la journée";

                        texteHeure.textContent = "Enregistrée à : " + heureActuelle;

                        divPrecipitation.appendChild(titrePrecipitation);
                        divPrecipitation.appendChild(textePrecipitation);
                        divPrecipitation.appendChild(texteHeure);
                        divContenu.appendChild(divPrecipitation);
                    } else {
                        const children = divPrecipitation.children;
                        children[0].textContent = donnees["valeur"] + " " + unitePrecip;

                        children[2].textContent = "Enregistrée à : " + heureActuelle;
                    }
                }
            }
        })
        .catch((error) => {
            console.log("Erreur : " + error);
        });
}

let intervalle;
let parametre = 1;

// Fonction pour récupérer les données de la BD en alternant entre température et précipitation.
function recupererDonnee(uniteTemp, unitePrecip) {
    afficherDonneesMQTT(parametre, uniteTemp, unitePrecip);
    parametre = parametre === 1 ? 2 : 1;
}

// Fonction pour arrêter l'intervalle.
function arreterIntervalle() {
    clearInterval(sessionStorage.getItem('intervalle')); // Clear using stored ID
    sessionStorage.removeItem('intervalle'); // Remove the stored ID
}

// Fonction pour débuter l'intervalle.
function debuterIntervalle(uniteTemp, unitePrecip) {

    // Lorsque la page est fermée, on arrête l'intervalle.
    window.addEventListener('unload', arreterIntervalle);

    // Appel initial de la fonction.
    recupererDonnee(uniteTemp, unitePrecip);
    // Crée un intervalle pour appeler la fonction recupererDonnee() toutes les 10 secondes.
    intervalle = setInterval(function () { recupererDonnee(uniteTemp, unitePrecip) }, 10000);
    sessionStorage.setItem('intervalle', intervalle); // Store the ID in sessionStorage
}

// C'est fonctions convertissent les données et laissent 2 chiffres de précision.
function celciusVersFahrenheit(temperature) {
    return (temperature * 1.8 + 32).toFixed(2);
}
function millimetreVersPouce(longueur) {
    return (longueur / 25.4).toFixed(2);
}