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
const images = [...document.querySelectorAll('.no-ctx > img, .no-ctx > svg, .no-ctx > picture')];
images.forEach(img => img.oncontextmenu = e => e.preventDefault());

//
//  Emulate css's aspect-ratio if missing
//
if (!CSS.supports('aspect-ratio', '1 / 1'))
{
    /**
     * @type {HTMLElement[]}
     */
    const targetsX = [...document.querySelectorAll('[data-aspect-ratio]')];
    targetsX.forEach(elem => elem.style.height = String(elem.clientWidth / Number(elem.getAttribute('data-aspect-ratio'))) + 'px');
    window.addEventListener('resize', () => {
        targetsX.forEach(elem => {
            try {
                elem.style.height = String(elem.clientWidth / Number(elem.getAttribute('data-aspect-ratio'))) + 'px';
            } catch (err) {
                console.warn(err);
            }
        });
    });
    /**
     * @type {HTMLElement[]}
     */
    const targetsY = [...document.querySelectorAll('[data-inverse-aspect-ratio]')];
    targetsY.forEach(elem => elem.style.width = String(elem.clientHeight * Number(elem.getAttribute('data-inverse-aspect-ratio'))) + 'px');
    window.addEventListener('resize', () => {
        targetsY.forEach(elem => {
            try {
                elem.style.width = String(elem.clientHeight * Number(elem.getAttribute('data-inverse-aspect-ratio'))) + 'px';
            } catch (err) {
                console.warn(err);
            }
        });
    });
}

//
//  Sharing checks
//

const current_folder = location.protocol + '//' + 
    location.hostname + location.pathname.split('/').filter((s, i) => i !== location.pathname.split('/').length - 1).join('/') + '/';
if (navigator.share && navigator.canShare({
    title: 'Intro di Star Wars personalizzata',
    text: 'Guarda la mia intro di Star Wars personalizzata!',
    url: current_folder + 'view.php?original=' + Math.floor(Math.random() * 9) + 1
}))
    document.body.classList.add('can-share');

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
    if (!url.hash || url.hash.length <= 1 || url.pathname !== '')
        return;
    a.onclick = e => {
        e.preventDefault();
        const target = document.getElementById(url.hash.substring(1));
        target.scrollIntoView();
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
async function post(path, params = null)
{
    async function post_async(path, params)
    {

        if (params)
        {
            const form_data = new FormData();
            for (const [name, value] of Object.entries(params))
            {
                form_data.append(name, value);
            }
            return await fetch(path, {
                method: 'POST',
                body: form_data
            });
        }
        return await fetch(path, {
            method: 'POST'
        });
    }
    try {
		const response = await post_async(path, params);
		if (!response.ok)
		{
			return {};
		}
		return await response.json();
	} catch (err) {
		console.warn(err);
		return {};
	}
}
/**
 * Calls post and returns the 'esit' parameter of the response 
 * @param {string} path 
 * @param {object} params 
 * @returns {string} The esit parameter
 */
async function ajax(params = null, path = './ajax.php')
{
    const res = await post(path, params);
    if (!res || !('esit' in res))
    {
        return 'Unknown';
    }
    return res['esit'];
}