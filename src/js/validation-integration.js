// validation-integration.js
// Small helper to wire jQuery Validation plugin to forms using HTML attributes
(function () {
    'use strict';

    function init() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') return;

        // Auto-initialize validation on forms with data-validate="true"
        jQuery('form[data-validate="true"]').each(function () {
            var $f = jQuery(this);
            if (!$f.data('validator')) {
                $f.validate({
                    errorClass: 'text-red-600',
                    errorElement: 'div',
                    highlight: function (el) { jQuery(el).addClass('border-red-300'); },
                    unhighlight: function (el) { jQuery(el).removeClass('border-red-300'); }
                });
            }
        });
    }

    if (document.readyState !== 'loading') init();
    else document.addEventListener('DOMContentLoaded', init);
})();
