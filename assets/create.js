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
/**
 * @type {HTMLButtonElement}
 */
const clearBtn = document.getElementById('clear');
/**
 * @type {HTMLButtonElement}
 */
const submitBtn = document.getElementById('submit');

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

/**
 * @type {HTMLDialogElement}
 */
const dialog = document.getElementById('show-originals-dialog');
/**
 * @type {HTMLSelectElement}
 */
const dialogSelect = document.getElementById('dialog-select');
/**
 * @type {HTMLButtonElement}
 */
const dialogCloseBtn = document.getElementById('dialog-close');


function ShowOriginals()
{
    episodeInput.disabled = titleInput.disabled = langInput.disabled = contentInput.disabled = true;
    clearBtn.disabled = submitBtn.disabled = true;
    ClearTvUpdate();
    dialog.showModal();
}
function CloseDialog()
{
    dialog.close();
    episodeInput.disabled = titleInput.disabled = langInput.disabled = contentInput.disabled = false;
    clearBtn.disabled = submitBtn.disabled = false;
}
/**
 * @param {string} episode 
 * @param {string} lang 
 */
function ShowOriginal(episode, lang)
{
    if (!episode || episode.length === 0 || !lang || lang.length === 0)
        return;
    const base = './view.php?';
    const params =  new URLSearchParams({
        'original': episode,
        'lang': lang
    });
    const url = base + params.toString();

    tv.blur(); // Remove focus from the element
    tv.lang = langInput.value;
    if (tv.src !== url)
        tv.src = url; // Load the new page if it's different
}
if (dialogSelect) {
    dialogSelect.addEventListener('change', () => {
        const value = dialogSelect.value;
        if (value === '0')
            return;
        const [number, lang] = value.split('-');
        CloseDialog();
        ShowOriginal(number, lang);
    })
}
if (dialogCloseBtn) {
    dialogCloseBtn.onclick = CloseDialog;
}