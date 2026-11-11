'use strict';

document.querySelectorAll('[data-delete-url]').forEach(link => {
    link.addEventListener('click', async event => {
        event.preventDefault();
        const result = await post(link.dataset.deleteUrl, {
            [link.dataset.deleteField]: link.dataset.deleteId
        });
        if (result.esit === 'ok') {
            const row = document.getElementById(link.dataset.deleteRow);
            if (row) {
                row.classList.add('fade-out');
                setTimeout(() => row.remove(), 1000);
            }
        }
    });
});
