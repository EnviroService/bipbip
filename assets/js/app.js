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
//story_bubbles = menu burger
//modal story = menu

const menu_burger = document.querySelector("#menu-burger");
const menu = document.querySelector("#menu");
const close_button = document.querySelector(".close-button");

menu_burger.addEventListener('click', function () {
    menu.classList.toggle('displayed');
});

close_button.addEventListener('click', function () {
    menu.classList.toggle('displayed');
});


