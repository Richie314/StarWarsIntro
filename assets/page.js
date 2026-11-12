'use strict';

$(() => {
    $('html').addClass('js');
    const $toggle = $('#nav-toggle');
    function menu(open) {
        $toggle.attr({ 'aria-expanded': String(open),
            'aria-label': $toggle.data(open ? 'close-label' : 'open-label') });
        $('#nav-links').toggleClass('is-open', open);
    }
    $toggle.on('click', () => menu($toggle.attr('aria-expanded') !== 'true'));
    $(document).on('keydown', event => {
        if (event.key === 'Escape' && $toggle.attr('aria-expanded') === 'true') {
            menu(false);
            $toggle.trigger('focus');
        }
    });
    $('.language-switch').on('submit', function () {
        $(this).find('[name=return_to]').val(location.pathname + location.search + location.hash);
    });
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
