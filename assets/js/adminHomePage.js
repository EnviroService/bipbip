/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');

// any CSS you require will output into a single css file (app.css in this case)
require('../scss/admin.scss');

// any CSS you require will output into a single css file (app.css in this case)
require('bootstrap');


const categories = document.querySelectorAll('h2.title');
const subCategories = document.querySelectorAll('div.block');

for (let i = 0; i < categories.length; categories[i++]) {
    categories[i].addEventListener('click', () => {
        subCategories[i].classList.toggle('displayed');
    });
}
