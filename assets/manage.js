'use strict';

$(() => {
    const messages = $('body').data('messages');
    $(document).on('click', '[data-delete-url]', function (event) {
        event.preventDefault();
        const $button = $(this), data = $button.data();
        if ($button.attr('aria-disabled') === 'true') return;
        $button.attr('aria-disabled', 'true').prop('disabled', true);
        const failure = () => {
            $button.removeAttr('aria-disabled').prop('disabled', false);
            $('#action-feedback').text(messages.deleteFailed).prop('hidden', false);
        };
        $.post(data.deleteUrl, { [data.deleteField]: data.deleteId }, null, 'json')
            .done(result => {
                if (result.esit !== 'ok') return failure();
                $('#action-feedback').prop('hidden', true);
                $(document.getElementById(data.deleteRow)).fadeOut(180, function () {
                    $(this).remove();
                    const count = $('[data-intro-card]').length;
                    $('[data-intro-count]').text(count);
                    $('#intro-empty').prop('hidden', count !== 0);
                });
            })
            .fail(failure);
    });
});
