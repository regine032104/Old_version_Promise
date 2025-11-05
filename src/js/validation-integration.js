<<<<<<< HEAD
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
=======
/* validation-integration.js
   Adds jQuery Validation behaviors for contact and reservation forms.
   - Fullname >= 2 chars
   - Email: blocks special characters on keydown except @ . _ and alphanumerics and + -
   - Notes/Message: min 10 max 100
   - Standard required checks

   Requires jQuery and jQuery Validation plugin to be loaded first.
*/

(function($){
  if(!$) return;

  // helper: block email disallowed characters on keydown and sanitize paste/input
  function isAllowedEmailChar(ch){
    return /^[A-Za-z0-9@._]$/.test(ch);
  }

  function allowEmailKey(e){
    // Allow control/navigation keys by name
    const allowedControlKeys = ['Backspace','Tab','Enter','Escape','Home','End','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Delete'];
    if(allowedControlKeys.indexOf(e.key) !== -1) return;

    // e.key gives the actual typed character for printable keys (including '.')
    const char = e.key || String.fromCharCode(e.which || e.keyCode || 0);
    if(char.length === 1){
      if(!isAllowedEmailChar(char)){
        e.preventDefault();
      }
    }
  }

  // sanitize a full email string by removing disallowed characters
  function sanitizeEmailValue(val){
    if(typeof val !== 'string') return val;
    // keep only letters, numbers, @ . _
    return val.split('').filter(function(ch){ return /^[A-Za-z0-9@._]$/.test(ch); }).join('');
  }

  function init(){
    // bind keydown for email inputs in both forms
    $(document).on('keydown', '#contact-email, #res-email', allowEmailKey);
    // block paste of disallowed characters by sanitizing pasted text
    $(document).on('paste', '#contact-email, #res-email', function(e){
      var el = this;
      setTimeout(function(){
        var sanitized = sanitizeEmailValue(el.value);
        if(el.value !== sanitized) el.value = sanitized;
      }, 0);
    });
    // also sanitize on input and blur to catch programmatic changes
    $(document).on('input blur', '#contact-email, #res-email', function(){
      var sanitized = sanitizeEmailValue(this.value);
      if(this.value !== sanitized) this.value = sanitized;
    });

    // jQuery Validation rules
    if($.validator){
      // common rules
      const nameRules = {
        required: true,
        minlength: 2
      };

      const emailRules = {
        required: true,
        email: true
      };

      const notesRules = {
        minlength: 10,
        maxlength: 100
      };

      // Contact form
      $('#contact-form').validate({
        rules: {
          name: nameRules,
          email: emailRules,
          message: $.extend({}, notesRules, { required: true }),
          subject: { maxlength: 150 }
        },
        messages: {
          name: {
            required: 'Please enter your name',
            minlength: 'Name must be at least 2 characters'
          },
          email: {
            required: 'Please enter your email',
            email: 'Please enter a valid email address'
          },
          message: {
            required: 'Please enter a message',
            minlength: 'Message must be at least 10 characters',
            maxlength: 'Message must be 100 characters or less'
          }
        },
        errorClass: 'text-red-600 text-sm',
        errorElement: 'div',
        errorPlacement: function(error, element){
          const name = element.attr('id');
          // try to attach to existing error container if present
          const container = $('.contact-error[data-error-for="' + name + '"]');
          if(container.length){
            container.removeClass('hidden').html(error);
          } else {
            error.insertAfter(element);
          }
        },
        success: function(label){
          // Hide inline contact errors
          label.closest('form').find('.contact-error[data-error-for="' + label.attr('for') + '"]').addClass('hidden').empty();
        }
      });

      // Reservation form
      $('#reservation-form').validate({
        rules: {
          name: nameRules,
          email: emailRules,
          date: { required: true, date: true },
          time: { required: true },
          service: { required: true },
          notes: notesRules
        },
        messages: {
          name: {
            required: 'Please enter your full name',
            minlength: 'Full name must be at least 2 characters'
          },
          email: {
            required: 'Please enter your email',
            email: 'Please enter a valid email address'
          },
          date: {
            required: 'Please select a date'
          },
          time: { required: 'Please select a time' },
          service: { required: 'Please select a service type' },
          notes: {
            minlength: 'Notes must be at least 10 characters',
            maxlength: 'Notes must be 100 characters or less'
          }
        },
        errorClass: 'text-red-600 text-sm',
        errorElement: 'div',
        errorPlacement: function(error, element){
          error.insertAfter(element);
        }
      });

    } else {
      console.warn('jQuery Validation plugin not found.');
    }
  }

  // init on DOM ready
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})(window.jQuery);
>>>>>>> bf1e9afdb186b25e88fe9d01f82ecd89cd0086db
