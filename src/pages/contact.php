<?php
require_once('../backend/session_check.php');
$isLoggedIn = isLoggedIn();

// Get user data directly from session for consistency
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
require_once('../layouts/app.php');
renderHeader([
  'title' => 'About - Promise',
  'isLoggedIn' => $isLoggedIn,
  'bodyClass' => 'flex min-h-screen flex-col bg-white',
  'mainClass' => 'flex-1'
]);
// HERO
$title = "CONTACT";
$highlight = 'US';
$subtitle = "Where inspired design, expert craftsmanship, and genuine care come together to create lasting memories.";
$extra_class = "py-32";

include('../components/hero.html');
?>

  <div class="mx-auto max-w-screen-xl px-4 py-20">
    <!-- Contact US -->
    <section class="mb-12 card grid grid-cols-1 gap-6 lg:grid-cols-2">
      <div>
        <h3 class="mb-2 text-lg font-semibold text-slate-900 sm:text-xl">
          Contact Us
        </h3>
        <p class="mb-4 text-sm text-slate-700 sm:text-base">
          Have questions or want to schedule a fitting? Send us a message and
          we'll get back to you within 48 hours.
        </p>
        <form id="contact-form" class="space-y-4" novalidate>
          <div>
            <label for="contact-name" class="form-label">Name <span
                class="text-red-500">*</span></label>
            <input id="contact-name" name="name" autocomplete="name"
              class="form-input" />
            <div class="contact-error mt-1 hidden text-xs text-red-600" data-error-for="contact-name"></div>
          </div>
          <div>
            <label for="contact-email" class="form-label">Email <span
                class="text-red-500">*</span></label>
            <input id="contact-email" name="email" autocomplete="email"
              class="form-input" />
            <div class="contact-error mt-1 hidden text-xs text-red-600" data-error-for="contact-email"></div>
          </div>
          <div>
            <label for="contact-subject" class="form-label">Subject</label>
            <input id="contact-subject" name="subject"
              class="form-input" />
          </div>
          <div>
            <label for="contact-message" class="form-label">Message <span
                class="text-red-500">*</span></label>
            <textarea id="contact-message" name="message"
              class="form-input h-32 resize-none"></textarea>
            <div class="contact-error mt-1 hidden text-xs text-red-600" data-error-for="contact-message"></div>
          </div>
          <button type="submit" class="btn-primary w-full">
            Send Message
          </button>
        </form>
      </div>

      <div>
        <h3 class="mb-2 text-lg font-bold text-slate-900  sm:text-xl">
          Visit or write to us
        </h3>
        <div class="mb-4 text-sm text-slate-700  sm:text-base">
          <p>Promise Atelier</p>
          <p>0321 December Avenue</p>
          <p>Philippines</p>
          <p>
            Email:
            <a href="mailto:promiseatelier@email.com" class="text-slate-700  underline">promiseatelier@email.com</a>
          </p>
          <p>
            Phone:
            <a href="tel:+639121234567" class="text-slate-700 underline">+63 912 123 4567</a>
          </p>
        </div>

        <div class="mt-2">
          <div class="mb-3 font-medium  text-slate-900 ">Follow us:</div>
          <div class="flex items-center gap-4">
            <!-- Facebook -->
            <a href="#" target="_blank" rel="noopener"
              class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-light bg-white text-primary shadow transition-transform duration-200 hover:scale-110 hover:shadow-soft"
              aria-label="Facebook">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                <path
                  d="M22 12a10 10 0 10-11.5 9.9v-7H8.5v-3h2V9.1c0-2 1.2-3.1 3-3.1.9 0 1.8.1 1.8.1v2h-1c-1 0-1.3.6-1.3 1.2V12h2.2l-.4 3h-1.8v7A10 10 0 0022 12z" />
              </svg>
            </a>
            <!-- Instagram -->
            <a href="#" target="_blank" rel="noopener"
              class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-light bg-white text-primary shadow transition-transform duration-200 hover:scale-110 hover:shadow-soft"
              aria-label="Instagram">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                <path
                  d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm5 6.2A3.8 3.8 0 1015.8 12 3.8 3.8 0 0012 8.2zm4.9-.9a1.1 1.1 0 11-1.1-1.1 1.1 1.1 0 011.1 1.1zM12 9.5A2.5 2.5 0 1114.5 12 2.5 2.5 0 0112 9.5z" />
              </svg>
            </a>
            <!-- GitHub -->
            <a href="#" target="_blank" rel="noopener"
              class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-light bg-white text-primary shadow transition-transform duration-200 hover:scale-110 hover:shadow-soft"
              aria-label="GitHub">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                <path
                  d="M12 .5A12 12 0 000 12.6c0 5.3 3.4 9.8 8.2 11.4.6.1.8-.3.8-.6v-2.1c-3.3.7-4-1.6-4-1.6-.5-1.2-1.2-1.5-1.2-1.5-1-.7.1-.7.1-.7 1.1.1 1.7 1.1 1.7 1.1 1 .1.7 1.5 2.8 1.1.1-.9.4-1.5.8-1.9-2.7-.3-5.5-1.4-5.5-6.1 0-1.3.5-2.4 1.2-3.3-.1-.3-.5-1.6.1-3.3 0 0 1-.3 3.3 1.3a11.4 11.4 0 016 0C17.7 6 18.7 6.3 18.7 6.3c.6 1.7.2 3 .1 3.3.7.9 1.2 2 1.2 3.3 0 4.7-2.8 5.8-5.5 6.1.4.4.7 1.1.7 2.2v3.2c0 .3.2.7.8.6A12 12 0 0024 12.6 12 12 0 0012 .5z" />
              </svg>
            </a>
          </div>

          <div class="mt-4 card">
            <img src="../img/maps.png" alt="Map"
              class="h-40 w-full rounded-md border border-light object-cover sm:h-48" />
            <div class="mt-2 text-center text-sm text-slate-900 ">
              Click image to open full map
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

<?php
renderFooter([
  'scripts' => [
    '<script src="https://unpkg.com/motion@latest/dist/motion.umd.js"></script>',
  '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>',
  '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>',
    '<script src="../js/main.js"></script>',
    '<script src="../js/validation-integration.js"></script>',
    '<script src="../js/auth.js"></script>',
    '<script src="../js/reveal.js"></script>',
    '<script src="../js/reviews.js"></script>'
  ]
]);
?>