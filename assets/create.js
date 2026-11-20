'use strict';

$(() => {
    const messages = $('body').data('messages');
    const $tv = $('#tv'), $fields = $('#episode, #title, #lang, #content');
    const viewUrl = $tv.data('view-url');
    let timer, originalQuery;
    function preview(query, lang = $('#lang').val()) {
        clearTimeout(timer);
        originalQuery = query;
        $tv.attr('lang', lang);
        $('#episode, #title, #content').attr('lang', lang);
        if (query) {
            $tv.attr('src', viewUrl + '?' + query);
            return;
        }
        const form = document.createElement('form');
        form.method = 'post';
        form.action = viewUrl;
        form.target = $tv.attr('name');
        form.hidden = true;
        for (const { name, value } of $fields.serializeArray()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.append(input);
        }
        document.body.append(form);
        form.submit();
        form.remove();
    }
    $fields.on('input change', () => {
        clearTimeout(timer);
        timer = setTimeout(() => preview(), 120);
    });
    $('.intro-form').on('reset', () => setTimeout(() => preview(), 0));
    $('#reload-tv').on('click', () => preview(originalQuery, $tv.attr('lang')));
    $('#show-originals').on('click', event => {
        event.preventDefault();
        clearTimeout(timer);
        $('#dialog-select').val('0');
        $('#show-originals-dialog')[0].showModal();
    });
    $('[data-close-dialog]').on('click', function () { $(this).closest('dialog')[0].close(); });
    $('#dialog-select').on('change', function () {
        if (this.value === '0') return;
        const [original, lang] = this.value.split('-');
        preview($.param({ original, lang }), lang);
        $('#show-originals-dialog')[0].close();
    });
    preview();

    const url = new URL(location.href), action = url.searchParams.get('action');
    const id = url.searchParams.get('id');
    if (action === 'edited' || action === 'share') {
        url.searchParams.delete('action');
        history.replaceState(null, '', url.pathname + url.search + url.hash);
    }
    if (action === 'edited') $('#action-feedback').text(messages.saved).prop('hidden', false);
    if (action === 'share' && id && $('body').hasClass('can-share')) {
        $('#share-dialog')[0].showModal();
        $('#share-btn').on('click', async () => {
            try {
                await navigator.share({ title: messages.shareTitle,
                    text: messages.shareText,
                    url: new URL(viewUrl + '?' + $.param({ id }), location.origin).href });
                $('#share-dialog')[0].close();
            } catch (error) {
                if (error.name !== 'AbortError') $('#action-feedback').text(messages.shareFailed).prop('hidden', false);
            }
        });
    }
});
