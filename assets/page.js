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

const sampleUrl = new URL(document.body.dataset.viewUrl, location.origin);
sampleUrl.searchParams.set('original', String(Math.floor(Math.random() * 9) + 1));
if (navigator.share && navigator.canShare && navigator.canShare({ url: sampleUrl.href })) {
    document.body.classList.add('can-share');
}

//
//  Handle automatic scroll on page load
//

function ScrollFragment()
{
    const frag = location.hash;
    if (!frag || frag.length <= 1)
        return;
    const elem = document.getElementById(frag.substring(1));
    if (!elem)
        return;
    elem.scrollIntoView({
        behavior: 'smooth'
    });
}
ScrollFragment();

//
//  Handle in-page links (# links)
//

const inPageAnchors = document.querySelectorAll('a[href*=\'#\']');
inPageAnchors.forEach(a => {
    const url = new URL(a.href);
    if (!url.hash || url.hash.length <= 1 || url.pathname !== location.pathname || url.origin !== location.origin)
        return;
    a.onclick = e => {
        e.preventDefault();
        const target = document.getElementById(url.hash.substring(1));
        target?.scrollIntoView();
    }
});

//
//  Simple POST fetch wrapper
//
/**
 * @param {string} path the path to send the post request to
 * @param {object} params the parameters to add to the url
 * @returns {object}
 */
async function post(path, params = {}) {
    const body = new FormData();
    for (const [name, value] of Object.entries(params)) {
        body.append(name, value);
    }
    try {
        const response = await fetch(path, { method: 'POST', body });
        return response.ok ? await response.json() : {};
    } catch (error) {
        console.warn(error);
        return {};
    }
}
