/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */


// any CSS you require will output into a single css file (app.css in this case)
require('../scss/admin.scss');

const collecters = document.querySelectorAll('li.collecters');
const collecterAtionsLister = document.querySelectorAll('p.collecterActionsLister');
const collecterAtionsAdd = document.querySelectorAll('p.collecterActionsAdd');
// eslint-disable-next-line no-plusplus

for (let i = 0; i < collecters.length; i++) {
    collecters[i].addEventListener('click', () => {
        collecterAtionsLister[i].classList.toggle('displayed');
        collecterAtionsAdd[i].classList.toggle('displayed');
    });
}

const officialDocuments = document.querySelectorAll('li.officialDocuments');
const officialDocumentsBc = document.querySelectorAll('p.officialDocumentsBc');
const officialDocumentsCi = document.querySelectorAll('p.officialDocumentsCi');
// eslint-disable-next-line no-plusplus

for (let i = 0; i < collecters.length; i++) {
    officialDocuments[i].addEventListener('click', () => {
        officialDocumentsBc[i].classList.toggle('displayed');
        officialDocumentsCi[i].classList.toggle('displayed');
    });
}

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');
