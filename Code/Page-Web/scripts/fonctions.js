function afficherAlerte(message, ajouterBouton = true, typeAlerte = 'danger') {
    // Crée un élément div avec les classes alert et alert-danger avec le message d'erreur.
    const alerte = document.createElement("div");
    alerte.classList.add("alert", "alert-" + typeAlerte);
    alerte.textContent = message;

    const form = document.createElement("form");
    if (ajouterBouton) {// Crée un élément form et définit son action.
        form.action = "";

        // Crée un élément button de type submit pour mettre à jour la page.
        const boutonRecharge = document.createElement("button");
        boutonRecharge.classList.add("bouton-erreur");
        boutonRecharge.type = "submit";
        boutonRecharge.textContent = "Recharger la page";

        // appendChild ajoute l'élément à la fin de la liste des enfants de l'élément.
        form.appendChild(boutonRecharge);
    }

    // Va chercher la division du contenu et y ajoute les éléments.
    const divContenu = document.getElementById("contenu");
    divContenu.appendChild(alerte);
    if (ajouterBouton) { divContenu.appendChild(form); }
}

// Permet de récupérer l'heure actuelle (heures, minutes et secondes).
function recupererHeureActuelle() {
    let heure = new Date().getHours();
    heure = heure.toString().length === 1 ? "0" + heure : heure;
    let minutes = new Date().getMinutes();
    minutes = minutes.toString().length === 1 ? "0" + minutes : minutes;
    let secondes = new Date().getSeconds();
    secondes = secondes.toString().length === 1 ? "0" + secondes : secondes;

    return heure + ":" + minutes + ":" + secondes;
}

// Cette fonction attache un listener sur les boutons radios.
function ajouterEventListenerRadio(boutonsRadios, nomInput) {
    // Boucle pour tous les input récupérés.
    boutonsRadios.forEach(function (boutonRadio) {
        boutonRadio.addEventListener('change', function () {
            // Récupère la nouvelle valeur sélectionnée.
            var valeurSelectionnee = document.querySelector('input[name="' + nomInput + '"]:checked').value;

            // Crée un form qui enverra la nouvelle données à la page.
            const form = document.createElement('form');
            form.method = 'post';
            form.action = ''; // l'action est vide pour que le form appelle la page d'où il est appelé.

            // Crée un input qui ne sera pas visible pour l'utilisateur avec la nouvelle valeur.
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = nomInput;
            input.value = valeurSelectionnee;

            // appendChild ajoute l'élément à la fin de la liste des enfants de l'élément.
            form.appendChild(input);
            divContenu.appendChild(form);

            // Envoi du form.
            form.submit();
        });
    });
}