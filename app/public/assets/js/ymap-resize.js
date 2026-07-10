/**
 * Пересчёт размера Yandex Map после fullscreen и изменения layout.
 */
(function (window) {
    'use strict';

    /**
     * Yandex API грузится асинхронно — нельзя вызывать ymaps до появления в window.
     */
    window.whenYmapsReady = function (callback) {
        if (typeof window.ymaps !== 'undefined' && typeof window.ymaps.ready === 'function') {
            window.ymaps.ready(callback);
            return;
        }

        let attempts = 0;
        const maxAttempts = 200;

        const timer = window.setInterval(function () {
            attempts += 1;

            if (typeof window.ymaps !== 'undefined' && typeof window.ymaps.ready === 'function') {
                window.clearInterval(timer);
                window.ymaps.ready(callback);
                return;
            }

            if (attempts >= maxAttempts) {
                window.clearInterval(timer);
                window.console.error('Yandex Maps API (ymaps) failed to load.');
            }
        }, 50);
    };

    function fitMap(map) {
        if (!map || !map.container || typeof map.container.fitToViewport !== 'function') {
            return;
        }

        map.container.fitToViewport();
    }

    function resetContainerStyles(containerId) {
        const el = document.getElementById(containerId);
        if (!el) {
            return;
        }

        el.style.width = '';
        el.style.height = '';
        el.style.position = '';
        el.style.top = '';
        el.style.left = '';
        el.style.zIndex = '';
    }

    window.bindYandexMapResize = function (map, containerId) {
        if (!map) {
            return;
        }

        function reflow() {
            resetContainerStyles(containerId);
            fitMap(map);
        }

        function reflowDelayed() {
            reflow();
            window.setTimeout(reflow, 0);
            window.setTimeout(reflow, 100);
            window.setTimeout(reflow, 300);
        }

        map.events.add('fullscreenchange', function (event) {
            if (!event.get('fullscreen')) {
                reflowDelayed();
            }
        });

        document.addEventListener('fullscreenchange', function () {
            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                reflowDelayed();
            }
        });

        window.addEventListener('resize', reflow);

        if (typeof window.ResizeObserver === 'function') {
            const container = document.getElementById(containerId);
            if (container) {
                const observer = new window.ResizeObserver(function () {
                    fitMap(map);
                });
                observer.observe(container);
            }
        }
    };
}(window));
