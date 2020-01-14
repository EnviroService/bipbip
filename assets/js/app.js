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
require('../scss/partnerList.scss');
require('../scss/partnerPage.scss');
require('../scss/newPartner.scss');
require('../scss/editPartner.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');


// BURGER MENU
if (document.querySelector('#menu-burger') !== null) {
    const menuBurger = document.querySelector('#menu-burger');
    const menu = document.querySelector('#menu');
    const closeButton = document.querySelector('.close-button');

    menuBurger.addEventListener('click', () => {
        menu.classList.toggle('displayed');
    });

    closeButton.addEventListener('click', () => {
        menu.classList.toggle('displayed');
    });
};

