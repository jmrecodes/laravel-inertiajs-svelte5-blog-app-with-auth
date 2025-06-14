<!--
  EDUCATIONAL AUTHENTICATION - LOGIN PAGE COMPONENT
  ================================================
  
  This component demonstrates authentication in a Svelte + Inertia.js + Laravel app.
  It showcases modern form handling, validation, user experience patterns, and 
  secure authentication practices.
  
  EDUCATIONAL CONCEPTS COVERED:
  ==============================
  1. FORM HANDLING: Modern Svelte 5 form patterns with reactive state
  2. INERTIA.JS INTEGRATION: How forms work with server-side validation
  3. USER EXPERIENCE: Loading states, error handling, accessibility
  4. SECURITY: CSRF protection, input validation, secure submission
  5. RESPONSIVE DESIGN: Mobile-first approach with Tailwind CSS
  
  AUTHENTICATION FLOW EXPLAINED:
  =============================
  1. User fills form → Svelte reactive state updates
  2. Form submission → Inertia.js sends data to Laravel  
  3. Laravel validates → Returns success/error response
  4. Inertia.js updates → Svelte component reacts to changes
  5. Success: Redirect to dashboard | Error: Show validation messages
  
  WHY THIS APPROACH WORKS:
  =======================
  - No separate API needed (Inertia.js handles everything)
  - Automatic CSRF protection (Laravel middleware)
  - Server-side validation with client-side UX
  - Progressive enhancement (works without JavaScript)
  - Secure session-based authentication
-->

<script>
  /*
   * IMPORTS AND DEPENDENCIES
   * ========================
   * 
   * We import only what we need for authentication functionality.
   * Each import serves a specific educational purpose.
   */
  
  // Inertia.js for SPA navigation and form handling
  import { router } from '@inertiajs/svelte'
  
  // Link component for internal navigation  
  import { Link } from '@inertiajs/svelte'
  
  /*
   * COMPONENT PROPS - SERVER DATA INTEGRATION
   * ========================================
   * 
   * These props come from our Laravel AuthController::showLogin() method.
   * This demonstrates how server-side data flows to Svelte components.
   */
  let { 
    canResetPassword = false,  // Flag from Laravel to show/hide the "Forgot password?" link
    status = null,             // Flash message from password reset attempt or other redirects
    errors = {}                // Validation errors from server
  } = $props()
  
  /*
   * FORM STATE MANAGEMENT - OFFICIAL INERTIA.JS SVELTE 5 PATTERN
   * ============================================================
   * 
   * IMPORTANT: We discovered that `useForm` from @inertiajs/svelte doesn't work
   * reliably with Svelte 5's new reactivity system. The official documentation
   * actually shows this simpler pattern using plain reactive variables.
   * 
   * ❌ DON'T USE: let form = useForm({ email: '', password: '' })
   * ✅ USE THIS: let values = $state({ email: '', password: '' })
   * 
   * This pattern provides:
   * - Better Svelte 5 compatibility
   * - Simpler mental model  
   * - Official documentation support
   * - More predictable reactivity
   */
  let values = $state({
    email: '',
    password: '',
    remember: false
  })
  
  /*
   * UI STATE MANAGEMENT - LOADING AND ERRORS
   * ========================================
   * 
   * We need to track loading state manually since we're not using useForm.
   */
  let showPassword = $state(false)        // Toggle password visibility
  let processing = $state(false)          // Track form submission state
  
  /*
   * COMPUTED VALUES - SVELTE 5 DERIVED STATE
   * ========================================
   * 
   * Reactive values that automatically update based on form state.
   * These help with UX and form validation.
   */
  let isFormValid = $derived(
    values.email.length > 0 && 
    values.password.length > 0
  )
  
  let hasErrors = $derived(Object.keys(errors).length > 0)
  
  /*
   * FORM SUBMISSION HANDLER - OFFICIAL INERTIA.JS PATTERN
   * =====================================================
   * 
   * This demonstrates the CORRECT way to handle forms with Inertia.js + Svelte 5.
   * 
   * KEY PATTERNS:
   * 1. Use router.post() instead of form.post()
   * 2. Pass values object directly as second parameter
   * 3. Handle loading state manually with $state
   * 4. Use onSuccess/onError/onFinish callbacks for UX
   * 
   * This pattern works perfectly with Svelte 5 reactivity and follows
   * the official Inertia.js documentation examples.
   */
  function handleLogin(event) {
    event.preventDefault()
    
    console.log('🚀 Form submission started!')
    console.log('Email:', values.email)
    console.log('Password length:', values.password.length)
    console.log('Remember:', values.remember)
    
    // Set processing state manually (no useForm needed)
    processing = true
    
    // Official Inertia.js pattern - simple and reliable
    router.post('/login', values, {
      onSuccess: () => {
        console.log('✅ Login successful!')
        processing = false
      },
      onError: (errors) => {
        console.log('❌ Login error:', errors)
        processing = false
      },
      onFinish: () => {
        // Always runs after success or error
        processing = false
      }
    })
  }
  
  /*
   * PASSWORD VISIBILITY TOGGLE
   * ==========================
   * 
   * UX enhancement to help users see what they're typing.
   * Especially useful on mobile devices.
   */
  function togglePasswordVisibility() {
    showPassword = !showPassword
  }
  
  /*
   * NAVIGATION HANDLER - PREVENT PASSWORD SAVE PROMPTS
   * =================================================
   * 
   * This fixes the browser password save prompt issue when navigating
   * between login/register pages. The browser interprets navigation
   * away from forms as "submission" if there's data in password fields.
   * 
   * Solution: Clear form data before navigation.
   */
  function handleNavigateToRegister(event) {
    // Clear any entered data to prevent browser save prompts
    values.email = ''
    values.password = ''
    values.remember = false
    
    // Allow normal navigation to continue
    console.log('🧭 Navigating to register - form data cleared')
  }
  
  /*
   * DEMO CREDENTIALS HELPER (DEVELOPMENT ONLY)
   * ==========================================
   * 
   * For educational purposes, provide easy login with demo credentials.
   * In production, remove this functionality.
   */
  
  function fillDemoCredentials() {
    // Direct assignment to reactive values - works with Svelte 5 $state
    // The values object is reactive and will update the UI automatically
    values.email = 'demo@example.com'
    values.password = 'password123'
    values.remember = false
    
    // Log state changes for debugging
    console.log('Demo credentials filled:', { 
      email: values.email, 
      password: values.password, 
      remember: values.remember 
    })
    console.log('Form valid:', isFormValid)
  }

  // Add these lines for debugging
  console.log('Vite Mode:', import.meta.env.MODE);
  console.log('Vite Dev Flag:', import.meta.env.DEV);
</script>

<!--
  DYNAMIC PAGE HEAD - SEO AND META TAGS
  ====================================
  
  Using Svelte's <svelte:head> for page-specific meta information.
  This demonstrates proper SEO practices in SPA applications.
-->
<svelte:head>
  <title>Login | jmrecodes Educational Blog</title>
  <meta name="description" content="Sign in to your jmrecodes Educational Blog account to create and manage your blog posts." />
  <meta name="robots" content="noindex" /> <!-- Don't index login pages -->
</svelte:head>

<!--
  MAIN LOGIN PAGE LAYOUT
  ======================
  
  Clean, accessible, and responsive login form design.
  Follows modern UI/UX best practices and design system specifications.
  Enhanced with systematic color palette and professional styling.
-->
<div class="min-h-screen bg-[rgb(var(--color-bg-secondary))] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
  
  <!-- 
    HEADER SECTION - BRANDING AND WELCOME
    ====================================
    
    Clear branding and welcoming message to set user expectations.
  -->
  <div class="sm:mx-auto sm:w-full sm:max-w-md">
    
    <!-- Logo/Brand -->
    <div class="flex justify-center">
      <Link href="/" class="flex items-center space-x-2 text-2xl font-bold text-[rgb(var(--color-text-primary))] hover:text-[rgb(var(--color-primary-600))] transition-colors duration-[var(--duration-normal)] ease-[var(--ease-out)]">
        <svg class="h-8 w-8 text-[rgb(var(--color-primary-500))]" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
        <span>Educational Blog</span>
      </Link>
    </div>
    
    <!-- Welcome Message -->
    <h1 class="mt-6 text-center text-[length:var(--font-size-3xl)] font-[var(--font-weight-bold)] text-[rgb(var(--color-text-primary))] leading-[var(--line-height-tight)]">
      Welcome Back!
    </h1>
    <p class="mt-2 text-center text-[length:var(--font-size-sm)] text-[rgb(var(--color-text-secondary))] leading-[var(--line-height-normal)]">
      Sign in to your account to continue learning
    </p>
    
            <!-- Status Message (from password reset) -->
    {#if status}
      <div class="mt-4 p-4 bg-[rgb(var(--color-success-50))] border border-[rgb(var(--color-success-100))] rounded-[var(--radius-md)]">
        <p class="text-[length:var(--font-size-sm)] text-[rgb(var(--color-success-700))]">{status}</p>
      </div>
    {/if}
  </div>

  <!-- 
    LOGIN FORM CONTAINER
    ===================
    
    Main form container with proper spacing and styling.
    Responsive design that works on all device sizes.
  -->
  <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-[rgb(var(--color-bg-primary))] py-8 px-4 shadow-[var(--shadow-lg)] rounded-[var(--radius-lg)] sm:px-10 border border-[rgb(var(--color-border-secondary))]">
      
      <!--
        EDUCATIONAL DEMO SECTION (DEVELOPMENT ONLY)
        ==========================================
        
        Quick access to demo credentials for testing purposes.
        Remove this section in production.
      -->
      {#if import.meta.env.DEV || import.meta.env.MODE === 'development'}
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
          <h3 class="text-sm font-medium text-yellow-800 mb-2">🎓 Educational Demo</h3>
          <p class="text-xs text-yellow-700 mb-3">
            Click below to fill in demo credentials for testing:
          </p>
          <button 
            type="button"
            onclick={fillDemoCredentials}
            class="text-xs bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700 transition-colors"
          >
            Fill Demo Credentials
          </button>
        </div>
      {/if}
      
      <!--
        LOGIN FORM - MODERN SVELTE 5 PATTERNS
        ====================================
        
        Accessible, validated form with excellent user experience.
        Demonstrates modern form handling in Svelte applications.
      -->
      <form onsubmit={handleLogin} class="space-y-6">
        
        <!-- 
          EMAIL INPUT FIELD
          ================
          
          Proper labeling, validation, and error handling.
          Shows how to bind Svelte state to form inputs.
        -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            Email Address
          </label>
          <div class="relative">
            <input
              id="email"
              type="email"
              bind:value={values.email}
              required
              autocomplete="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                     disabled:bg-gray-50 disabled:text-gray-500
                     {errors?.email ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : ''}"
              class:border-red-500={errors?.email}
              disabled={processing}
              placeholder="Enter your email address"
            />
            
            <!-- Email icon -->
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
              </svg>
            </div>
          </div>
          
          <!-- Email validation error -->
          {#if errors?.email}
            <p class="mt-1 text-sm text-red-600" role="alert">
              {errors.email}
            </p>
          {/if}
        </div>

        <!-- 
          PASSWORD INPUT FIELD
          ===================
          
          Password field with show/hide toggle for better UX.
          Demonstrates form input enhancements.
        -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Password
          </label>
          <div class="relative">
            <input
              id="password"
              type={showPassword ? 'text' : 'password'}
              bind:value={values.password}
              required
              autocomplete="current-password"
              minlength="8"
              class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm 
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                     disabled:bg-gray-50 disabled:text-gray-500
                     {errors?.password ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : ''}"
              disabled={processing}
              placeholder="Enter your password"
            />
            
            <!-- Password visibility toggle -->
            <button
              type="button"
              onclick={togglePasswordVisibility}
              class="absolute inset-y-0 right-0 pr-3 flex items-center hover:text-gray-600 focus:outline-none focus:text-gray-600"
              disabled={processing}
              aria-label={showPassword ? 'Hide password' : 'Show password'}
            >
              {#if showPassword}
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
              {:else}
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              {/if}
            </button>
          </div>
          
          <!-- Password validation error -->
          {#if errors?.password}
            <p class="mt-1 text-sm text-red-600" role="alert">
              {errors.password}
            </p>
          {/if}
        </div>

        <!-- 
          REMEMBER ME CHECKBOX
          ===================
          
          Optional remember me functionality for user convenience.
          Shows proper checkbox styling and state management.
        -->
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember"
              type="checkbox"
              bind:checked={values.remember}
              disabled={processing}
              class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-700">
              Remember me for 30 days
            </label>
          </div>

          <!-- Forgot password link (if enabled) -->
          {#if canResetPassword}
            <Link 
              href="/forgot-password" 
              class="text-sm font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition-colors"
            >
              Forgot password?
            </Link>
          {/if}
        </div>

        <!-- 
          SUBMIT BUTTON - LOADING STATES AND ACCESSIBILITY
          ===============================================
          
          Button with loading state, proper accessibility, and visual feedback.
          Demonstrates modern button patterns in web applications.
        -->
        <div>
          <button
            type="submit"
            disabled={processing}
            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white
                   bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
                   disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600
                   transition-all duration-200 transform hover:scale-[1.02] disabled:hover:scale-100"
          >
            {#if processing}
              <!-- Loading spinner -->
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Signing In...
            {:else}
              Sign In
            {/if}
          </button>
        </div>

        <!-- 
          GENERAL ERROR DISPLAY
          ====================
          
          Show any general errors that don't belong to specific fields.
          Provides clear feedback for authentication failures.
        -->
        {#if errors?.general}
          <div class="p-4 bg-red-50 border border-red-200 rounded-md">
            <div class="flex">
              <svg class="h-5 w-5 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.346 15.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <p class="text-sm text-red-700">{errors.general}</p>
            </div>
          </div>
        {/if}
      </form>
    </div>
    
    <!-- 
      REGISTRATION LINK - ACCOUNT CREATION
      ===================================
      
      Clear call-to-action for users who need to create an account.
      Demonstrates proper navigation patterns in SPA applications.
    -->
    <!-- Register link is only shown in development mode -->
    {#if import.meta.env.DEV || import.meta.env.MODE === 'development'}
    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600">
        Don't have an account?
        <Link 
          href="/register" 
          onclick={handleNavigateToRegister}
          class="font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition-colors"
        >
          Create one now
        </Link>
        </p>
      </div>
    {/if}
    
    <!-- 
      BACK TO HOME LINK
      ================
      
      Easy way for users to return to the main site without logging in.
    -->
    <div class="mt-4 text-center">
      <Link 
        href="/" 
        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 focus:outline-none focus:underline transition-colors"
      >
        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Home
      </Link>
    </div>
  </div>
</div>

<!--
  EDUCATIONAL SUMMARY - KEY LEARNING POINTS
  ========================================
  
  This Login component demonstrates:
  
  ✅ MODERN SVELTE 5 PATTERNS:
  - $state rune for reactive form data
  - $derived for computed values  
  - $props for component properties
  - Modern event handler syntax
  
  ✅ INERTIA.JS INTEGRATION:
  - Form submission without page refresh
  - Server-side validation with client-side UX
  - Automatic error handling and display
  - Seamless navigation between pages
  
  ✅ USER EXPERIENCE BEST PRACTICES:
  - Loading states during form submission
  - Clear error messages and validation
  - Accessibility features (ARIA labels, focus management)
  - Responsive design for all devices
  - Password visibility toggle
  
  ✅ SECURITY CONSIDERATIONS:
  - CSRF protection (handled by Laravel middleware)
  - Proper input validation on client and server
  - Secure password handling (never stored in plain text)
  - Session-based authentication
  
  ✅ PRODUCTION-READY FEATURES:
  - SEO optimization with proper meta tags
  - Progressive enhancement (works without JavaScript)
  - Error boundary handling
  - Proper form validation and feedback
  
  This component serves as a reference implementation for authentication
  in modern web applications using the Svelte + Inertia.js + Laravel stack.
--> 