/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

require('bootstrap');

// any CSS you require will output into a single css file (app.css in this case)
require('../scss/app.scss');
require('../scss/reset.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');


// BURGER MENU

const menuBurger = document.querySelector('#menu-burger');
const menu = document.querySelector('#menu');
const closeButton = document.querySelector('.close-button');

menuBurger.addEventListener('click', () => {
    menu.classList.toggle('displayed');
});

closeButton.addEventListener('click', () => {
    menu.classList.toggle('displayed');
});

// Retour haut de page
/* POUR AFFICHER LE BOUTTON SUR VOS PAGES, INSERER A LA FIN DE VOTRE HTML LE BOUTTON SUIVANT:
 <button onclick="retourHaut()" id="haut" title="Retour haut de page">Haut de page</button>
*/

function scrollFunction()
{
    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
        document.getElementById('haut').style.display = 'block';
    } else {
        document.getElementById('haut').style.display = 'none';
    }
}
window.onscroll = function () {
    scrollFunction();
};
function retourHaut()
{
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}
