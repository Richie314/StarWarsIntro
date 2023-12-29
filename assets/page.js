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
//  Handle TV in create page
//

/**
 * @type {HTMLIFrameElement}
 */
const tv = document.getElementById('tv');
/**
 * @type {HTMLInputElement}
 */
const episodeInput = document.getElementById('episode');
/**
 * @type {HTMLInputElement}
 */
const titleInput = document.getElementById('title');
/**
 * @type {HTMLSelectElement}
 */
const langInput = document.getElementById('lang');
/**
 * @type {HTMLTextAreaElement}
 */
const contentInput = document.getElementById('content');

function UpdateTvSrc()
{
    const base = './view.php?';
    const params =  new URLSearchParams({
        'episode': episodeInput.value.trim(),
        'title': titleInput.value.trim(),
        'lang': langInput.value.trim(),
        'content': contentInput.value.trim()
    });
    const url = base + params.toString();

    tv.blur(); // Remove focus from the element
    tv.lang = langInput.value;
    if (tv.src !== url)
        tv.src = url; // Load the new page if it's different
}
var UpdateTimeoutId = 0;
function ScheduleTvUpdate()
{
    ClearTvUpdate();
    UpdateTimeoutId = setTimeout(UpdateTvSrc, 100);
}
function ClearTvUpdate()
{
    if (UpdateTimeoutId !== 0)
    {
        clearTimeout(UpdateTimeoutId);
    }
}
if (tv && episodeInput && titleInput && langInput && contentInput) {
    tv.onload = ClearTvUpdate;
    episodeInput.addEventListener('input', ScheduleTvUpdate);
    titleInput.addEventListener('input', ScheduleTvUpdate);
    langInput.addEventListener('input', ScheduleTvUpdate);
    contentInput.addEventListener('input', ScheduleTvUpdate);
    ScheduleTvUpdate();
}