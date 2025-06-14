<!--
  EDUCATIONAL AUTHENTICATION - REGISTRATION PAGE COMPONENT
  =======================================================
  
  This component demonstrates user registration in a modern web application.
  It builds upon the login component patterns while adding registration-specific
  features like password confirmation and terms acceptance.
  
  EDUCATIONAL CONCEPTS COVERED:
  ============================
  1. REGISTRATION FORMS: Advanced form patterns with multiple validation rules
  2. PASSWORD CONFIRMATION: Client-side validation for matching passwords
  3. TERMS OF SERVICE: Legal compliance patterns in web applications
  4. PROGRESSIVE VALIDATION: Real-time feedback during form completion
  5. ACCESSIBILITY: Screen reader support and keyboard navigation
  
  REGISTRATION FLOW EXPLAINED:
  ===========================
  1. User fills registration form → Reactive validation provides immediate feedback
  2. Form submission → Inertia.js sends data to Laravel with CSRF protection
  3. Laravel validates → Creates user account and logs them in automatically
  4. Success: Redirect to dashboard with welcome message
  5. Failure: Return validation errors with preserved form state
  
  SECURITY CONSIDERATIONS:
  =======================
  - Password strength requirements enforced
  - Email format validation on client and server
  - CSRF tokens automatically included
  - Secure password hashing on server side
  - No sensitive data stored in browser storage
-->

<script>
  /*
   * IMPORTS AND DEPENDENCIES
   * ========================
   */
  import { router } from '@inertiajs/svelte' // Inertia.js router for client-side navigation   
  import { Link } from '@inertiajs/svelte'     // Inertia.js Link component for SPA-like navigation
  
  /*
   * COMPONENT PROPS - SERVER CONFIGURATION
   * =====================================
   * 
   * Props passed from Laravel AuthController::showRegister() method.
   * These control feature availability and legal links.
   */
  let { 
    termsUrl = '/terms',
    privacyUrl = '/privacy',
    errors = {}                // Validation errors from server
  } = $props()
  
  /*
   * REGISTRATION FORM STATE - OFFICIAL INERTIA.JS SVELTE 5 PATTERN
   * ==============================================================
   * 
   * CONSISTENT PATTERN: Using the same approach as Login.svelte for consistency.
   * This demonstrates how the router.post() pattern scales to complex forms.
   * 
   * EDUCATIONAL NOTE: This form has more complexity (validation, password
   * confirmation, terms acceptance) but uses the exact same fundamental
   * pattern as the login form. This consistency makes the codebase easier
   * to understand and maintain.
   * 
   * The key insight: Simple patterns work for both simple and complex forms.
   */
  let values = $state({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false
  })
  
  /*
   * UI STATE MANAGEMENT - ENHANCED UX TRACKING
   * ==========================================
   */
  let showPassword = $state(false)
  let showConfirmPassword = $state(false)
  let focusedField = $state(null)        // Track which field has focus
  let processing = $state(false)          // Track form submission state
  
  /*
   * VALIDATION STATE - REAL-TIME FEEDBACK
   * ====================================
   * 
   * Progressive validation that provides immediate feedback as users type.
   * This improves UX by catching issues before form submission.
   */
  let validation = $derived({
    name: {
      isValid: values.name.length >= 2,
      message: values.name.length === 0 ? '' : 
               values.name.length < 2 ? 'Name must be at least 2 characters' : ''
    },
    email: {
      isValid: /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email),
      message: values.email.length === 0 ? '' :
               !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email) ? 'Please enter a valid email address' : ''
    },
    password: {
      isValid: values.password.length >= 8,
      hasUppercase: /[A-Z]/.test(values.password),
      hasLowercase: /[a-z]/.test(values.password),
      hasNumber: /\d/.test(values.password),
      hasSpecial: /[!@#$%^&*(),.?":{}|<>]/.test(values.password),
      message: values.password.length === 0 ? '' :
               values.password.length < 8 ? 'Password must be at least 8 characters' : ''
    },
    passwordConfirmation: {
      isValid: values.password_confirmation === values.password && values.password.length > 0,
      message: values.password_confirmation.length === 0 ? '' :
               values.password_confirmation !== values.password ? 'Passwords do not match' : ''
    }
  })
  
  /*
   * OVERALL FORM VALIDATION - SUBMISSION READINESS
   * =============================================
   */
  let isFormValid = $derived(
    validation.name.isValid &&
    validation.email.isValid &&
    validation.password.isValid &&
    validation.passwordConfirmation.isValid &&
    values.terms
  )
  
  /*
   * PASSWORD STRENGTH INDICATOR - VISUAL FEEDBACK
   * ============================================
   * 
   * Calculate password strength for user guidance.
   * Encourages stronger passwords through visual feedback.
   */
  let passwordStrength = $derived(() => {
    if (values.password.length === 0) return { score: 0, label: '', color: '' }
    
    let score = 0
    if (values.password.length >= 8) score++
    if (validation.password.hasUppercase) score++
    if (validation.password.hasLowercase) score++
    if (validation.password.hasNumber) score++
    if (validation.password.hasSpecial) score++
    
    const levels = [
      { score: 0, label: 'Very Weak', color: 'bg-red-500' },
      { score: 1, label: 'Weak', color: 'bg-red-400' },
      { score: 2, label: 'Fair', color: 'bg-yellow-500' },
      { score: 3, label: 'Good', color: 'bg-blue-500' },
      { score: 4, label: 'Strong', color: 'bg-green-500' },
      { score: 5, label: 'Very Strong', color: 'bg-green-600' }
    ]
    
    return levels[score] || levels[0]
  })
  
  /*
   * FORM SUBMISSION HANDLER - OFFICIAL INERTIA.JS PATTERN
   * =====================================================
   */
  function handleRegister(event) {
    event.preventDefault()
    
    console.log('🚀 Registration submission started!')
    console.log('Values:', values)
    
    // Set processing state
    processing = true
    
    router.post('/register', values, {
      onSuccess: () => {
        console.log('🎉 Registration successful! User logged in automatically.')
        processing = false
      },
      
      onError: (errors) => {
        console.log('❌ Registration failed:', errors)
        processing = false
      },
      
      onFinish: () => {
        processing = false
      },
      
      preserveScroll: true
    })
  }
  
  /*
   * UI HELPER FUNCTIONS
   * ==================
   */
  function togglePasswordVisibility() {
    showPassword = !showPassword
  }
  
  function toggleConfirmPasswordVisibility() {
    showConfirmPassword = !showConfirmPassword
  }
  
  function handleFieldFocus(fieldName) {
    focusedField = fieldName
  }
  
  function handleFieldBlur() {
    focusedField = null
  }
  
  /*
   * NAVIGATION HANDLER - PREVENT PASSWORD SAVE PROMPTS
   * =================================================
   * 
   * This fixes the browser password save prompt issue when navigating
   * between register/login pages. The browser interprets navigation
   * away from forms as "submission" if there's data in password fields.
   * 
   * Solution: Clear form data before navigation.
   */
  function handleNavigateToLogin(event) {
    // Clear any entered data to prevent browser save prompts
    values.name = ''
    values.email = ''
    values.password = ''
    values.password_confirmation = ''
    values.terms = false
    
    // Allow normal navigation to continue
    console.log('🧭 Navigating to login - form data cleared')
  }
  
  /*
   * DEMO DATA HELPER (DEVELOPMENT ONLY)
   * ==================================
   */
  function fillDemoData() {
    values.name = 'Demo User 2' 
    values.email = 'demo2@example.com'
    values.password = 'SecureDemo123!'
    values.password_confirmation = 'SecureDemo123!'
    values.terms = true
    
    console.log('Demo registration data filled:', values)
  }
</script>

<!--
  PAGE HEAD - SEO AND META TAGS
  =============================
-->
<svelte:head>
  <title>Create Account | jmrecodes Educational Blog</title>
  <meta name="description" content="Join jmrecodes Educational Blog to start creating and sharing your knowledge with the world." />
  <meta name="robots" content="noindex" />
</svelte:head>

<!--
  MAIN REGISTRATION PAGE LAYOUT
  =============================
-->
<div class="min-h-screen bg-[rgb(var(--color-bg-secondary))] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
  
  <!-- HEADER SECTION -->
  <div class="sm:mx-auto sm:w-full sm:max-w-md">
    <!-- Logo/Brand -->
    <div class="flex justify-center">
      <Link href="/" class="flex items-center space-x-2 text-2xl font-bold text-gray-900 hover:text-blue-600 transition-colors">
        <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
        <span>Educational Blog</span>
      </Link>
    </div>
    
    <!-- Welcome Message -->
    <h1 class="mt-6 text-center text-[length:var(--font-size-3xl)] font-[var(--font-weight-bold)] text-[rgb(var(--color-text-primary))] leading-[var(--line-height-tight)]">
      Create Your Account
    </h1>
    <p class="mt-2 text-center text-[length:var(--font-size-sm)] text-[rgb(var(--color-text-secondary))] leading-[var(--line-height-normal)]">
      Join our community and start sharing your knowledge
    </p>
  </div>

  <!-- REGISTRATION FORM CONTAINER -->
  <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-[rgb(var(--color-bg-primary))] py-8 px-4 shadow-[var(--shadow-lg)] rounded-[var(--radius-lg)] sm:px-10 border border-[rgb(var(--color-border-secondary))]">
      
      <!-- EDUCATIONAL DEMO SECTION (DEVELOPMENT ONLY) -->
      {#if import.meta.env.DEV || import.meta.env.MODE === 'development'}
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
          <h3 class="text-sm font-medium text-yellow-800 mb-2">🎓 Educational Demo</h3>
          <p class="text-xs text-yellow-700 mb-3">
            Click below to fill in demo registration data:
          </p>
          <button 
            type="button"
            onclick={fillDemoData}
            class="text-xs bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700 transition-colors"
          >
            Fill Demo Data
          </button>
        </div>
      {/if}
      
      <!-- REGISTRATION FORM -->
      <form onsubmit={handleRegister} class="space-y-6">
        
        <!-- 
          NAME INPUT FIELD
          ===============
        -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
            Full Name
          </label>
          <div class="relative">
            <input
              id="name"
              type="text"
              bind:value={values.name}
              onfocus={() => handleFieldFocus('name')}
              onblur={handleFieldBlur}
              required
              autocomplete="name"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                     disabled:bg-gray-50 disabled:text-gray-500
                     {errors?.name ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 
                      validation.name.isValid && values.name.length > 0 ? 'border-green-500' : ''}"
              disabled={processing}
              placeholder="Enter your full name"
            />
            
            <!-- Validation icon -->
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
              {#if values.name.length > 0}
                {#if validation.name.isValid}
                  <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                {:else}
                  <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                {/if}
              {/if}
            </div>
          </div>
          
          <!-- Field validation message -->
          {#if errors?.name}
            <p class="mt-1 text-sm text-red-600" role="alert">{errors.name}</p>
          {:else if validation.name.message}
            <p class="mt-1 text-sm text-red-600" role="alert">{validation.name.message}</p>
          {/if}
        </div>

        <!-- 
          EMAIL INPUT FIELD
          ================
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
              onfocus={() => handleFieldFocus('email')}
              onblur={handleFieldBlur}
              required
              autocomplete="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                     disabled:bg-gray-50 disabled:text-gray-500
                     {errors?.email ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 
                      validation.email.isValid && values.email.length > 0 ? 'border-green-500' : ''}"
              disabled={processing}
              placeholder="Enter your email address"
            />
            
            <!-- Validation icon -->
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
              {#if values.email.length > 0}
                {#if validation.email.isValid}
                  <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                {:else}
                  <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                {/if}
              {/if}
            </div>
          </div>
          
          <!-- Field validation message -->
          {#if errors?.email}
            <p class="mt-1 text-sm text-red-600" role="alert">{errors.email}</p>
          {:else if validation.email.message}
            <p class="mt-1 text-sm text-red-600" role="alert">{validation.email.message}</p>
          {/if}
        </div>

        <!-- 
          PASSWORD INPUT FIELD WITH STRENGTH INDICATOR
          ===========================================
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
              onfocus={() => handleFieldFocus('password')}
              onblur={handleFieldBlur}
              required
              autocomplete="new-password"
              minlength="8"
              class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm 
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                     disabled:bg-gray-50 disabled:text-gray-500
                     {errors?.password ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 
                      validation.password.isValid && values.password.length > 0 ? 'border-green-500' : ''}"
              disabled={processing}
              placeholder="Create a strong password"
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
          
          <!-- Password strength indicator -->
          {#if values.password.length > 0}
            <div class="mt-2">
              <div class="flex justify-between items-center mb-1">
                <span class="text-xs text-gray-600">Password strength:</span>
                <span class="text-xs font-medium {passwordStrength.score >= 3 ? 'text-green-600' : passwordStrength.score >= 2 ? 'text-yellow-600' : 'text-red-600'}">
                  {passwordStrength.label}
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="h-2 rounded-full transition-all duration-300 {passwordStrength.color}" 
                     style="width: {(passwordStrength.score / 5) * 100}%"></div>
              </div>
              
              <!-- Password requirements checklist -->
              <div class="mt-2 space-y-1">
                <div class="flex items-center text-xs {validation.password.isValid ? 'text-green-600' : 'text-gray-500'}">
                  <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  At least 8 characters
                </div>
                <div class="flex items-center text-xs {validation.password.hasUppercase ? 'text-green-600' : 'text-gray-500'}">
                  <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  One uppercase letter
                </div>
                <div class="flex items-center text-xs {validation.password.hasNumber ? 'text-green-600' : 'text-gray-500'}">
                  <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  One number
                </div>
              </div>
            </div>
          {/if}
          
          <!-- Field validation message -->
          {#if errors?.password}
            <p class="mt-1 text-sm text-red-600" role="alert">{errors.password}</p>
          {:else if validation.password.message}
            <p class="mt-1 text-sm text-red-600" role="alert">{validation.password.message}</p>
          {/if}
        </div>

        <!-- 
          PASSWORD CONFIRMATION FIELD
          ==========================
        -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
            Confirm Password
          </label>
          <div class="relative">
            <input
              id="password_confirmation"
              type={showConfirmPassword ? 'text' : 'password'}
              bind:value={values.password_confirmation}
              onfocus={() => handleFieldFocus('password_confirmation')}
              onblur={handleFieldBlur}
              required
              autocomplete="new-password"
              class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm 
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                     disabled:bg-gray-50 disabled:text-gray-500
                     {errors?.password_confirmation ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 
                      validation.passwordConfirmation.isValid && values.password_confirmation.length > 0 ? 'border-green-500' : ''}"
              disabled={processing}
              placeholder="Confirm your password"
            />
            
            <!-- Password visibility toggle -->
            <button
              type="button"
              onclick={toggleConfirmPasswordVisibility}
              class="absolute inset-y-0 right-0 pr-3 flex items-center hover:text-gray-600 focus:outline-none focus:text-gray-600"
              disabled={processing}
              aria-label={showConfirmPassword ? 'Hide password confirmation' : 'Show password confirmation'}
            >
              {#if showConfirmPassword}
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
          
          <!-- Field validation message -->
          {#if errors?.password_confirmation}
            <p class="mt-1 text-sm text-red-600" role="alert">{errors.password_confirmation}</p>
          {:else if validation.passwordConfirmation.message}
            <p class="mt-1 text-sm text-red-600" role="alert">{validation.passwordConfirmation.message}</p>
          {/if}
        </div>

        <!-- 
          TERMS OF SERVICE AGREEMENT
          =========================
        -->
        <div>
          <div class="flex items-start">
            <input
              id="terms"
              type="checkbox"
              bind:checked={values.terms}
              disabled={processing}
              required
              class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 disabled:opacity-50"
            />
            <label for="terms" class="ml-2 block text-sm text-gray-700">
              I agree to the 
              <Link 
                href={termsUrl} 
                target="_blank" 
                class="text-blue-600 hover:text-blue-500 underline"
                aria-label="Read our Terms of Service"
              >
                Terms of Service
              </Link>
              and 
              <Link 
                href={privacyUrl} 
                target="_blank" 
                class="text-blue-600 hover:text-blue-500 underline"
                aria-label="Read our Privacy Policy"
              >
                Privacy Policy
              </Link>
            </label>
          </div>
          
          {#if errors?.terms}
            <p class="mt-1 text-sm text-red-600" role="alert">{errors.terms}</p>
          {/if}
        </div>

        <!-- 
          SUBMIT BUTTON
          =============
        -->
        <div>
          <button
            type="submit"
            disabled={!isFormValid || processing}
            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white
                   bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
                   disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600
                   transition-all duration-200 transform hover:scale-[1.02] disabled:hover:scale-100"
          >
            {#if processing}
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating Account...
            {:else}
              Create Account
            {/if}
          </button>
        </div>

        <!-- GENERAL ERROR DISPLAY -->
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
    
    <!-- LOGIN LINK -->
    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600">
        Already have an account?
        <Link 
          href="/login" 
          onclick={handleNavigateToLogin}
          class="font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition-colors"
        >
          Sign in here
        </Link>
      </p>
    </div>
    
    <!-- BACK TO HOME LINK -->
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
  EDUCATIONAL SUMMARY - REGISTRATION COMPONENT FEATURES
  ====================================================
  
  This Registration component demonstrates advanced patterns:
  
  ✅ PROGRESSIVE VALIDATION:
  - Real-time feedback as users type
  - Visual indicators for field validity
  - Password strength meter with requirements
  - Immediate password confirmation checking
  
  ✅ ENHANCED USER EXPERIENCE:
  - Password visibility toggles for both fields
  - Clear visual feedback with icons and colors
  - Comprehensive error handling and display
  - Loading states and disabled form submission
  
  ✅ SECURITY & COMPLIANCE:
  - Strong password requirements enforced
  - Terms of service agreement required
  - CSRF protection via Laravel middleware
  - Secure password confirmation validation
  
  ✅ ACCESSIBILITY FEATURES:
  - Proper ARIA labels and roles
  - Screen reader compatible error messages
  - Keyboard navigation support
  - Focus management and visual indicators
  
  ✅ PRODUCTION PATTERNS:
  - Form state preservation on errors
  - Graceful error handling and recovery
  - Progressive enhancement principles
  - Mobile-responsive design
  
  This component serves as a comprehensive example of modern form handling
  in web applications, demonstrating both technical implementation and
  user experience best practices.
--> 