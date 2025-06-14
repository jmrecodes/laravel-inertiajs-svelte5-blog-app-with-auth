<script>
  /*
   * FORGOT PASSWORD / RESET PASSWORD PAGE - EDUCATIONAL AUTHENTICATION FLOW
   * ======================================================================
   * 
   * This Svelte component handles both the "forgot password" (email request) and
   * "reset password" (new password entry with token) functionalities.
   * It demonstrates a secure, token-based password reset flow and how to adapt
   * a single component's UI based on URL parameters.
   * 
   * 🎓 BEGINNER LEARNING OBJECTIVES:
   * ================================
   * 1. **Conditional UI Rendering**: Displaying different forms/messages based on external state (URL token).
   * 2. **URL Parameter Handling**: Extracting data from the URL query string (`?token=...&email=...`).
   * 3. **Inertia.js Form Submissions**: Sending `POST` and `PUT` requests for authentication flows.
   * 4. **User Feedback**: Providing clear success, error, and loading messages.
   * 5. **Security Considerations**: Understanding basic principles like token validity and expiration.
   * 
   * 🔍 WHAT YOU'LL LEARN:
   * =====================
   * - How to use Svelte's `$state` for local form data and UI flags.
   * - Implementing client-side validation for email and password fields.
   * - Integrating Laravel's server-side validation errors with Svelte's UI.
   * - Managing distinct form submission processes within one component.
   * - Displaying dynamic page titles and descriptions using `<svelte:head>`.
   * - Navigating between authentication-related pages (login, register).
   *
   * KEY SECTIONS OF THIS COMPONENT:
   * - Email Request Form: For users who forgot their password and need a reset link.
   * - Password Reset Form: For users who have a valid token and need to set a new password.
   * - Success State: Message displayed after a reset link is sent.
   * - Invalid Token State: Message displayed if the reset link is invalid or expired.
   */
  
  /*
   * IMPORTS AND DEPENDENCIES - SVELTE 5 + INERTIA.JS
   * ================================================
   * 
   * We import necessary modules for form submission and navigation.
   * 🎓 LEARN: How to import and use external functionality in Svelte.
   */
  import { router } from '@inertiajs/svelte' // Inertia.js router for form submissions and navigation
  import { Link } from '@inertiajs/svelte'     // Inertia.js Link component for SPA-like navigation
  
  /*
   * COMPONENT PROPS - DATA FROM LARAVEL CONTROLLER
   * ==============================================
   * 
   * These properties are passed to this Svelte component from the
   * `ForgotPasswordController::showForm()` method. Inertia.js automatically maps
   * the data returned by the controller to `$props` in Svelte.
   * 
   * 🎓 LEARN: How backend data flows to Svelte components to control UI state.
   * 
   * - `mode`: String, either 'request' (show email form) or 'reset' (show password form).
   * - `token`: String, the password reset token from the URL (if available).
   * - `email`: String, the user's email from the URL (if available).
   * - `tokenValid`: Boolean, indicates if the token is valid and not expired (checked server-side).
   * - `errors`: Object, validation errors sent back from Laravel after form submission.
   * - `auth`: Object, global authentication data (available on all Inertia pages).
   * - `flash`: Object, one-time flash messages (e.g., success messages from controller).
   */
  let { 
    mode = 'request',    
    token = '',          
    email = '',          
    tokenValid = false,  
    errors = {},         
    auth = {},           
    flash = {}           
  } = $props()
  
  /*
   * FORM STATE MANAGEMENT - SVELTE 5 RUNES ($STATE)
   * ===============================================
   * 
   * `$state` creates reactive variables that hold the current values of our form inputs.
   * We use separate state objects for the email request form and the password reset form
   * because their data structures are different.
   * 
   * 🎓 LEARN: How `$state` manages form data for different parts of a complex component.
   */
  
  // 1. Email Request Form State (for sending reset link)
  let emailForm = $state({
    email: '' // Stores the email entered by the user
  })
  
  // 2. Password Reset Form State (for changing password with token)
  let resetForm = $state({
    token: token || '',              // Pre-fill with token from URL prop
    email: email || '',              // Pre-fill with email from URL prop
    password: '',
    password_confirmation: ''
  })
  
  /*
   * UI STATE MANAGEMENT ($STATE)
   * ============================
   * 
   * These variables control the interactive elements and loading indicators of the UI.
   * 🎓 LEARN: How `$state` manages temporary UI states (e.g., loading spinners, success messages).
   */
  let processing = $state(false) // True when either form is submitting
  let showSuccess = $state(false) // True when the email has been successfully sent (for `request` mode)
  
  /*
   * COMPUTED VALUES - SVELTE 5 DERIVED STATE ($DERIVED)
   * ====================================================
   * 
   * `$derived` creates values that automatically re-calculate whenever their
   * dependencies (`$state` variables) change. This is perfect for real-time
   * form validation status.
   * 🎓 LEARN: How `$derived` simplifies reactive logic for form validation.
   */
  let emailFormValid = $derived(
    // Check if email is not empty and contains '@' (basic client-side validation)
    emailForm.email.trim().length > 0 && 
    emailForm.email.includes('@')
  )
  
  let resetFormValid = $derived(
    // Check if new password meets basic length, matches confirmation, and token/email are present
    resetForm.password.length >= 8 && // Basic client-side check, server will do full validation
    resetForm.password === resetForm.password_confirmation &&
    resetForm.email.length > 0 &&
    resetForm.token.length > 0
  )
  
  /*
   * FORM SUBMISSION HANDLERS - INERTIA.JS `router` METHODS
   * =====================================================
   * 
   * These functions handle submitting data to the Laravel backend using Inertia.js's `router`.
   * Each function manages its own loading state and reacts to success or error responses.
   * 🎓 LEARN: The proper way to make AJAX-like form submissions with Inertia.js + Svelte 5.
   */
  
  /**
   * HANDLE EMAIL REQUEST FORM SUBMISSION
   * ------------------------------------
   * Sends a `POST` request to the backend to request a password reset link.
   * Corresponds to `ForgotPasswordController::sendResetLink()`.
   */
  function handleEmailSubmit(event) {
    event.preventDefault() // Prevent default browser form submission (full page reload)
    
    if (!emailFormValid) return // Prevent submission if client-side validation fails
    
    processing = true // Set processing state to show loading indicator
    
    router.post('/forgot-password', emailForm, { // Send POST request to /forgot-password
      onFinish: () => {
        processing = false // Always reset processing state after request finishes
      },
      onSuccess: () => {
        emailForm.email = '' // Clear email field on success
        showSuccess = true // Display success message UI
        console.log('✅ Password reset link sent (or simulated) successfully!')
      },
      onError: (serverErrors) => {
        console.error('❌ Reset link send failed:', serverErrors) // Log errors for debugging
        processing = false // Reset processing state on error
        // Inertia automatically passes errors to $props.errors
      }
    })
  }
  
  /**
   * HANDLE PASSWORD RESET FORM SUBMISSION
   * -------------------------------------
   * Sends a `PUT` request to the backend to reset the user's password with the provided token.
   * Corresponds to `ForgotPasswordController::resetPassword()`.
   */
  function handleResetSubmit(event) {
    event.preventDefault() // Prevent default browser form submission
    
    if (!resetFormValid) return // Prevent submission if client-side validation fails
    
    processing = true // Set processing state
    
    router.put('/forgot-password', resetForm, { // Send PUT request to /forgot-password
      onFinish: () => {
        processing = false // Always reset processing state
      },
      onSuccess: () => {
        // Laravel controller will handle redirect to login page on success,
        // so no manual action needed here. Flash message will be displayed there.
        console.log('✅ Password reset successful!')
      },
      onError: (serverErrors) => {
        console.error('❌ Password reset failed:', serverErrors) // Log errors
        processing = false // Reset processing state on error
        // Inertia automatically passes errors to $props.errors
      }
    })
  }
  
  /**
   * NAVIGATION HELPER FUNCTION
   * -------------------------
   * A simple utility function to navigate to different parts of the application.
   * `router.visit()` performs an Inertia.js visit, preventing full page reloads.
   */
  function navigateTo(href) {
    router.visit(href)
  }
</script>

<!--
  DYNAMIC PAGE HEAD - SEO OPTIMIZATION FOR FORGOT PASSWORD PAGE
  =============================================================
  
  `<svelte:head>` is used to set page-specific meta tags for search engines and browser tabs.
  For authentication-related pages, we typically set `noindex, nofollow` to prevent indexing.
  
  🎓 LEARN: How `<svelte:head>` manages dynamic HTML `<head>` content and `robots` directives.
-->
<svelte:head>
  <title>{mode === 'reset' ? 'Reset Your Password' : 'Forgot Your Password'} | jmrecodes Educational Blog</title>
  <meta name="description" content={mode === 'reset' ? 'Enter your new password to regain access to your account' : 'Enter your email address to receive a password reset link'} />
  <meta name="robots" content="noindex, nofollow" /> <!-- Prevent search engines from indexing authentication forms -->
</svelte:head>

<!--
  MAIN CONTENT CONTAINER - OVERALL PAGE LAYOUT
  ===========================================
  
  This `div` defines the main structure and background for the forgot password page,
  applying a minimum height and background color for a consistent look.
  It uses responsive design principles with Tailwind CSS utilities.
-->
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
  <div class="sm:mx-auto sm:w-full sm:max-w-md">
    <!-- 
      LOGO AND PAGE TITLE HEADER
      ==========================
      
      Displays the application logo and a dynamic title/subtitle based on the current mode.
     -->
    <div class="text-center">
      <!-- Link to Home Page via Logo -->
      <Link href="/" class="inline-block">
        <div class="mx-auto h-12 w-12 bg-gradient-to-br from-logo-navy to-logo-cyan rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-xl">EB</span>
        </div>
      </Link>
      <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
        {#if mode === 'reset'}
          Reset Your Password
        {:else}
          Forgot Your Password?
        {/if}
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        {#if mode === 'reset'}
          Enter your new password below
        {:else if showSuccess}
          Check your email for reset instructions
        {:else}
          Enter your email to receive a reset link
        {/if}
      </p>
    </div>
  </div>

  <!-- 
    FORM CONTAINER - EMAIL REQUEST OR PASSWORD RESET
    ================================================
    
    This section conditionally renders either the email request form, the password
    reset form, or an invalid token message based on the `mode` and `tokenValid` props.
   -->
  <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
      
      <!-- Flash Messages (e.g., general success messages from Laravel) -->
      {#if flash.success}
        <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="ml-3">
              <p class="text-sm font-medium text-green-800">{flash.success}</p>
            </div>
          </div>
        </div>
      {/if}
      
      <!-- Error Messages (e.g., token invalid/expired from Laravel) -->
      {#if errors.token}
        <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <div class="ml-3">
              <p class="text-sm font-medium text-red-800">{errors.token}</p>
            </div>
          </div>
        </div>
      {/if}
      
      {#if mode === 'request' && !showSuccess} <!-- Show email request form if mode is 'request' and no success message yet -->
        <!-- 
          EMAIL REQUEST FORM
          ==================
          This form allows the user to enter their email address to receive a password reset link.
         -->
        <form onsubmit={handleEmailSubmit} class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Email Address <span class="text-red-500">*</span>
            </label>
            <input
              type="email"
              id="email"
              bind:value={emailForm.email}
              placeholder="Enter your email address"
              required
              autocomplete="email"
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                     focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                     disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                     class:border-red-500={errors?.email}
                     disabled={processing} 
            />
            {#if errors.email}
              <p class="mt-1 text-sm text-red-600">{errors.email}</p>
            {/if}
            <p class="mt-1 text-sm text-gray-500">
              We'll send a password reset link to this email address.
            </p>
          </div>
          
          <div>
            <button
              type="submit"
              disabled={!emailFormValid || processing}
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium
                     text-white bg-accent-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500
                     disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {#if processing}
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Sending Reset Link...
              {:else}
                Send Reset Link
              {/if}
            </button>
          </div>
        </form>
        
      {:else if mode === 'request' && showSuccess} <!-- Show success message if email was sent -->
        <!-- 
          SUCCESS STATE MESSAGE
          =====================
          This section informs the user that a password reset link has been sent to their email.
         -->
        <div class="text-center space-y-4">
          <!-- Success Icon -->
          <div class="mx-auto h-16 w-16 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">Check Your Email</h3>
            <p class="mt-2 text-sm text-gray-600">
              If an account with that email exists, we've sent a password reset link. 
              Please check your email and click the link to reset your password.
            </p>
            <p class="mt-2 text-xs text-gray-500">
              The link will expire in 1 hour for security reasons.
            </p>
          </div>
          <button
            onclick={() => { showSuccess = false; emailForm.email = '' }} 
            class="text-sm text-accent-600 hover:text-accent-500 font-medium"
          >
            Send another reset link
          </button>
        </div>
        
      {:else if mode === 'reset' && tokenValid} <!-- Show password reset form if mode is 'reset' and token is valid -->
        <!-- 
          PASSWORD RESET FORM
          ===================
          This form allows the user to enter and confirm a new password using a valid token.
         -->
        <form onsubmit={handleResetSubmit} class="space-y-6">
          <!-- Hidden fields for token and email (essential for backend validation) -->
          <input type="hidden" bind:value={resetForm.token} />
          <input type="hidden" bind:value={resetForm.email} />
          
          <!-- Information message about the email being reset -->
          <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
              <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div class="ml-3">
                <p class="text-sm text-blue-800">
                  Setting new password for: <span class="font-medium">{email}</span>
                </p>
              </div>
            </div>
          </div>
          
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              New Password <span class="text-red-500">*</span>
            </label>
            <input
              type="password"
              id="password"
              bind:value={resetForm.password}
              placeholder="Enter new password (min. 8 characters)"
              required
              minlength="8"
              autocomplete="new-password"
              class={`block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                     focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                     disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm
                     ${errors?.password ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : ''}`}
              disabled={processing}
            />
            {#if errors.password} <!-- Displays server-side validation error for 'password' -->
              <p class="mt-1 text-sm text-red-600">{errors.password}</p>
            {/if}
            <p class="mt-1 text-sm text-gray-500">Must be at least 8 characters with letters and numbers.</p>
          </div>
          
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
              Confirm New Password <span class="text-red-500">*</span>
            </label>
            <input
              type="password"
              id="password_confirmation"
              bind:value={resetForm.password_confirmation}
              placeholder="Confirm your new password"
              required
              autocomplete="new-password"
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                     focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                     disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                     disabled={processing}
            />
            {#if resetForm.password !== resetForm.password_confirmation && resetForm.password_confirmation.length > 0}
              <p class="mt-1 text-sm text-red-600">Passwords do not match.</p>
            {/if}
          </div>
          
          <div>
            <button
              type="submit"
              disabled={!resetFormValid || processing}
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium
                     text-white bg-accent-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500
                     disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {#if processing}
                <!-- Loading spinner SVG -->
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Resetting Password...
              {:else}
                Reset Password
              {/if}
            </button>
          </div>
        </form>
        
      {:else} <!-- Show invalid token message if mode is 'reset' but token is invalid -->
        <!-- 
          INVALID TOKEN STATE MESSAGE
          ===========================
          This section informs the user that their reset link is invalid or expired
          and prompts them to request a new one.
         -->
        <div class="text-center space-y-4">
          <!-- Error Icon -->
          <div class="mx-auto h-16 w-16 bg-red-100 rounded-full flex items-center justify-center">
            <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">Invalid Reset Link</h3>
            <p class="mt-2 text-sm text-gray-600">
              This password reset link is invalid or has expired. Reset links are only valid for 1 hour for security reasons.
            </p>
          </div>
          <button
            onclick={() => navigateTo('/forgot-password')}
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md
                   text-white bg-accent-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500"
          >
            Request New Reset Link
          </button>
        </div>
      {/if}
      
      <!-- 
        NAVIGATION LINKS - FOOTER NAVIGATION
        ====================================
        
        Provides quick navigation to other authentication-related pages.
       -->
      <div class="mt-6 space-y-2">
        <!-- Back to Login Link -->
        {#if !auth.user}
        <div class="text-center">
          <Link 
            href="/login" 
            class="text-sm text-accent-600 hover:text-accent-500 font-medium"
          >
            ← Back to Login
          </Link>
        </div>
        <div class="text-center">
          <!-- Register link is only shown in development mode -->
          {#if import.meta.env.DEV || import.meta.env.MODE === 'development'}
          <Link 
            href="/register" 
            class="text-sm text-accent-600 hover:text-accent-500 font-medium"
          >
              Sign up
            </Link>
          {/if}
        </div>
        {:else}
        <div class="text-center">
          <Link 
            href="/dashboard" 
            class="text-sm text-accent-600 hover:text-accent-500 font-medium"
          >
            ← Back to Dashboard
          </Link>
        </div>
        {/if}
        
        <!-- Return to Home Link -->
        <div class="text-center">
          <Link 
            href="/" 
            class="text-sm text-gray-500 hover:text-gray-700"
          >
            Return to Home
          </Link>
        </div>
      </div>
    </div>
  </div>
</div>

<!--
  🎓 EDUCATIONAL SUMMARY - FORGOT PASSWORD COMPONENT
  ==================================================
  
  This `Auth/ForgotPassword.svelte` component is a comprehensive example of building a secure
  and user-friendly password reset interface in a modern web application.
  
  ✅ KEY LEARNINGS:
  - **Dynamic UI States**: Seamlessly transitioning between different forms and messages based on URL data.
  - **Client-Side Validation**: Providing immediate feedback for form inputs.
  - **Inertia.js Workflow**: Handling POST and PUT requests for authentication features.
  - **User Experience**: Clear instructions, loading indicators, and informative success/error states.
  - **Security Principles**: Reflecting backend security logic (e.g., invalid token handling) in the UI.
  
  This component serves as a robust foundation for any authentication flow
  in your web application, focusing on both functionality and user experience.
-->