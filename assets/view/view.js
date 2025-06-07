'use strict';
// Prevent contextmenu from getting in the way
document.oncontextmenu = evt => evt.preventDefault();

// Shorthand
const Now = () => performance.now();

// Delay for audio start after the start of the animations
const AUDIO_START_DELAY = 6300;

// Get rendered lines of the content
function ContentLength() {
    const element = document.getElementById('content');
    const totalHeight = element.getBoundingClientRect().height;

    const range = document.createRange();
    range.selectNodeContents(element);
    const p = document.createElement("p");
    p.className = 'paragraph';
    p.textContent = "A";
    element.appendChild(p);
    const singleLineHeight = p.getBoundingClientRect().height;
    element.removeChild(p);


    const lines =  Math.round(totalHeight / singleLineHeight);
    if (isNaN(lines) || lines < 0)
    {
        return 0;
    }
    return lines;
}

// Delay for credits to be shown
const CREDITS_SHOW_DELAY = 105 * 1000;

// When animations start
var start = 0;

// Generate the audio as we want it
function CreateAudio() {
    const audio = new Audio('./assets/intro.mp3');
    audio.autoplay = false;
    audio.muted = false;
    audio.preload = 'auto';
    return audio;
};
const audio = CreateAudio();

// Function that starts the animations
function StartPage() {
    start = Now();
    document.body.classList.remove('wait'); // Start animations
    StartAudio();
    const credits = document.getElementById('credits');
    if (!credits) {
        return;
    }
    function show_credits() {
        credits.classList.remove('hidden');
        credits.style.userSelect = 'text';
        setTimeout(() => credits.style.opacity = 1, 1000);
    }
    setTimeout(() => {
        const additional_delay = Math.sqrt(ContentLength() + 10) * 2100;
        if (!isNaN(additional_delay) && additional_delay > 0)
        {
            setTimeout(show_credits, additional_delay);
        } else {
            show_credits();
        }
    }, CREDITS_SHOW_DELAY);
}

// Starts the audio.
// automatically detects offsets
function StartAudio() {
    if (!audio.paused)
        return; // Do nothing if the audio is playing

    // Time passed since animation start - the offset: put as currentTime of the audio
    const deltaT = Now() - start - AUDIO_START_DELAY;
    if (deltaT < 0) {
        // Schedule the start
        setTimeout(StartAudio, -deltaT);
        return;
    }
    // Audio should already been playing. Sync it to the desired currentTime

    // If the audio should have ended don't start it
    if (deltaT >= audio.duration * 1000)
    {
        return;
    }
    
    // Set the current time and play it
    audio.currentTime = deltaT / 1000;
    if (!audio.paused) {
        // Already playing
        return;
    }
    audio.play().then(
        () => console.log('Audio started')).catch(
        () => console.warn('Audio will start when the user clicks on the page'));
}

// Check for load of both css and audio
var audioLoaded = false;
var pageStyleLoaded = false;
var pageAnimationsLoaded = false;
function CheckLoad() {
    if (!audioLoaded || !pageStyleLoaded || !pageAnimationsLoaded)
        return;
    StartPage();
}
function AudioLoaded() {
    audioLoaded = true;
    CheckLoad();
}
function PageStyleLoaded() {
    pageStyleLoaded = true;
    CheckLoad();
}
function PageAnimationsLoaded() {
    pageAnimationsLoaded = true;
    CheckLoad();
}

audio.addEventListener('loadeddata', () => AudioLoaded());
document.addEventListener('click', () => StartAudio());

// Hide cursor when it does not move for more than 2s
document.addEventListener('mousemove', () => {
    document.body.classList.remove('no-cursor');
    if (hideMouseTimeout !== 0) {
        clearTimeout(hideMouseTimeout);
    }
    hideMouseTimeout = setTimeout(() => {
        document.body.classList.add('no-cursor');
        hideMouseTimeout = 0;
    }, 2000);
});
var hideMouseTimeout = setTimeout(() => {
    document.body.classList.add('no-cursor');
    hideMouseTimeout = 0;
}, 3000);