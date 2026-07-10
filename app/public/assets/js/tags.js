(function ($) {
    'use strict';

    const SPINNER_HTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>';

    function setButtonLoading($btn, isLoading, idleLabel) {
        let $label = $btn.find('.btn-label');
        if (!$label.length) {
            $label = $btn;
        }

        if (isLoading) {
            $btn.prop('disabled', true);
            $label.html(SPINNER_HTML + 'Добавление…');
            return;
        }

        $btn.prop('disabled', false);
        $label.text(idleLabel);
    }

    function initTags() {
        const $form = $('#tag-create-form');
        if (!$form.length) {
            return;
        }

        const $name = $('#tag-name');
        const $description = $('#tag-description');
        const $submitBtn = $form.find('button[type="submit"]');

        $form.on('submit', function (event) {
            event.preventDefault();

            const nameVal = $name.val().trim();
            const descVal = $description.val().trim();

            if (!nameVal) {
                alert('Введите название тега');
                return;
            }

            setButtonLoading($submitBtn, true, 'Добавить');

            $.ajax({
                type: 'POST',
                url: '/api/tags',
                contentType: 'application/json',
                data: JSON.stringify({
                    name: nameVal,
                    description: descVal
                })
            }).done(function (response) {
                // Clear the form
                $name.val('');
                $description.val('');

                // Render the new tag immediately (basic layout)
                const $tr = $('<tr>');
                $tr.append($('<td>').html('<span class="badge bg-primary rounded-pill px-3 py-2 fw-normal">' + response.name + '</span>'));
                $tr.append($('<td>').text(response.description || ''));
                $tr.append($('<td class="text-end">').html('<button class="btn btn-sm btn-outline-danger" disabled title="Удаление пока не реализовано">Удалить</button>'));

                // Remove empty state message if it exists
                const $emptyTr = $('#empty-tags-row');
                if ($emptyTr.length) {
                    $emptyTr.remove();
                }

                $('#tags-list').prepend($tr);
            }).fail(function (xhr) {
                let message = 'Не удалось создать тег.';
                if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors[0]) {
                    message = xhr.responseJSON.errors[0].message;
                }
                alert(message);
            }).always(function () {
                setButtonLoading($submitBtn, false, 'Добавить');
            });
        });
    }

    $(initTags);
}(jQuery));
