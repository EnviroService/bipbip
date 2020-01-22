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

for (let i = 0; i < categories.length; categories[i += 1]) {
    categories[i].addEventListener('click', () => {
        subCategories[i].classList.toggle('displayed');
    });
}
$(document).ready(function () {
    $('#search_nameSearch').keyup(function () {
        $('.resultSearch').html('');
        const users = $(this).val();
        if (users !== '') {
            $.ajax({
                type: 'GET',
                url: 'home',
                data: 'users=' + encodeURIComponent(users),
                success: function (data) {
                    if (data !== '') {
                        let names = '';
                        for (let i = 0; i < data.length; i += 1) {
                            names += `<a href="user/${data[i]["id"]}/documents">${data[i]["lastname"]} ${data[i]["firstname"]}</a>`;
                        }
                        document.querySelector('.resultSearch').innerHTML += names + ' ';
                    } else {
                        document.querySelector('.resultSearch').innerHTML += 'Aucunes recherches trouvées';
                    }
                },
            });
        }
    });
});
