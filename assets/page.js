'use strict';

$(() => {
    const $nav = $('nav');
    $(document).on('scroll', () => $nav.toggleClass('bg', $(document).scrollTop() > 5));
    $('.no-ctx').on('contextmenu', 'img, svg, picture', event => event.preventDefault());
    const sample = new URL($('body').data('view-url'), location.origin);
    sample.searchParams.set('original', '1');
    $('body').toggleClass('can-share', !!(navigator.share && navigator.canShare?.({ url: sample.href })));
    $('#accept-cookie')[0]?.showModal();

    function scrollToHash(hash) {
        document.getElementById(decodeURIComponent(hash.slice(1)))?.scrollIntoView({ behavior: 'smooth' });
    }
    if (location.hash) scrollToHash(location.hash);
    $('a[href*="#"]').on('click', function (event) {
        const url = new URL(this.href);
        if (url.hash && url.origin === location.origin && url.pathname === location.pathname &&
            !$(this).is('#show-originals')) {
            event.preventDefault();
            scrollToHash(url.hash);
        }
    });
});
