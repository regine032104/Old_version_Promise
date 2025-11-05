// main.js - site-wide UI helpers
(function () {
    'use strict';

    // Update the cart count badge used in the navbar
    function updateCartCount(n) {
        try {
            var el = document.getElementById('nav-cart-count');
            if (!el) return;
            el.textContent = n ? n : '';
            if (n && Number(n) > 0) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        } catch (e) {
            // ignore
        }
    }

    // Fetch current cart summary from the backend and update badge
    function refreshCartCount() {
        // Try fetch API first
        var apiUrl = '../backend/cart_api.php';
        if (window.location.pathname.indexOf('/src/pages/') !== -1) {
            // pages are served from src/pages, backend path from pages should be ../backend
            apiUrl = '../backend/cart_api.php';
        }
        // Fallback: if served from project root, try src/backend
        // Try fetch
        if (window.fetch) {
            fetch(apiUrl, { credentials: 'same-origin' })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (json && json.cartCount !== undefined) updateCartCount(json.cartCount);
                })
                .catch(function () {
                    // try jQuery if present
                    if (window.jQuery) {
                        jQuery.get(apiUrl, function (json) {
                            if (json && json.cartCount !== undefined) updateCartCount(json.cartCount);
                        });
                    }
                });
            return;
        }

        // jQuery fallback
        if (window.jQuery) {
            jQuery.get(apiUrl, function (json) {
                if (json && json.cartCount !== undefined) updateCartCount(json.cartCount);
            });
        }
    }

    // Expose to global for simple use by other scripts
    window.PromiseUI = {
        updateCartCount: updateCartCount,
        refreshCartCount: refreshCartCount
    };

    // DOM ready: init small behaviors
    function domReady(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    domReady(function () {
        // Initialize cart count from server-rendered badge text if present
        var el = document.getElementById('nav-cart-count');
        if (el) {
            var val = parseInt(el.textContent || el.innerText || '0', 10) || 0;
            updateCartCount(val);
        }
        // Refresh from server to ensure accurate badge
        refreshCartCount();
    });
})();
