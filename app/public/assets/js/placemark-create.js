(function ($) {
    'use strict';

    const SPINNER_HTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>';

    function parseCoord(value) {
        const parsed = parseFloat(value);
        return isNaN(parsed) ? null : parsed;
    }

    function setButtonLoading($btn, isLoading, idleLabel) {
        let $label = $btn.find('.btn-label');
        if (!$label.length) {
            $label = $btn;
        }

        if (isLoading) {
            $btn.prop('disabled', true);
            $label.html(SPINNER_HTML + 'Сохранение…');
            return;
        }

        $btn.prop('disabled', false);
        $label.text(idleLabel);
    }

    function apiErrorMessage(xhr, fallback) {
        if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors[0]) {
            return xhr.responseJSON.errors[0].message || fallback;
        }

        if (xhr.status === 404) {
            return 'API не найден (404). Проверьте, что RR запущен с .rr.dev.yaml (pool.debug: true).';
        }

        if (xhr.status) {
            return fallback + ' (HTTP ' + xhr.status + ')';
        }

        return fallback;
    }

    function initPlacemarkCreate() {
        const $mapEl = $('#placemark-map');
        if (!$mapEl.length) {
            return;
        }

        const centerLat = parseFloat($mapEl.data('center-lat')) || 59.94;
        const centerLon = parseFloat($mapEl.data('center-lon')) || 30.31;
        const apiCreateUrl = $mapEl.data('api-url');
        const apiBase = ($mapEl.data('api-base') || apiCreateUrl).replace(/\/$/, '');
        const initialPlacemarkers = $mapEl.data('initial-placemarkers') || [];

        let mapInstance = null;
        let draftMarker = null;
        const savedMarkersById = {};
        const placemarkersStore = {};
        let selectedTags = [];

        const $savedList = $('#placemark-saved-list');
        const $savedTemplate = $('#placemark-saved-card-template');
        const $newForm = $('#placemark-new-form');
        const editModalEl = document.getElementById('placemark-edit-modal');
        const editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;

        function updateEmptyMessage() {
            const hasCards = Object.keys(placemarkersStore).length > 0;
            $('.placemark-cards-empty').toggle(!hasCards);
        }

        function apiUrlForId(id) {
            return apiBase + '/' + id;
        }

        function getNewFormFields() {
            return {
                $name: $newForm.find('.placemark-name'),
                $lat: $newForm.find('.placemark-lat'),
                $lon: $newForm.find('.placemark-lon'),
                $type: $newForm.find('.placemark-type'),
                $description: $newForm.find('.placemark-description'),
                $save: $newForm.find('.placemark-save')
            };
        }

        function removeDraftMarker() {
            if (draftMarker && mapInstance) {
                mapInstance.geoObjects.remove(draftMarker);
                draftMarker = null;
            }
        }

        function updateDraftMarker(lat, lon, label) {
            if (!mapInstance || lat === null || lon === null) {
                return;
            }

            removeDraftMarker();
            draftMarker = new ymaps.GeoObject({
                geometry: {
                    type: 'Point',
                    coordinates: [lat, lon]
                },
                properties: {
                    iconContent: label || 'Новая метка'
                }
            }, {
                preset: 'islands#redStretchyIcon'
            });
            mapInstance.geoObjects.add(draftMarker);
        }

        function addMapMarker(id, lat, lon, name) {
            if (!mapInstance) {
                return;
            }

            const marker = new ymaps.GeoObject({
                geometry: {
                    type: 'Point',
                    coordinates: [lat, lon]
                },
                properties: {
                    iconContent: name
                }
            }, {
                preset: 'islands#greenStretchyIcon'
            });

            mapInstance.geoObjects.add(marker);
            savedMarkersById[id] = marker;
        }

        function updateMapMarkerLabel(id, name) {
            const marker = savedMarkersById[id];
            if (marker) {
                marker.properties.set('iconContent', name);
            }
        }

        function removeMapMarker(id) {
            const marker = savedMarkersById[id];
            if (marker && mapInstance) {
                mapInstance.geoObjects.remove(marker);
            }
            delete savedMarkersById[id];
        }

        function formatCoord(value) {
            const num = parseCoord(value);
            return num === null ? value : num.toPrecision(8);
        }

        function renderSavedCard(data) {
            const $col = $($savedTemplate.html().trim());
            const $card = $col.find('.placemark-saved-card');

            $card.attr('data-placemarker-id', data.id);
            $card.find('.placemark-saved-name').text(data.name).attr('title', data.name);
            $card.find('.placemark-saved-lat').text(formatCoord(data.lat)).attr('title', 'lat: ' + data.lat);
            $card.find('.placemark-saved-lon').text(formatCoord(data.lon)).attr('title', 'lon: ' + data.lon);

            $savedList.append($col);
            bindSavedCard($col, data.id);

            return $col;
        }

        function updateSavedCardUi(id) {
            const data = placemarkersStore[id];
            const $card = $savedList.find('[data-placemarker-id="' + id + '"]');

            if (!data || !$card.length) {
                return;
            }

            $card.find('.placemark-saved-name').text(data.name).attr('title', data.name);
            $card.find('.placemark-saved-lat').text(formatCoord(data.lat)).attr('title', 'lat: ' + data.lat);
            $card.find('.placemark-saved-lon').text(formatCoord(data.lon)).attr('title', 'lon: ' + data.lon);
        }

        function bindSavedCard($col, id) {
            const $card = $col.find('.placemark-saved-card');

            $card.on('click', function (event) {
                if ($(event.target).closest('.placemark-saved-remove').length) {
                    return;
                }
                openEditModal(id);
            });

            $card.on('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openEditModal(id);
                }
            });

            $col.find('.placemark-saved-remove').on('click', function (event) {
                event.stopPropagation();
                deletePlacemarker(id, $col);
            });
        }

        function openEditModal(id) {
            const data = placemarkersStore[id];
            if (!data || !editModal) {
                return;
            }

            $('#placemark-edit-id').val(data.id);
            $('#placemark-edit-name').val(data.name);
            $('#placemark-edit-lat').val(formatCoord(data.lat));
            $('#placemark-edit-lon').val(formatCoord(data.lon));
            $('#placemark-edit-type').val(data.type_id || 'default');
            $('#placemark-edit-description').val(data.description || '');
            editModal.show();
        }

        function renderSelectedTags() {
            const $container = $('#selected-tags-container');
            $container.empty();
            selectedTags.forEach(function(tagId) {
                const tagName = $('.tag-checkbox[value="' + tagId + '"]').data('name') || tagId;
                $container.append('<span class="badge bg-primary rounded-pill px-3 py-2">' + tagName + '</span>');
            });
        }

        function resetNewForm() {
            const fields = getNewFormFields();
            fields.$name.val('');
            fields.$lat.val('');
            fields.$lon.val('');
            if (fields.$type.length) fields.$type.val('default');
            fields.$description.val('');
            selectedTags = [];
            $('.tag-checkbox').prop('checked', false);
            renderSelectedTags();
            removeDraftMarker();
        }

        function saveNewPlacemark() {
            const fields = getNewFormFields();
            const name = fields.$name.val().trim();
            const lat = fields.$lat.val().trim();
            const lon = fields.$lon.val().trim();
            const type_id = fields.$type.val() || 'default';
            const description = fields.$description.val().trim();

            if (!name || !lat || !lon) {
                alert('Заполните название, широту и долготу.');
                return;
            }

            const latNum = parseCoord(lat);
            const lonNum = parseCoord(lon);
            if (latNum === null || lonNum === null) {
                alert('Некорректные координаты.');
                return;
            }

            setButtonLoading(fields.$save, true, 'Сохранить');

            $.ajax({
                type: 'POST',
                url: apiCreateUrl,
                contentType: 'application/json',
                data: JSON.stringify({
                    name: name,
                    lat: lat,
                    lon: lon,
                    type_id: type_id,
                    tags: selectedTags,
                    description: description
                })
            }).done(function (response) {
                placemarkersStore[response.id] = {
                    id: response.id,
                    name: response.name,
                    lat: response.lat,
                    lon: response.lon,
                    type_id: response.type_id || type_id,
                    description: response.description || ''
                };

                removeDraftMarker();
                addMapMarker(response.id, latNum, lonNum, response.name);
                renderSavedCard(placemarkersStore[response.id]);
                resetNewForm();
                updateEmptyMessage();
            }).fail(function (xhr) {
                alert(apiErrorMessage(xhr, 'Не удалось сохранить метку.'));
            }).always(function () {
                setButtonLoading(fields.$save, false, 'Сохранить');
            });
        }

        function saveEditPlacemark() {
            const id = $('#placemark-edit-id').val();
            const name = $('#placemark-edit-name').val().trim();
            const type_id = $('#placemark-edit-type').val();
            const description = $('#placemark-edit-description').val().trim();
            const $saveBtn = $('#placemark-edit-save');

            if (!id || !name) {
                alert('Укажите название метки.');
                return;
            }

            setButtonLoading($saveBtn, true, 'Сохранить');

            $.ajax({
                type: 'PUT',
                url: apiUrlForId(id),
                contentType: 'application/json',
                data: JSON.stringify({
                    name: name,
                    type_id: type_id,
                    description: description
                })
            }).done(function (response) {
                placemarkersStore[id] = {
                    id: response.id,
                    name: response.name,
                    lat: response.lat,
                    lon: response.lon,
                    type_id: response.type_id || type_id,
                    description: response.description || ''
                };

                updateMapMarkerLabel(id, response.name);
                updateSavedCardUi(id);
                editModal.hide();
            }).fail(function (xhr) {
                alert(apiErrorMessage(xhr, 'Не удалось обновить метку.'));
            }).always(function () {
                setButtonLoading($saveBtn, false, 'Сохранить');
            });
        }

        function deletePlacemarker(id, $col) {
            const data = placemarkersStore[id];
            if (!data) {
                return;
            }

            if (!window.confirm('Удалить метку «' + data.name + '»?')) {
                return;
            }

            $.ajax({
                type: 'DELETE',
                url: apiUrlForId(id)
            }).done(function () {
                removeMapMarker(id);
                delete placemarkersStore[id];
                $col.remove();
                updateEmptyMessage();
            }).fail(function (xhr) {
                alert(apiErrorMessage(xhr, 'Не удалось удалить метку.'));
            });
        }

        function bindNewForm() {
            const fields = getNewFormFields();

            fields.$name.on('input', function () {
                const lat = parseCoord(fields.$lat.val());
                const lon = parseCoord(fields.$lon.val());
                if (lat !== null && lon !== null) {
                    updateDraftMarker(lat, lon, $(this).val() || 'Новая метка');
                }
            });

            fields.$lat.add(fields.$lon).on('change', function () {
                const lat = parseCoord(fields.$lat.val());
                const lon = parseCoord(fields.$lon.val());
                updateDraftMarker(lat, lon, fields.$name.val() || 'Новая метка');
            });

            fields.$save.on('click', saveNewPlacemark);
            fields.$description.closest('form').on('submit', function (event) {
                event.preventDefault();
            });
        }

        $('#placemark-edit-save').on('click', saveEditPlacemark);
        $('#placemark-edit-form').on('submit', function (event) {
            event.preventDefault();
            saveEditPlacemark();
        });

        $('#save-tags-button').on('click', function() {
            selectedTags = [];
            $('.tag-checkbox:checked').each(function() {
                selectedTags.push($(this).val());
            });
            renderSelectedTags();
        });

        bindNewForm();

        const initMap = function () {
            mapInstance = new ymaps.Map('placemark-map', {
                center: [centerLat, centerLon],
                zoom: 15
            }, {
                searchControlProvider: 'yandex#search'
            });

            mapInstance.events.add('click', function (event) {
                const coords = event.get('coords');
                const fields = getNewFormFields();
                fields.$lat.val(coords[0].toPrecision(8));
                fields.$lon.val(coords[1].toPrecision(8));
                updateDraftMarker(coords[0], coords[1], fields.$name.val() || 'Новая метка');
            });

            if (window.bindYandexMapResize) {
                window.bindYandexMapResize(mapInstance, 'placemark-map');
            }

            // Render initial placemarkers from server
            if (Array.isArray(initialPlacemarkers)) {
                initialPlacemarkers.forEach(function (item) {
                    placemarkersStore[item.id] = {
                        id: item.id,
                        name: item.name,
                        lat: parseFloat(item.lat),
                        lon: parseFloat(item.lon),
                        type_id: item.type_id || 'default',
                        description: item.description || ''
                    };
                    addMapMarker(item.id, parseFloat(item.lat), parseFloat(item.lon), item.name);
                    renderSavedCard(placemarkersStore[item.id]);
                });
                updateEmptyMessage();
            }
        };

        if (window.whenYmapsReady) {
            window.whenYmapsReady(initMap);
        } else {
            window.console.error('ymap-resize.js must be loaded before placemark-create.js');
        }
    }

    $(initPlacemarkCreate);
}(jQuery));
