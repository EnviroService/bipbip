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
        // rafaraichir pour avoir un blanc lors de la frappe
        $('.resultSearch').html('');
        // recuperation des données
        let users = $(this).val();
        // eslint-disable-next-line eqeqeq
        if (users !== "") {
            // animation et recuperation des données // rafraichir un bout de page
            $.ajax({
                type: 'GET',
                // url pour raffraichir les données
                url: "home",
                //encoder la variable pour quelle ne soit pas en claire(secu)
                data: 'users=' + encodeURIComponent(users),
                // data valeur par def d'ajax données recup du resultat erreor ou succes.
                success: function (data) {
                    if (data !== "") {
                        // afficher les données $data
                        let names = "";
                        for (i = 0; i < data.length; i ++) {
                            names += `<a href="user/${data[i]['id']}/documents">${data[i]['lastname']} ${data[i]['firstname']}</a>`;
                        };
                        //const names = JSON.parse(data);

                        //for(){
                        document.querySelector('.resultSearch').innerHTML += names + ' ';
                        //}
                    } else {
                        //meme chose pour recup un element d'un id
                        document.querySelector('.resultSearch').innerHTML +="Aucunes recherches trouvées"
                    }
                }
            });
        }
    })
});
