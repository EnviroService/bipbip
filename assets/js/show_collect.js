
const showCollect = document.querySelectorAll('a.choice_collect');
for (let i = 0; i < showCollect.length; i += 1) {
    showCollect[i].addEventListener('click', (e) => {
        if (confirm('Es-tu sûr de vouloir participer à cette collecte ?')) {
        } else {
            e.preventDefault();
        }
    });
}
