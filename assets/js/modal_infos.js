const bubbleInfoImg = document.querySelectorAll('img.bubble-info-img');
const bubbleInfoTxt = document.querySelectorAll('p.bubble-info-txt');
const bubbleInfoBg = document.querySelectorAll('div.bubble-info-bg');

// eslint-disable-next-line no-plusplus
for (let i = 0; i < bubbleInfoImg.length; i++) {
    bubbleInfoImg[i].addEventListener('click', () => {
        bubbleInfoTxt[i].classList.toggle('displayed');
        bubbleInfoBg[i].classList.toggle('displayed');
    });
}

// eslint-disable-next-line no-plusplus
for (let i = 0; i < bubbleInfoBg.length; i++) {
    bubbleInfoBg[i].addEventListener('click', () => {
        bubbleInfoTxt[i].classList.toggle('displayed');
        bubbleInfoBg[i].classList.toggle('displayed');
    });
}
