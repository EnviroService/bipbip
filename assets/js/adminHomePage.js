/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */


// any CSS you require will output into a single css file (app.css in this case)
require('../scss/adminHomePage.scss');

const collecters = document.querySelectorAll('a.collecters');
const collecterAtions = document.querySelectorAll('div.collecterActions');
// eslint-disable-next-line no-plusplus
for (let i = 0; i < collecters.length; i++) {
    collecters[i].addEventListener('click', () => {
        collecterAtions[i].classList.toggle('displayed');
    });
}

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');
