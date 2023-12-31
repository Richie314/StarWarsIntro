'use strict';

//
//  Remove context menu from images
//

const nav = document.querySelector('nav');
document.body.onscroll = () => {
    const nav_rect = nav.getBoundingClientRect();
    const pos = Math.floor(nav_rect.top - document.body.getBoundingClientRect().top);
    nav.classList.toggle('bg', pos > 5 - Math.floor(nav_rect.height));
}
// Discourage img copy by disabling right click
const images = [...document.querySelectorAll('.no-ctx > :is(img, svg, picture)')];
images.forEach(img => img.oncontextmenu = e => e.preventDefault());

//
//  Sharing checks
//

const current_folder = location.protocol + '//' + 
    location.hostname + location.pathname.split('/').filter((s, i) => i !== location.pathname.split('/').length - 1).join('/') + '/';
if (navigator.share && navigator.canShare({
    title: 'Intro di Star Wars personalizzata',
    text: 'Guarda la mia intro di Star Wars personalizzata!',
    url: current_folder + 'view.php?original=' + Math.floor(Math.random() * 9) + 1
}));

document.body.classList.add('can-share');

