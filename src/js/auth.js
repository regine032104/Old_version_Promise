// auth.js - small helpers for auth modals and AJAX forms
(function () {
    'use strict';

    function submitAuthForm($form, cb) {
        if (typeof jQuery === 'undefined') { if (cb) cb(null); return; }
        var url = $form.attr('action') || $form.data('action');
        var data = $form.serialize();
        jQuery.post(url, data, function (res) {
            if (cb) cb(res);
        }, 'json').fail(function () { if (cb) cb(null); });
    }

    // Attach to forms with data-ajax="auth"
    function init() {
        if (typeof jQuery === 'undefined') return;
        jQuery('form[data-ajax="auth"]').on('submit', function (e) {
            e.preventDefault();
            var $f = jQuery(this);
            submitAuthForm($f, function (res) {
                if (!res) return;
                if (res.success) {
                    // refresh to apply logged-in state or call callback
                    if ($f.data('redirect')) window.location = res.redirect || $f.data('redirect');
                    else window.location.reload();
                } else {
                    // show message in an element with .auth-error
                    var $err = $f.find('.auth-error');
                    if ($err.length) $err.text(res.message || 'Error');
                }
            });
        });

        // Also attach to modal login/register forms by id if present
        var $login = jQuery('#login-form');
        if ($login.length) {
            $login.on('submit', function (e) {
                e.preventDefault();
                submitAuthForm($login, function (res) {
                    var $msg = jQuery('#login-message');
                    if (!res) {
                        if ($msg.length) $msg.removeClass('hidden').text('Network error');
                        return;
                    }
                    if (res.success) {
                        if ($msg.length) $msg.removeClass('hidden').text(res.message || 'Logged in');
                        // refresh cart badge
                        if (window.PromiseUI && typeof window.PromiseUI.refreshCartCount === 'function') {
                            try { window.PromiseUI.refreshCartCount(); } catch (e) { }
                        }
                        // follow redirect if provided, otherwise reload to update navbar
                        if (res.redirect) window.location.href = res.redirect;
                        else setTimeout(function () { window.location.reload(); }, 600);
                    } else {
                        if ($msg.length) $msg.removeClass('hidden').text(res.message || 'Login failed');
                    }
                });
            });
        }

        var $reg = jQuery('#register-form');
        if ($reg.length) {
            $reg.on('submit', function (e) {
                e.preventDefault();
                submitAuthForm($reg, function (res) {
                    var $msg = jQuery('#register-message');
                    if (!res) {
                        if ($msg.length) $msg.removeClass('hidden').text('Network error');
                        return;
                    }
                    if (res.success) {
                        if ($msg.length) $msg.removeClass('hidden').text(res.message || 'Registration successful');
                        // switch to login modal after a short delay
                        setTimeout(function () { if (typeof switchToLogin === 'function') switchToLogin(); }, 900);
                    } else {
                        if ($msg.length) $msg.removeClass('hidden').text(res.message || 'Registration failed');
                    }
                });
            });
        }
    }

    if (document.readyState !== 'loading') init();
    else document.addEventListener('DOMContentLoaded', init);

})();
