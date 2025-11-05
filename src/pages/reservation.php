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
$title = "BOOK YOUR";
$highlight = 'RESERVATION';
$subtitle = "Reserve your appointment with Promise Atelier for fittings, consultations, or custom designs.";
$extra_class = "py-32";

include('../components/hero.html');
?>

  <div class="mx-auto max-w-screen-xl px-4 py-20">
    <!-- Reservation Form -->
    <main class="mx-auto max-w-screen-xl px-4 py-8">
    <section class="mb-12 card grid grid-cols-1 gap-6 lg:grid-cols-2">
      <div>
        <h3 class="mb-2 text-lg font-semibold text-dark sm:text-xl">
          Reservation Form
        </h3>
        <p class="mb-4 text-sm text-neutral sm:text-base">
          Fill in your details below to schedule your visit. We’ll confirm your reservation via email.
        </p>

        <form id="reservation-form" class="space-y-4" novalidate>
          <div>
            <label for="res-name" class="form-label">Full Name <span class="text-red-500">*</span></label>
            <input id="res-name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" autocomplete="name" class="form-input" />
          </div>

          <div>
            <label for="res-email" class="form-label">Email <span class="text-red-500">*</span></label>
            <input id="res-email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" autocomplete="email" class="form-input" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="res-date" class="form-label">Preferred Date <span class="text-red-500">*</span></label>
              <input type="date" id="res-date" name="date" class="form-input" />
            </div>
            <div>
              <label for="res-time" class="form-label">Preferred Time <span class="text-red-500">*</span></label>
              <input type="time" id="res-time" name="time" class="form-input" />
            </div>
          </div>

          <div>
            <label for="res-service" class="form-label">Service Type <span class="text-red-500">*</span></label>
            <select id="res-service" name="service" class="form-input">
              <option value="" disabled selected>Select a service</option>
              <option value="fitting">Fitting Appointment</option>
              <option value="consultation">Style Consultation</option>
              <option value="custom-design">Custom Design Request</option>
            </select>
          </div>

          <div>
            <label for="res-notes" class="form-label">Additional Notes</label>
            <textarea id="res-notes" name="notes" class="form-input h-32 resize-none"></textarea>
          </div>

          <button type="submit" class="btn-primary w-full">
            Submit Reservation
          </button>
        </form>
      </div>

      <div>
        <h3 class="mb-2 text-lg font-semibold text-pink-800 sm:text-xl">
          Visit Our Atelier
        </h3>
        <div class="mb-4 text-sm text-pink-700/80 sm:text-base">
          <p>Promise Atelier</p>
          <p>0321 December Avenue</p>
          <p>Philippines</p>
          <p>
            Email:
            <a href="mailto:promiseatelier@email.com" class="text-pink-600 underline">promiseatelier@email.com</a>
          </p>
          <p>
            Phone:
            <a href="tel:+639121234567" class="text-pink-600 underline">+63 912 123 4567</a>
          </p>
        </div>

        <div class="mt-2">
          <div class="mb-3 font-medium text-neutral">Follow us:</div>
          <div class="flex items-center gap-4">
            <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-light bg-white text-primary shadow transition-transform duration-200 hover:scale-110 hover:shadow-soft" aria-label="Facebook">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M22 12a10 10 0 10-11.5 9.9v-7H8.5v-3h2V9.1c0-2 1.2-3.1 3-3.1.9 0 1.8.1 1.8.1v2h-1c-1 0-1.3.6-1.3 1.2V12h2.2l-.4 3h-1.8v7A10 10 0 0022 12z"/></svg>
            </a>
            <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-light bg-white text-primary shadow transition-transform duration-200 hover:scale-110 hover:shadow-soft" aria-label="Instagram">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm5 6.2A3.8 3.8 0 1015.8 12 3.8 3.8 0 0012 8.2zm4.9-.9a1.1 1.1 0 11-1.1-1.1 1.1 1.1 0 011.1 1.1zM12 9.5A2.5 2.5 0 1114.5 12 2.5 2.5 0 0112 9.5z"/></svg>
            </a>
            <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-light bg-white text-primary shadow transition-transform duration-200 hover:scale-110 hover:shadow-soft" aria-label="GitHub">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M12 .5A12 12 0 000 12.6c0 5.3 3.4 9.8 8.2 11.4.6.1.8-.3.8-.6v-2.1c-3.3.7-4-1.6-4-1.6-.5-1.2-1.2-1.5-1.2-1.5-1-.7.1-.7.1-.7 1.1.1 1.7 1.1 1.7 1.1 1 .1.7 1.5 2.8 1.1.1-.9.4-1.5.8-1.9-2.7-.3-5.5-1.4-5.5-6.1 0-1.3.5-2.4 1.2-3.3-.1-.3-.5-1.6.1-3.3 0 0 1-.3 3.3 1.3a11.4 11.4 0 016 0C17.7 6 18.7 6.3 18.7 6.3c.6 1.7.2 3 .1 3.3.7.9 1.2 2 1.2 3.3 0 4.7-2.8 5.8-5.5 6.1.4.4.7 1.1.7 2.2v3.2c0 .3.2.7.8.6A12 12 0 0024 12.6 12 12 0 0012 .5z"/></svg>
            </a>
          </div>

          <div class="mt-4 card">
            <img src="../img/maps.png" alt="Map" class="h-40 w-full rounded-md border border-light object-cover sm:h-48" />
            <div class="mt-2 text-center text-sm text-primary">
              Click image to open full map
            </div>
          </div>
        </div>
      </div>
    </section>
    </main>

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