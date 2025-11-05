// reviews.js - lightweight reviews UI helpers
(function () {
    'use strict';

    function init() {
        // star click handlers (data-star attribute)
        document.querySelectorAll('.review-star').forEach(function (star) {
            star.addEventListener('click', function () {
                var container = star.closest('.review-stars');
                if (!container) return;
                var value = parseInt(star.getAttribute('data-star'), 10) || 0;
                container.setAttribute('data-value', value);
            });
        });
    }

    if (document.readyState !== 'loading') init();
    else document.addEventListener('DOMContentLoaded', init);
})();
