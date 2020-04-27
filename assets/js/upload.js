const logo = document.querySelector('#organisms_logo');
const label = document.querySelector('.custom-file-label');
function uploadMe()
{
    logo.placeholder = logo.value;
    label.innerHTML = logo.value.replace('C:\\fakepath\\','');
};

logo.addEventListener('input', uploadMe);
