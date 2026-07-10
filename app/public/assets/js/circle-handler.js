/**
 * Interactive search circle handler for Yandex Maps 2.1.
 * Refactored from legacy circle_constr.js.
 */
var MapCircleHandler = function () {
    var start, end, balloon,
        map, initModule,
        chooseRadius, drawCircle, left_vertex,
        right_vertex, top_vertex, bottom_vertex,
        center_vertex, renderDomElements,
        circle, createDomElements, getRadius, getCenter,
        bindVertex, removeButton, removeCallback,
        removeCircle, setBaloonData,
        polyline, activateEditMode, getMap,
        getRemoveButton;

    chooseRadius = function (event) {
        if (!start) {
            return;
        }

        var projection = map.options.get('projection');
        var cursorCoord = projection.fromGlobalPixels(event.get('globalPixels'), map.getZoom());
        var currRadius = parseInt(ymaps.coordSystem.geo.getDistance(start, cursorCoord), 10);

        start && balloon.setData({content: 'Расстояние: ' + currRadius + 'м'});

        if (circle) {
            circle.geometry.setRadius(currRadius);
        } else {
            drawCircle(start, currRadius, true);
        }
    };

    setBaloonData = function (e) {
        var pane = $(circle.geometry.getMap().panes.get('controls').getElement());
        var offset = pane.offset();
        var globalPixels = map.panes.get('controls').fromClientPixels([e.pageX - offset.left, e.pageY - offset.top]);
        var geo = map.options.get('projection').fromGlobalPixels(globalPixels, map.getZoom());
        var newRadius = Math.round(ymaps.coordSystem.geo.getDistance(circle.geometry.getCoordinates(), geo));

        !balloon.isOpen() && balloon.open(circle.geometry.getCoordinates());
        balloon.setData({content: 'Расстояние: ' + newRadius + 'м'});
    };

    drawCircle = function (center, radius, withoutEvents) {
        circle && map.geoObjects.remove(circle);
        circle && removeCircle();

        circle = new ymaps.Circle([
            center,
            radius
        ], {
            balloonContent: 'Радиус круга ' + parseInt(radius, 10) + 'м'
        }, {
            draggable: false,
            fillColor: '#d03636',
            strokeColor: '#ff0000',
            strokeOpacity: 0.8,
            fillOpacity: 0.1,
            strokeWidth: 1
        });

        map.geoObjects.add(circle);

        if (!withoutEvents) {
            map.events.add('actionbegin', function () {
                $(map.panes.get('controls').getElement()).find('.point').hide();
            });
            map.events.add('actionend', function () {
                renderDomElements();
                $(map.panes.get('controls').getElement()).find('.point').show();
            });

            center_vertex || createDomElements();
            renderDomElements();

            removeButton = removeButton || new ymaps.control.Button({
                data: {
                    content: 'Удалить область',
                    title: 'Нажмите, чтобы удалить область.'
                },
                options: {
                    selectOnClick: false,
                    maxWidth: [130, 130, 130]
                }
            });

            removeButton.events.add('click', function () {
                removeCircle();
                removeCallback && removeCallback();
            });

            map.controls.add(removeButton, {top: 5, right: 5});
        }
    };

    removeCircle = function () {
        map && $(map.panes.get('controls').getElement()).find('.point').remove();
        center_vertex = null;

        if (circle) {
            map.geoObjects.remove(circle);
        }

        if (removeButton) {
            map.controls.remove(removeButton);
        }

        removeButton = null;
        circle = null;
        start = null;
    };

    createDomElements = function () {
        var $pane = $(map.panes.get('controls').getElement());
        var css = {
            position: 'absolute',
            width: 10,
            height: 10,
            marginLeft: -6,
            marginTop: -6,
            backgroundColor: 'white',
            border: '1px solid black',
            cursor: 'pointer',
            zIndex: 999
        };

        left_vertex = (left_vertex || $('<div class="point"></div>')).css(css).appendTo($pane);
        bindVertex(left_vertex, $pane);

        right_vertex = (right_vertex || $('<div class="point"></div>')).css(css).appendTo($pane);
        bindVertex(right_vertex, $pane);

        top_vertex = (top_vertex || $('<div class="point"></div>')).css(css).appendTo($pane);
        bindVertex(top_vertex, $pane);

        bottom_vertex = (bottom_vertex || $('<div class="point"></div>')).css(css).appendTo($pane);
        bindVertex(bottom_vertex, $pane);

        center_vertex = (center_vertex || $('<div class="point"></div>'))
            .css(css)
            .css('border-radius', 90)
            .appendTo($pane)
            .bind('mousedown', function () {
                $(document).bind('mousemove', function (e) {
                    e.stopPropagation();
                    var offset = $pane.offset();
                    var globalPixels = map.panes.get('controls').fromClientPixels([e.pageX - offset.left, e.pageY - offset.top]);
                    var geo = map.options.get('projection').fromGlobalPixels(globalPixels, map.getZoom());
                    circle.geometry.setCoordinates(geo);
                    renderDomElements();
                }).bind('mouseup', function () {
                    $(this).unbind('mousemove mouseup');
                    document.ondragstart = document.body.onselectstart = null;
                    $(this).css('backgroundColor', 'white');
                });
            });
    };

    bindVertex = function (vertex, pane) {
        vertex.bind('mouseenter', function () {
            $(this).css('backgroundColor', 'yellow');
        }).bind('mouseleave', function () {
            $(this).css('backgroundColor', 'white');
        }).bind('mousedown', function () {
            $(document).bind('mousemove', function (e) {
                e.stopPropagation();
                var offset = pane.offset();
                var globalPixels = map.panes.get('controls').fromClientPixels([e.pageX - offset.left, e.pageY - offset.top]);
                var geo = map.options.get('projection').fromGlobalPixels(globalPixels, map.getZoom());
                var newRadius = ymaps.coordSystem.geo.getDistance(circle.geometry.getCoordinates(), geo);
                circle.geometry.setRadius(newRadius);
                renderDomElements(circle);
                setBaloonData(e);
            }).bind('mouseup', function () {
                balloon.close();
                $(this).unbind('mousemove mouseup');
                document.ondragstart = document.body.onselectstart = null;
                $(this).css('backgroundColor', 'white');
            });

            document.ondragstart = document.body.onselectstart = function () {
                return false;
            };
            $(this).css('backgroundColor', 'yellow');
        });
    };

    renderDomElements = function () {
        setTimeout(function () {
            var pane = map.panes.get('controls');

            if (circle === null) {
                return;
            }

            var pixelGeometry = circle.geometry.getPixelGeometry();
            var centerGlobal = pixelGeometry.getCoordinates();
            var centerClient = pane.toClientPixels(centerGlobal);
            var boundsGlobal = pixelGeometry.getBounds();

            var vertexGlobalLeft = [boundsGlobal[0][0], centerGlobal[1]];
            var vertexClientLeft = pane.toClientPixels(vertexGlobalLeft);

            var vertexGlobalRight = [boundsGlobal[1][0], centerGlobal[1]];
            var vertexClientRight = pane.toClientPixels(vertexGlobalRight);

            var vertexGlobalTop = [centerGlobal[0], boundsGlobal[0][1]];
            var vertexClientTop = pane.toClientPixels(vertexGlobalTop);

            var vertexGlobalBottom = [centerGlobal[0], boundsGlobal[1][1]];
            var vertexClientBottom = pane.toClientPixels(vertexGlobalBottom);

            left_vertex.css({left: vertexClientLeft[0] + 'px', top: vertexClientLeft[1] + 'px'});
            right_vertex.css({left: vertexClientRight[0] + 'px', top: vertexClientRight[1] + 'px'});
            top_vertex.css({left: vertexClientTop[0] + 'px', top: vertexClientTop[1] + 'px'});
            bottom_vertex.css({left: vertexClientBottom[0] + 'px', top: vertexClientBottom[1] + 'px'});
            center_vertex.css({left: centerClient[0] + 'px', top: centerClient[1] + 'px'});
        }, 0);
    };

    activateEditMode = function (e) {
        (circle && !e) && removeCircle();

        if (polyline) {
            polyline.editor.startEditing();
            polyline.editor.startDrawing();
        }
    };

    var Init = {
        events: function () {
            Init.makePolyline();
            activateEditMode();
        },
        makePolyline: function () {
            polyline = new ymaps.GeoObject({
                geometry: {type: 'LineString'}
            });

            polyline.editor.events.add('vertexdraw', chooseRadius);

            balloon = new ymaps.Balloon(map);
            balloon.options.setParent(map.options);
            balloon.setData({content: 'Расстояние: 0м'});

            polyline.editor.events.add('vertexadd', function (e) {
                (!e.get('vertexIndex') && circle) && removeCircle();

                if (!start) {
                    start = polyline.geometry.get(0);
                    balloon.open(start);
                } else if (!end) {
                    end = polyline.geometry.get(1);
                    map.geoObjects.remove(polyline);
                    var radius = ymaps.coordSystem.geo.getDistance(start, end);
                    drawCircle(start, radius);
                    balloon.close();
                    start = null;
                    end = null;
                    polyline.editor.stopEditing();
                    polyline.editor.stopDrawing();
                    Init.makePolyline();
                    activateEditMode(true);
                }

                e.stopImmediatePropagation();
                e.preventDefault();
            });

            map.geoObjects.add(polyline);
        },
        controls: function () {
            var searchControl = map.controls.get('searchControl');

            searchControl.events.add('resultselect', function (e) {
                var pos = searchControl.state.get('results')[e.get('index')].geometry.getCoordinates();
                map.panTo(pos);
                circle && removeCircle();

                setTimeout(function () {
                    var zoom = 40;
                    var iter = 18 - map.getZoom();

                    for (var i = 0; i <= iter; i++) {
                        zoom *= 2;
                    }

                    drawCircle(pos, zoom);
                }, 100);
            });
        }
    };

    getRadius = function () {
        if (!circle) {
            return null;
        }

        return circle.geometry.getRadius();
    };

    getCenter = function () {
        if (!circle) {
            return null;
        }

        return circle.geometry.getCoordinates();
    };

    getMap = function () {
        return map;
    };

    initModule = function (ymap, opts) {
        opts = opts || {};
        removeCallback = opts.onMapDelete || null;
        map = ymap;

        if (opts.initEvents !== false && opts.initEvents !== 0) {
            Init.events();
        }

        if (opts.initSearch) {
            Init.controls();
        }
    };

    getRemoveButton = function () {
        return removeButton;
    };

    return {
        initModule: initModule,
        getRadius: getRadius,
        getMap: getMap,
        getCenter: getCenter,
        drawCircle: drawCircle,
        getRemoveButton: getRemoveButton
    };
};
