/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

require('bootstrap');

// any CSS you require will output into a single css file (app.css in this case)
require('../scss/faq.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
require('jquery');

const question = document.querySelectorAll("article.question");
const answer = document.querySelectorAll("article.answer");

for (let i=0; i< question.length; question [i++]) {
    question[i].addEventListener('click', function () {
        answer[i].classList.toggle('displayed');
    })
}
