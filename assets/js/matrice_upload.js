const matrice = document.querySelector('#matrice_matrice_field');
const label = document.querySelector('.custom-file-label');
function uploadMatrice()
{
    matrice.placeholder = matrice.value;
    label.innerHTML = matrice.value.replace('C:\\fakepath\\','');
};

matrice.addEventListener('input', uploadMatrice);


