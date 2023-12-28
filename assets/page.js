'use strict';
const nav = document.querySelector('nav');
document.body.onscroll = () => {
    const nav_rect = nav.getBoundingClientRect();
    const pos = Math.floor(nav_rect.top - document.body.getBoundingClientRect().top);
    nav.classList.toggle('bg', pos > 5 - Math.floor(nav_rect.height));
}
// Discourage img copy by disabling right click
const images = [...document.querySelectorAll('.no-ctx > :is(img, svg, picture)')];
images.forEach(img => img.oncontextmenu = e => e.preventDefault());