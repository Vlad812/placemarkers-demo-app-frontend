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

    function ensureTypeGroup(typeId, typeName) {
        let $group = $('.tags-type-group[data-type-id="' + typeId + '"]');
        if ($group.length) {
            return $group;
        }

        $('#empty-tags-row').remove();

        $group = $(
            '<div class="tags-type-group mb-4" data-type-id="' + typeId + '">' +
                '<h6 class="text-muted text-uppercase small fw-semibold mb-3"></h6>' +
                '<div class="table-responsive">' +
                    '<table class="table table-hover align-middle mb-0">' +
                        '<thead><tr><th>Название</th><th>Описание</th><th class="text-end">Действия</th></tr></thead>' +
                        '<tbody class="tags-type-group__body"></tbody>' +
                    '</table>' +
                '</div>' +
            '</div>'
        );
        $group.find('h6').text(typeName || typeId);
        $('#tags-grouped-list').prepend($group);

        return $group;
    }

    function initTags() {
        const $form = $('#tag-create-form');
        if (!$form.length) {
            return;
        }

        const $type = $('#tag-type');
        const $name = $('#tag-name');
        const $description = $('#tag-description');
        const $submitBtn = $form.find('button[type="submit"]');

        $form.on('submit', function (event) {
            event.preventDefault();

            const typeId = String($type.val() || '').trim();
            const typeName = $type.find('option:selected').text().trim();
            const nameVal = $name.val().trim();
            const descVal = $description.val().trim();

            if (!typeId) {
                alert('Выберите тип метки');
                return;
            }

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
                    type_id: typeId,
                    name: nameVal,
                    description: descVal
                })
            }).done(function (response) {
                $name.val('');
                $description.val('');

                const $tr = $('<tr>').attr('data-tag-id', response.id || '');
                $tr.append($('<td>').append(
                    $('<span class="badge bg-primary rounded-pill px-3 py-2 fw-normal"></span>').text(response.name)
                ));
                $tr.append($('<td>').text(response.description || ''));
                $tr.append($('<td class="text-end">').html('<button class="btn btn-sm btn-outline-danger" disabled title="Удаление пока не реализовано">Удалить</button>'));

                ensureTypeGroup(response.type_id || typeId, typeName)
                    .find('.tags-type-group__body')
                    .prepend($tr);
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
