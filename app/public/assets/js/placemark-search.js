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

            $label.html(SPINNER_HTML + idleLabel + '…');

            return;

        }



        $btn.prop('disabled', false);

        $label.text(idleLabel);

    }



    function pluralizeMetki(count) {

        const abs = Math.abs(count) % 100;

        const lastDigit = abs % 10;



        if (abs > 10 && abs < 20) {

            return 'меток';

        }



        if (lastDigit > 1 && lastDigit < 5) {

            return 'метки';

        }



        if (lastDigit === 1) {

            return 'метка';

        }



        return 'меток';

    }



    function initPlacemarkSearch() {

        const $mapEl = $('#search-map');

        if (!$mapEl.length) {

            return;

        }



        const centerLat = parseFloat($mapEl.data('center-lat')) || 59.94;

        const centerLon = parseFloat($mapEl.data('center-lon')) || 30.31;

        const defaultRadius = parseInt($mapEl.data('default-radius'), 10) || 1000;

        const apiUrl = $mapEl.data('api-url');

        const getUrlTemplate = $mapEl.data('get-url');
        const collectionsUrl = $mapEl.data('collections-url');
        const saveUrl = $mapEl.data('save-url');

        let mapInstance = null;
        let circleHandler = null;
        let searchMarkers = [];
        let lastSearchResults = [];
        let lastSearchCriteria = null;
        let userCollections = [];

        const $searchBtn = $('#search-button');
        const $saveBtn = $('#save-search-button');
        const $showCollectionsBtn = $('#show-collections-button');
        const $collectionsCountBadge = $('#collections-count-badge');
        const $resultsList = $('#search-results-list');
        const $emptyMsg = $('.search-results-empty');
        const $cardTemplate = $('#placemark-saved-card-template');
        const $resultsCount = $('#search-results-count');
        const $resultsCountValue = $('#search-results-count-value');
        const $resultsCountLabel = $('#search-results-count-label');
        
        const $saveModalEl = $('#save-search-modal');
        const $collectionNameInput = $('#collection-name-input');
        const $saveConfirmBtn = $('#save-search-confirm-button');
        const $saveError = $('#save-search-error');
        let saveModal = null;

        const $collectionsModalEl = $('#collections-list-modal');
        const $collectionsTbody = $('#collections-list-tbody');
        let collectionsModal = null;

        const $tagsFilterBtn = $('#search-tags-filter-button');
        const $typesFilterBtn = $('#search-types-filter-button');
        const $tagsModalEl = $('#search-tags-modal');
        const $typesModalEl = $('#search-types-modal');
        const $selectedTagsContainer = $('#search-selected-tags-container');
        const $selectedTypesContainer = $('#search-selected-types-container');
        const $filterToolbar = $('#search-filter-toolbar');
        const $filtersResetBtn = $('#search-filters-reset-button');
        const $tagCheckboxes = $('.search-tag-checkbox');
        const $typeCheckboxes = $('.search-type-checkbox');
        let tagsModal = null;
        let typesModal = null;
        let infoModal = null;
        let selectedTags = [];
        let selectedTypes = [];
        let activeInfoRequest = null;

        const $infoModalEl = $('#placemark-info-modal');
        const $infoContent = $('#placemark-info-content');
        const $infoName = $('#placemark-info-name');
        const $infoLat = $('#placemark-info-lat');
        const $infoLon = $('#placemark-info-lon');
        const $infoType = $('#placemark-info-type');
        const $infoTags = $('#placemark-info-tags');
        const $infoTagsEmpty = $('#placemark-info-tags-empty');
        const $infoDescription = $('#placemark-info-description');
        const $infoCreatedAt = $('#placemark-info-created-at');
        const $infoError = $('#placemark-info-error');
        const $infoLoading = $('#placemark-info-loading');

        if ($saveModalEl.length && window.bootstrap) {
            saveModal = new window.bootstrap.Modal($saveModalEl[0]);
        }
        if ($collectionsModalEl.length && window.bootstrap) {
            collectionsModal = new window.bootstrap.Modal($collectionsModalEl[0]);
        }
        if ($tagsModalEl.length && window.bootstrap) {
            tagsModal = new window.bootstrap.Modal($tagsModalEl[0]);
        }
        if ($typesModalEl.length && window.bootstrap) {
            typesModal = new window.bootstrap.Modal($typesModalEl[0]);
        }
        if ($infoModalEl.length && window.bootstrap) {
            infoModal = new window.bootstrap.Modal($infoModalEl[0]);
        }



        function formatCoord(value) {
            const parsed = parseFloat(value);
            return isNaN(parsed) ? String(value) : parsed.toPrecision(8);
        }

        function buildTagNameMap() {
            const map = {};

            $tagCheckboxes.each(function () {
                map[$(this).val()] = $(this).data('name');
            });

            return map;
        }

        function buildTypeLabelMap() {
            const map = {};

            $typeCheckboxes.each(function () {
                map[$(this).val()] = $(this).data('label');
            });

            return map;
        }

        function formatCreatedAt(value) {
            if (!value) {
                return '—';
            }

            const date = new Date(value);

            if (isNaN(date.getTime())) {
                return value;
            }

            return date.toLocaleString('ru-RU', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getPlacemarkerUrl(id) {
            if (!getUrlTemplate) {
                return '';
            }

            return String(getUrlTemplate).replace('__ID__', encodeURIComponent(id));
        }

        function clearInfoModal() {
            $infoName.text('');
            $infoLat.text('');
            $infoLon.text('');
            $infoType.text('');
            $infoTags.empty();
            $infoTagsEmpty.prop('hidden', true);
            $infoDescription.text('').removeClass('placemark-info-description--empty');
            $infoCreatedAt.text('');
            $infoError.prop('hidden', true).text('');
            $infoLoading.prop('hidden', true);
            $infoContent.prop('hidden', true);
        }

        function setInfoModalLoading(isLoading) {
            $infoLoading.prop('hidden', !isLoading);

            if (isLoading) {
                $infoContent.prop('hidden', true);
                $infoError.prop('hidden', true);
            }
        }

        function setInfoModalContentVisible(isVisible) {
            $infoContent.prop('hidden', !isVisible);
        }

        function renderInfoTags(tagIds) {
            const tagNameMap = buildTagNameMap();
            const tags = Array.isArray(tagIds) ? tagIds : [];

            $infoTags.empty();

            if (tags.length === 0) {
                $infoTagsEmpty.prop('hidden', false);
                return;
            }

            $infoTagsEmpty.prop('hidden', true);

            tags.forEach(function (tagId) {
                const label = tagNameMap[tagId] || tagId;
                $('<span class="placemark-info-tag"></span>').text(label).appendTo($infoTags);
            });
        }

        function fillInfoModal(data) {
            const typeLabelMap = buildTypeLabelMap();
            const typeLabel = typeLabelMap[data.type_id] || data.type_id || 'Не указан';
            const createdAtLabel = formatCreatedAt(data.created_at);
            const description = (data.description || '').trim();

            $infoName.text(data.name || 'Без названия');
            $infoLat.text(formatCoord(data.lat));
            $infoLon.text(formatCoord(data.lon));
            $infoType.text(typeLabel);
            renderInfoTags(data.tags);

            if (description) {
                $infoDescription
                    .text(description)
                    .removeClass('placemark-info-description--empty');
            } else {
                $infoDescription
                    .text('Описание не указано')
                    .addClass('placemark-info-description--empty');
            }

            $infoCreatedAt.text(createdAtLabel);
        }

        function showInfoError(message) {
            $infoContent.prop('hidden', true);
            $infoError.text(message).prop('hidden', false);
        }

        function openInfoModal(id) {
            if (!id || !getUrlTemplate) {
                return;
            }

            if (activeInfoRequest) {
                activeInfoRequest.abort();
                activeInfoRequest = null;
            }

            clearInfoModal();
            setInfoModalLoading(true);

            if (infoModal) {
                infoModal.show();
            }

            activeInfoRequest = $.ajax({
                url: getPlacemarkerUrl(id),
                method: 'GET'
            }).done(function (data) {
                const placemarker = data && data.id ? data : (data && data.data ? data.data : null);

                if (!placemarker) {
                    showInfoError('Не удалось загрузить данные метки.');
                    return;
                }

                fillInfoModal(placemarker);
                setInfoModalContentVisible(true);
            }).fail(function (xhr) {
                let message = 'Не удалось загрузить информацию о метке.';

                if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors[0]) {
                    message = xhr.responseJSON.errors[0].message || message;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                showInfoError(message);
            }).always(function () {
                activeInfoRequest = null;
                setInfoModalLoading(false);
            });
        }

        function bindResultCard($card, id) {
            $card.attr('data-placemarker-id', id);
            $card.addClass('placemark-saved-card--interactive');

            $card.on('click', function () {
                openInfoModal(id);
            });

            $card.on('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openInfoModal(id);
                }
            });
        }



        function getSelectedTags() {
            return $tagCheckboxes.filter(':checked').map(function () {
                return {
                    id: $(this).val(),
                    name: $(this).data('name')
                };
            }).get();
        }

        function getSelectedTypes() {
            return $typeCheckboxes.filter(':checked').map(function () {
                return {
                    id: $(this).val(),
                    label: $(this).data('label')
                };
            }).get();
        }

        function renderFilterChip(label, typeClass, onRemove) {
            const $chip = $('<span class="search-filter-chip ' + typeClass + '"></span>');
            const $text = $('<span class="search-filter-chip__text"></span>').text(label).attr('title', label);
            const $remove = $('<button type="button" class="search-filter-chip__remove" aria-label="Убрать">&times;</button>');

            $remove.on('click', onRemove);
            $chip.append($text).append($remove);

            return $chip;
        }

        function updateFilterToolbarState() {
            const hasFilters = selectedTags.length > 0 || selectedTypes.length > 0;

            $filterToolbar.toggleClass('search-filter-toolbar--active', hasFilters);
            $filtersResetBtn.prop('disabled', !hasFilters);
            $selectedTagsContainer.closest('.search-filter-group').toggleClass('search-filter-group--active', selectedTags.length > 0);
            $selectedTypesContainer.closest('.search-filter-group').toggleClass('search-filter-group--active', selectedTypes.length > 0);
        }

        function renderSelectedTags() {
            $selectedTagsContainer.empty();

            selectedTags.forEach(function (tag) {
                const $chip = renderFilterChip(tag.name, 'search-filter-chip--tag', function () {
                    $tagCheckboxes.filter('[value="' + tag.id + '"]').prop('checked', false);
                    selectedTags = selectedTags.filter(function (item) {
                        return item.id !== tag.id;
                    });
                    renderSelectedTags();
                    updateFilterToolbarState();
                });
                $selectedTagsContainer.append($chip);
            });

            updateFilterToolbarState();
        }

        function renderSelectedTypes() {
            $selectedTypesContainer.empty();

            selectedTypes.forEach(function (type) {
                const $chip = renderFilterChip(type.label, 'search-filter-chip--type', function () {
                    $typeCheckboxes.filter('[value="' + type.id + '"]').prop('checked', false);
                    selectedTypes = selectedTypes.filter(function (item) {
                        return item.id !== type.id;
                    });
                    renderSelectedTypes();
                    updateFilterToolbarState();
                });
                $selectedTypesContainer.append($chip);
            });

            updateFilterToolbarState();
        }

        function applyTagsFilter() {
            selectedTags = getSelectedTags();
            renderSelectedTags();
        }

        function applyTypesFilter() {
            selectedTypes = getSelectedTypes();
            renderSelectedTypes();
        }

        function clearTagsFilter() {
            $tagCheckboxes.prop('checked', false);
            selectedTags = [];
            renderSelectedTags();
        }

        function clearTypesFilter() {
            $typeCheckboxes.prop('checked', false);
            selectedTypes = [];
            renderSelectedTypes();
        }

        function resetAllFilters() {
            clearTagsFilter();
            clearTypesFilter();
        }



        function updateResultsCount(count) {

            $resultsCountValue.text(count);

            $resultsCountLabel.text(pluralizeMetki(count));

            $resultsCount.prop('hidden', false);

            $saveBtn.prop('disabled', count === 0);

        }



        function clearResults() {

            $resultsList.empty();

            if (mapInstance) {

                searchMarkers.forEach(function (marker) {

                    mapInstance.geoObjects.remove(marker);

                });

            }

            searchMarkers = [];

            lastSearchResults = [];

            lastSearchCriteria = null;

            $resultsCount.prop('hidden', true);

            $saveBtn.prop('disabled', true);

            $emptyMsg.text('Здесь появятся найденные метки').show();

        }



        function renderResult(item) {

            $emptyMsg.hide();



            if (mapInstance) {

                const marker = new ymaps.GeoObject({

                    geometry: {

                        type: 'Point',

                        coordinates: [item.lat, item.lon]

                    },

                    properties: {

                        iconContent: item.name,

                        balloonContent: item.description

                    }

                }, {

                    preset: 'islands#blueStretchyIcon'

                });

                mapInstance.geoObjects.add(marker);

                searchMarkers.push(marker);

            }



            const $col = $($cardTemplate.html().trim());

            const $card = $col.find('.placemark-saved-card');



            $card.find('.placemark-saved-name').text(item.name).attr('title', item.name);

            $card.find('.placemark-saved-lat').text(formatCoord(item.lat)).attr('title', 'lat: ' + item.lat);

            $card.find('.placemark-saved-lon').text(formatCoord(item.lon)).attr('title', 'lon: ' + item.lon);

            bindResultCard($card, String(item.id));



            $resultsList.append($col);

        }



        function getSearchArea() {

            if (!circleHandler) {

                return null;

            }



            const radius = circleHandler.getRadius();

            const center = circleHandler.getCenter();



            if (!radius || !center) {

                return null;

            }



            return {

                center: center,

                radius: Math.round(radius)

            };

        }



        function performSearch() {

            const area = getSearchArea();



            if (!area) {

                alert('Сначала задайте область поиска на карте.');

                return;

            }



            setButtonLoading($searchBtn, true, 'Поиск');

            clearResults();



            const requestData = {

                lat: area.center[0],

                lon: area.center[1],

                radius: area.radius

            };

            if (selectedTags.length > 0) {
                requestData.tags = selectedTags.map(function (tag) {
                    return tag.id;
                });
            }

            if (selectedTypes.length > 0) {
                requestData.types = selectedTypes.map(function (type) {
                    return type.id;
                });
            }



            $.ajax({

                url: apiUrl,

                method: 'GET',

                traditional: true,

                data: requestData

            }).done(function (data) {

                const results = Array.isArray(data) ? data : [];



                lastSearchResults = results;

                lastSearchCriteria = {

                    latitude: area.center[0],

                    longitude: area.center[1],

                    radius: area.radius,

                    tags: selectedTags.map(function (tag) {
                        return tag.id;
                    }),

                    types: selectedTypes.map(function (type) {
                        return type.id;
                    })

                };



                if (results.length > 0) {

                    results.forEach(renderResult);

                } else {

                    $emptyMsg.text('Метки не найдены').show();

                }



                updateResultsCount(results.length);

            }).fail(function () {

                alert('Ошибка при поиске меток.');

                $emptyMsg.text('Ошибка загрузки').show();

            }).always(function () {

                setButtonLoading($searchBtn, false, 'Найти');

            });

        }



        function showSaveError(message) {

            $saveError.text(message).prop('hidden', false);

            $collectionNameInput.addClass('is-invalid');

        }



        function clearSaveError() {

            $saveError.prop('hidden', true).text('');

            $collectionNameInput.removeClass('is-invalid');

        }



        function openSaveModal() {

            if (!lastSearchResults.length || !lastSearchCriteria) {

                alert('Сначала выполните поиск и найдите хотя бы одну метку.');

                return;

            }



            clearSaveError();

            $collectionNameInput.val('');

            saveModal.show();

            window.setTimeout(function () {

                $collectionNameInput.trigger('focus');

            }, 200);

        }



        function saveCollection() {

            const name = $.trim($collectionNameInput.val());



            if (!name) {

                showSaveError('Введите название подборки.');

                return;

            }



            clearSaveError();

            setButtonLoading($saveConfirmBtn, true, 'Сохранение');



            $.ajax({

                url: saveUrl,

                method: 'POST',

                contentType: 'application/json',

                data: JSON.stringify({

                    name: name,

                    search_criteria: lastSearchCriteria,

                    placemarkers: lastSearchResults.map(function (item) {

                        return {

                            originalId: String(item.id),

                            title: item.name,

                            latitude: item.lat,

                            longitude: item.lon,

                            description: item.description || null

                        };

                    })

                })

            }).done(function () {

                saveModal.hide();

                alert('Подборка сохранена.');

                loadCollections();

            }).fail(function (xhr) {

                let message = 'Не удалось сохранить подборку.';



                if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors[0]) {

                    message = xhr.responseJSON.errors[0].message || message;

                } else if (xhr.responseJSON && xhr.responseJSON.message) {

                    message = xhr.responseJSON.message;

                }



                showSaveError(message);

            }).always(function () {

                setButtonLoading($saveConfirmBtn, false, 'Сохранить');

            });

        }



        function loadCollections() {
            if (!collectionsUrl || $showCollectionsBtn.find('.spinner-border').length) return;

            const $spinner = $(SPINNER_HTML).addClass('me-2').removeClass('me-1');
            const $icon = $showCollectionsBtn.find('svg');
            
            $icon.hide();
            $showCollectionsBtn.prepend($spinner);
            $showCollectionsBtn.prop('disabled', true);

            $.ajax({
                url: collectionsUrl,
                method: 'GET'
            }).done(function (response) {
                const data = response.data || response;
                userCollections = Array.isArray(data) ? data : [];
                $collectionsCountBadge.text(userCollections.length);
            }).fail(function () {
                window.console.error('Failed to load collections');
            }).always(function () {
                $spinner.remove();
                $icon.show();
                $showCollectionsBtn.prop('disabled', false);
            });
        }

        function renderCollectionsTable() {
            $collectionsTbody.empty();

            if (userCollections.length === 0) {
                $collectionsTbody.append('<tr><td colspan="4" class="text-center py-4 text-muted">У вас пока нет сохраненных подборок</td></tr>');
                return;
            }

            userCollections.forEach(function (collection) {
                const date = new Date(collection.createdAt).toLocaleString('ru-RU', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const $tr = $('<tr>');
                $tr.append($('<td class="ps-4 fw-medium">').text(collection.name));
                $tr.append($('<td>').text(collection.placemarkersCount + ' ' + pluralizeMetki(collection.placemarkersCount)));
                $tr.append($('<td class="text-muted small">').text(date));

                const $actionsTd = $('<td class="text-end pe-4">');
                const $showBtn = $('<button type="button" class="btn btn-sm btn-outline-primary me-2">Показать</button>');
                const $deleteBtn = $('<button type="button" class="btn btn-sm btn-outline-danger">Удалить</button>');

                $showBtn.on('click', function () {
                    showCollectionOnMap(collection);
                });

                $deleteBtn.on('click', function () {
                    if (confirm('Удалить подборку "' + collection.name + '"?')) {
                        deleteCollection(collection.id, $tr);
                    }
                });

                $actionsTd.append($showBtn).append($deleteBtn);
                $tr.append($actionsTd);
                $collectionsTbody.append($tr);
            });
        }

        function showCollectionOnMap(collection) {
            if (collectionsModal) {
                collectionsModal.hide();
            }

            clearResults();

            if (circleHandler && collection.searchCriteria) {
                circleHandler.drawCircle(
                    [collection.searchCriteria.latitude, collection.searchCriteria.longitude],
                    collection.searchCriteria.radiusMeters
                );
            }

            const results = collection.placemarkers || [];
            lastSearchResults = results.map(function (pm) {
                return {
                    id: pm.originalId,
                    name: pm.title,
                    lat: pm.latitude,
                    lon: pm.longitude,
                    description: pm.description
                };
            });
            
            lastSearchCriteria = {
                latitude: collection.searchCriteria.latitude,
                longitude: collection.searchCriteria.longitude,
                radius: collection.searchCriteria.radiusMeters
            };

            if (results.length > 0) {
                lastSearchResults.forEach(renderResult);
            } else {
                $emptyMsg.text('Метки не найдены').show();
            }

            updateResultsCount(results.length);
        }

        function deleteCollection(id, $tr) {
            const $btn = $tr.find('.btn-outline-danger');
            const originalText = $btn.text();
            $btn.prop('disabled', true).html(SPINNER_HTML);

            $.ajax({
                url: collectionsUrl + '/' + id,
                method: 'DELETE'
            }).done(function () {
                userCollections = userCollections.filter(function (c) { return c.id !== id; });
                $collectionsCountBadge.text(userCollections.length);
                $tr.fadeOut(300, function () {
                    $tr.remove();
                    if (userCollections.length === 0) {
                        renderCollectionsTable();
                    }
                });
            }).fail(function () {
                alert('Ошибка при удалении подборки');
                $btn.prop('disabled', false).text(originalText);
            });
        }

        $showCollectionsBtn.on('click', function () {
            renderCollectionsTable();
            if (collectionsModal) {
                collectionsModal.show();
            }
        });

        $tagsFilterBtn.on('click', function () {
            if (tagsModal) {
                tagsModal.show();
            }
        });

        $typesFilterBtn.on('click', function () {
            if (typesModal) {
                typesModal.show();
            }
        });

        $('#search-tags-apply-button').on('click', applyTagsFilter);
        $('#search-types-apply-button').on('click', applyTypesFilter);
        $('#search-tags-clear-button').on('click', clearTagsFilter);
        $('#search-types-clear-button').on('click', clearTypesFilter);
        $filtersResetBtn.on('click', resetAllFilters);

        updateFilterToolbarState();

        $searchBtn.on('click', performSearch);
        $saveBtn.on('click', openSaveModal);

        $saveConfirmBtn.on('click', saveCollection);



        $saveModalEl.on('hidden.bs.modal', clearSaveError);

        $collectionNameInput.on('input', clearSaveError);

        $infoModalEl.on('hidden.bs.modal', function () {
            if (activeInfoRequest) {
                activeInfoRequest.abort();
                activeInfoRequest = null;
            }

            clearInfoModal();
        });



        const initMap = function () {
            mapInstance = new ymaps.Map('search-map', {
                center: [centerLat, centerLon],
                zoom: 12
            }, {
                searchControlProvider: 'yandex#search'
            });

            circleHandler = MapCircleHandler();
            circleHandler.initModule(mapInstance, {
                initSearch: true,
                initEvents: true
            });

            circleHandler.drawCircle([centerLat, centerLon], defaultRadius);

            if (window.bindYandexMapResize) {
                window.bindYandexMapResize(mapInstance, 'search-map');
            }
        };

        if (window.whenYmapsReady) {
            window.whenYmapsReady(initMap);
        } else {
            window.console.error('ymap-resize.js must be loaded before placemark-search.js');
        }

        loadCollections();
    }



    $(initPlacemarkSearch);

}(jQuery));

