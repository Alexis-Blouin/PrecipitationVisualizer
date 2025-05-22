// Récupère la divsion du contenu qui est dans le body qui sera accessible pour tous les autres scripts .js
const divContenu = document.getElementById("contenu");

// Récupère les input avec le nom 'uniteTemp'.
const boutonRadioTemp = document.querySelectorAll('input[name="uniteTemp"]');

ajouterEventListenerRadio(boutonRadioTemp, 'uniteTemp');

// Récupère les input avec le nom 'uniteLongueur'.
const boutonRadioLongueur = document.querySelectorAll('input[name="uniteLongueur"]');

ajouterEventListenerRadio(boutonRadioLongueur, 'uniteLongueur');

// Ajoute un listener au bouton hamburger du site lorsque l'écran est plus petit.
const hamburgerMenu = document.querySelector('.hamburger-menu');
const aside = document.querySelector('.collapse.barre-nav');

hamburgerMenu.addEventListener('click', () => {
    hamburgerMenu.classList.toggle('active');
    aside.classList.toggle('active');
});