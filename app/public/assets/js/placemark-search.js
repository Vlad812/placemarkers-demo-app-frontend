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

    function parseJsonData(raw, fallback) {
        if (Array.isArray(raw)) {
            return raw;
        }

        if (typeof raw !== 'string' || raw === '') {
            return fallback;
        }

        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : fallback;
        } catch (error) {
            return fallback;
        }
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

        const $filterToolbar = $('#search-filter-toolbar');
        const $filterPairs = $('#search-filter-pairs');
        const $filtersResetBtn = $('#search-filters-reset-button');
        const $tagsModalEl = $('#search-tags-modal');
        const $typesModalEl = $('#search-types-modal');
        const $tagsList = $('#search-tags-list');
        const $typesList = $('#search-types-list');
        const $tagsModalEmpty = $('#search-tags-modal-empty');
        const $typesModalEmpty = $('#search-types-modal-empty');
        let tagsModal = null;
        let typesModal = null;
        let infoModal = null;
        let activeInfoRequest = null;
        let activePairIndex = null;
        let activeModalKind = null;

        const allTypes = parseJsonData($filterToolbar.data('types'), []);
        const allTags = parseJsonData($filterToolbar.data('tags'), []);
        let filterPairs = [{ type: null, tags: [] }];

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
            allTags.forEach(function (tag) {
                map[tag.id] = tag.name;
            });
            return map;
        }

        function buildTypeLabelMap() {
            const map = {};
            allTypes.forEach(function (type) {
                map[type.slug] = type.name;
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
                $infoDescription.text(description).removeClass('placemark-info-description--empty');
            } else {
                $infoDescription.text('Описание не указано').addClass('placemark-info-description--empty');
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
                setInfoModalLoading(false);
                fillInfoModal(data || {});
                $infoContent.prop('hidden', false);
            }).fail(function (xhr) {
                if (xhr.statusText === 'abort') {
                    return;
                }
                setInfoModalLoading(false);
                showInfoError('Не удалось загрузить информацию о метке.');
            }).always(function () {
                activeInfoRequest = null;
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

        function getUsedTypeIds(exceptIndex) {
            return filterPairs
                .map(function (pair, index) {
                    if (index === exceptIndex || !pair.type) {
                        return null;
                    }
                    return pair.type.id;
                })
                .filter(Boolean);
        }

        function getAvailableTypes(pairIndex) {
            const used = getUsedTypeIds(pairIndex);
            return allTypes.filter(function (type) {
                return used.indexOf(type.slug) === -1;
            });
        }

        function getTagsForType(typeId) {
            return allTags.filter(function (tag) {
                return String(tag.type_id || 'default') === String(typeId);
            });
        }

        function buildActiveFilters() {
            return filterPairs
                .filter(function (pair) {
                    return pair.type !== null;
                })
                .map(function (pair) {
                    return {
                        type_id: pair.type.id,
                        tags: pair.tags.map(function (tag) {
                            return tag.id;
                        })
                    };
                });
        }

        function renderFilterChip(label, typeClass, onRemove) {
            const $chip = $('<span class="search-filter-chip ' + typeClass + '"></span>');
            const $text = $('<span class="search-filter-chip__text"></span>').text(label).attr('title', label);
            const $remove = $('<button type="button" class="search-filter-chip__remove" aria-label="Убрать">&times;</button>');

            $remove.on('click', onRemove);
            $chip.append($text).append($remove);

            return $chip;
        }

        function ensureTrailingEmptyPair() {
            const hasEmpty = filterPairs.some(function (pair) {
                return pair.type === null;
            });
            const usedCount = getUsedTypeIds(null).length;
            const hasUnusedTypes = usedCount < allTypes.length;

            if (!hasEmpty && hasUnusedTypes) {
                filterPairs.push({ type: null, tags: [] });
            }

            while (
                filterPairs.length > 1 &&
                filterPairs[filterPairs.length - 1].type === null &&
                filterPairs[filterPairs.length - 2].type === null
            ) {
                filterPairs.pop();
            }
        }

        function updateFilterToolbarState() {
            const activeCount = buildActiveFilters().length;
            $filterToolbar.toggleClass('search-filter-toolbar--active', activeCount > 0);
            $filtersResetBtn.prop('disabled', activeCount === 0);
        }

        function renderFilterPairs() {
            ensureTrailingEmptyPair();
            $filterPairs.empty();

            filterPairs.forEach(function (pair, index) {
                const $row = $('<div class="search-filter-toolbar__groups" data-pair-index="' + index + '"></div>');

                const $typeGroup = $(
                    '<div class="search-filter-group">' +
                        '<div class="search-filter-group__header">' +
                            '<span class="search-filter-group__label">Типы меток</span>' +
                            '<button type="button" class="btn btn-sm search-filter-add-btn search-pair-type-btn">Выбрать</button>' +
                        '</div>' +
                        '<div class="search-filter-chips search-pair-type-chips"></div>' +
                        '<p class="search-filter-group__hint mb-0 search-pair-type-hint">Не выбран</p>' +
                    '</div>'
                );

                const $tagsGroup = $(
                    '<div class="search-filter-group">' +
                        '<div class="search-filter-group__header">' +
                            '<span class="search-filter-group__label">Теги</span>' +
                            '<button type="button" class="btn btn-sm search-filter-add-btn search-pair-tags-btn">Выбрать</button>' +
                        '</div>' +
                        '<div class="search-filter-chips search-pair-tags-chips"></div>' +
                        '<p class="search-filter-group__hint mb-0 search-pair-tags-hint">Сначала выберите тип</p>' +
                    '</div>'
                );

                // UI request: types on left, tags on right
                $row.append($typeGroup).append($tagsGroup);

                const $typeBtn = $typeGroup.find('.search-pair-type-btn');
                const $tagsBtn = $tagsGroup.find('.search-pair-tags-btn');
                const $typeChips = $typeGroup.find('.search-pair-type-chips');
                const $tagsChips = $tagsGroup.find('.search-pair-tags-chips');
                const $typeHint = $typeGroup.find('.search-pair-type-hint');
                const $tagsHint = $tagsGroup.find('.search-pair-tags-hint');

                const availableTypes = getAvailableTypes(index);
                $typeBtn.prop('disabled', availableTypes.length === 0 && !pair.type);

                if (pair.type) {
                    $typeGroup.addClass('search-filter-group--active');
                    $typeHint.prop('hidden', true);
                    $typeChips.append(renderFilterChip(pair.type.label, 'search-filter-chip--type', function () {
                        filterPairs[index] = { type: null, tags: [] };
                        filterPairs = filterPairs.filter(function (item) {
                            return item.type !== null;
                        });
                        filterPairs.push({ type: null, tags: [] });
                        renderFilterPairs();
                    }));
                } else {
                    $typeHint.prop('hidden', false).text('Не выбран');
                }

                if (pair.type) {
                    $tagsBtn.prop('disabled', false);
                    if (pair.tags.length > 0) {
                        $tagsGroup.addClass('search-filter-group--active');
                        $tagsHint.prop('hidden', true);
                        pair.tags.forEach(function (tag) {
                            $tagsChips.append(renderFilterChip(tag.name, 'search-filter-chip--tag', function () {
                                filterPairs[index].tags = filterPairs[index].tags.filter(function (item) {
                                    return item.id !== tag.id;
                                });
                                renderFilterPairs();
                            }));
                        });
                    } else {
                        $tagsHint.prop('hidden', false).text('Любые теги типа');
                    }
                } else {
                    $tagsBtn.prop('disabled', true);
                    $tagsHint.prop('hidden', false).text('Сначала выберите тип');
                }

                $typeBtn.on('click', function () {
                    openTypesModal(index);
                });

                $tagsBtn.on('click', function () {
                    if (!filterPairs[index].type) {
                        return;
                    }
                    openTagsModal(index);
                });

                $filterPairs.append($row);
            });

            updateFilterToolbarState();
        }

        function openTypesModal(pairIndex) {
            activePairIndex = pairIndex;
            activeModalKind = 'types';

            const available = getAvailableTypes(pairIndex);
            const current = filterPairs[pairIndex].type;
            $typesList.empty();

            if (available.length === 0) {
                $typesModalEmpty.prop('hidden', false);
            } else {
                $typesModalEmpty.prop('hidden', true);
                available.forEach(function (type) {
                    const inputId = 'search_type_' + pairIndex + '_' + type.slug;
                    const $wrap = $('<div></div>');
                    const $input = $('<input type="radio" class="btn-check search-type-radio" autocomplete="off">')
                        .attr('name', 'search-type-choice')
                        .attr('id', inputId)
                        .val(type.slug)
                        .attr('data-label', type.name);
                    const $label = $('<label class="btn btn-outline-primary rounded-pill px-3 py-1"></label>')
                        .attr('for', inputId)
                        .text(type.name);

                    if (current && current.id === type.slug) {
                        $input.prop('checked', true);
                    }

                    $wrap.append($input).append($label);
                    $typesList.append($wrap);
                });
            }

            if (typesModal) {
                typesModal.show();
            }
        }

        function openTagsModal(pairIndex) {
            activePairIndex = pairIndex;
            activeModalKind = 'tags';

            const pair = filterPairs[pairIndex];
            if (!pair.type) {
                return;
            }

            const tags = getTagsForType(pair.type.id);
            const selectedIds = pair.tags.map(function (tag) {
                return tag.id;
            });

            $tagsList.empty();
            if (tags.length === 0) {
                $tagsModalEmpty.prop('hidden', false);
            } else {
                $tagsModalEmpty.prop('hidden', true);
                tags.forEach(function (tag) {
                    const inputId = 'search_tag_' + pairIndex + '_' + tag.id;
                    const $wrap = $('<div></div>');
                    const $input = $('<input type="checkbox" class="btn-check search-tag-checkbox" autocomplete="off">')
                        .attr('id', inputId)
                        .val(tag.id)
                        .attr('data-name', tag.name);
                    const $label = $('<label class="btn btn-outline-primary rounded-pill px-3 py-1"></label>')
                        .attr('for', inputId)
                        .text(tag.name);

                    if (selectedIds.indexOf(tag.id) !== -1) {
                        $input.prop('checked', true);
                    }

                    $wrap.append($input).append($label);
                    $tagsList.append($wrap);
                });
            }

            if (tagsModal) {
                tagsModal.show();
            }
        }

        function applyTypesFilter() {
            if (activePairIndex === null) {
                return;
            }

            const $selected = $typesList.find('.search-type-radio:checked');
            if (!$selected.length) {
                return;
            }

            const typeId = String($selected.val());
            const typeLabel = String($selected.data('label') || typeId);
            filterPairs[activePairIndex] = {
                type: { id: typeId, label: typeLabel },
                tags: []
            };
            renderFilterPairs();
        }

        function applyTagsFilter() {
            if (activePairIndex === null) {
                return;
            }

            const tags = $tagsList.find('.search-tag-checkbox:checked').map(function () {
                return {
                    id: $(this).val(),
                    name: $(this).data('name')
                };
            }).get();

            filterPairs[activePairIndex].tags = tags;
            renderFilterPairs();
        }

        function clearTagsFilter() {
            $tagsList.find('.search-tag-checkbox').prop('checked', false);
            if (activePairIndex !== null) {
                filterPairs[activePairIndex].tags = [];
                renderFilterPairs();
            }
        }

        function resetAllFilters() {
            filterPairs = [{ type: null, tags: [] }];
            renderFilterPairs();
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
                const name = item.name || 'Метка';
                const marker = new ymaps.GeoObject({
                    geometry: {
                        type: 'Point',
                        coordinates: [item.lat, item.lon]
                    },
                    properties: {
                        iconContent: name,
                        hintContent: name,
                        balloonContent: name
                    }
                }, {
                    preset: 'islands#blueStretchyIcon'
                });

                mapInstance.geoObjects.add(marker);
                searchMarkers.push(marker);
            }

            if (!$cardTemplate.length) {
                return;
            }

            const $col = $($cardTemplate.html());
            const $card = $col.find('.placemark-saved-card');
            $col.find('.placemark-saved-name').text(item.name || 'Без названия').attr('title', item.name || '');
            $col.find('.placemark-saved-lat').text(formatCoord(item.lat));
            $col.find('.placemark-saved-lon').text(formatCoord(item.lon));
            bindResultCard($card, item.id);
            $resultsList.append($col);
        }

        function getSearchArea() {
            if (!circleHandler) {
                return null;
            }

            const center = circleHandler.getCenter();
            const radius = circleHandler.getRadius();

            if (!center || !radius) {
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

            const filters = buildActiveFilters();
            const requestData = {
                lat: area.center[0],
                lon: area.center[1],
                radius: area.radius,
                filters: JSON.stringify(filters)
            };

            $.ajax({
                url: apiUrl,
                method: 'GET',
                data: requestData
            }).done(function (data) {
                const results = Array.isArray(data) ? data : [];

                lastSearchResults = results;
                lastSearchCriteria = {
                    latitude: area.center[0],
                    longitude: area.center[1],
                    radius: area.radius,
                    filters: filters
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
                alert('Сначала выполните поиск.');
                return;
            }

            clearSaveError();
            $collectionNameInput.val('');
            if (saveModal) {
                saveModal.show();
            }
        }

        function saveCollection() {
            const name = $collectionNameInput.val().trim();
            if (!name) {
                showSaveError('Укажите название подборки.');
                return;
            }

            if (!lastSearchResults.length || !lastSearchCriteria) {
                showSaveError('Нет результатов для сохранения.');
                return;
            }

            setButtonLoading($saveConfirmBtn, true, 'Сохранить');

            $.ajax({
                url: saveUrl,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    name: name,
                    search_criteria: {
                        latitude: lastSearchCriteria.latitude,
                        longitude: lastSearchCriteria.longitude,
                        radius: lastSearchCriteria.radius,
                        filters: lastSearchCriteria.filters || []
                    },
                    placemarkers: lastSearchResults.map(function (item) {
                        return {
                            originalId: String(item.id),
                            title: item.name,
                            latitude: item.lat,
                            longitude: item.lon,
                            description: item.description || null,
                            type_id: item.type_id || 'default',
                            tags: Array.isArray(item.tags) ? item.tags : []
                        };
                    })
                })
            }).done(function () {
                if (saveModal) {
                    saveModal.hide();
                }
                loadCollections();
            }).fail(function (xhr) {
                let message = 'Не удалось сохранить подборку.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors && xhr.responseJSON.errors[0] && xhr.responseJSON.errors[0].message) {
                        message = xhr.responseJSON.errors[0].message;
                    } else if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                }
                showSaveError(message);
            }).always(function () {
                setButtonLoading($saveConfirmBtn, false, 'Сохранить');
            });
        }

        function normalizeCollectionsPayload(data) {
            if (Array.isArray(data)) {
                return data;
            }

            if (data && Array.isArray(data.data)) {
                return data.data;
            }

            return [];
        }

        function loadCollections() {
            if (!collectionsUrl || $showCollectionsBtn.find('.spinner-border').length) {
                return;
            }

            const $spinner = $(SPINNER_HTML).addClass('me-2').removeClass('me-1');
            const $icon = $showCollectionsBtn.find('svg').first();

            $icon.hide();
            $showCollectionsBtn.prepend($spinner);
            $showCollectionsBtn.prop('disabled', true);

            $.ajax({
                url: collectionsUrl,
                method: 'GET'
            }).done(function (data) {
                userCollections = normalizeCollectionsPayload(data);
                $collectionsCountBadge.text(userCollections.length);
            }).fail(function () {
                userCollections = [];
                $collectionsCountBadge.text('0');
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
                $tr.append(
                    $('<td class="ps-4 fw-medium collections-list-name">').append(
                        $('<span class="collections-list-name__text">')
                            .text(collection.name)
                            .attr('title', collection.name)
                    )
                );
                $tr.append($('<td class="collections-list-col-count">').text(collection.placemarkersCount + ' ' + pluralizeMetki(collection.placemarkersCount)));
                $tr.append($('<td class="text-muted small collections-list-col-date">').text(date));

                const $actionsTd = $('<td class="text-end pe-4 collections-list-col-actions">');
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
                    description: pm.description,
                    type_id: pm.typeId || pm.type_id || 'default',
                    tags: pm.tags || []
                };
            });

            lastSearchCriteria = {
                latitude: collection.searchCriteria.latitude,
                longitude: collection.searchCriteria.longitude,
                radius: collection.searchCriteria.radiusMeters,
                filters: (collection.searchCriteria && collection.searchCriteria.filters) || []
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
                userCollections = userCollections.filter(function (c) {
                    return c.id !== id;
                });
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

        $('#search-types-apply-button').on('click', applyTypesFilter);
        $('#search-tags-apply-button').on('click', applyTagsFilter);
        $('#search-tags-clear-button').on('click', clearTagsFilter);
        $filtersResetBtn.on('click', resetAllFilters);

        renderFilterPairs();

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
