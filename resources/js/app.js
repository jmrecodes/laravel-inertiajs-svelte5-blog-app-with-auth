/**
 * EDUCATIONAL BLOG CRUD APP - MAIN JAVASCRIPT ENTRY POINT
 * ========================================================
 * 
 * This file is the primary JavaScript entry point for our Svelte + Inertia.js application.
 * It's responsible for bootstrapping the entire frontend, configuring Inertia.js,
 * and setting up global behaviors like loading indicators and routing.
 * 
 * 🎓 BEGINNER LEARNING OBJECTIVES:
 * ================================
 * 1. **Application Bootstrapping**: How a single JavaScript file starts the entire frontend.
 * 2. **Inertia.js Initialization**: Connecting Laravel's backend responses to Svelte components.
 * 3. **Client-Side Navigation**: Understanding how Inertia enables SPA-like routing.
 * 4. **Global Event Handling**: Listening to application-wide events (e.g., navigation start/end).
 * 5. **Performance & UX**: Implementing loading indicators and handling browser history.
 * 6. **Development vs. Production**: Using environment variables for conditional code.
 * 
 * 🔍 WHAT YOU'LL LEARN:
 * =====================
 * - The role of `createInertiaApp` in setting up the Inertia-Svelte bridge.
 * - How `resolvePageComponent` dynamically loads Svelte components.
 * - Configuring `NProgress` for visual loading feedback.
 * - Attaching event listeners (`router.on()`) to Inertia's lifecycle events.
 * - Customizing global settings like page titles and progress bar behavior.
 * - Basic error handling for application startup.
 * 
 * 🚀 KEY TECHNOLOGIES BOOTSTRAPPED HERE:
 * - **Svelte 5**: The reactive frontend framework.
 * - **Inertia.js 2.0**: The adapter enabling SPA functionality with Laravel.
 * - **Laravel Vite Plugin**: Integration with Laravel's asset compilation.
 * - **NProgress**: A lightweight progress bar for navigation.
 * 
 * This file is heavily commented to guide you through each concept
 * and pattern involved in setting up a modern Single Page Application.
 */

// =======================================================================
// CORE DEPENDENCIES AND IMPORTS - Bring in necessary libraries
// =======================================================================

/**
 * INERTIA.JS CORE - `createInertiaApp`
 * ====================================
 * 
 * `createInertiaApp` is the main function from Inertia.js that initializes your client-side
 * application. It sets up the bridge between your Laravel backend and Svelte frontend.
 */
import { createInertiaApp } from '@inertiajs/svelte'

/**
 * SVELTE INTEGRATION HELPERS - `resolvePageComponent` and `mount`
 * ==============================================================
 * 
 * `resolvePageComponent`: A helper from `laravel-vite-plugin` that intelligently loads your
 *   Svelte components based on the page name sent from Laravel.
 * `mount`: The official way to mount (initialize) a Svelte 5 component to the DOM.
 */
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { mount } from 'svelte'

/**
 * NPROGRESS - TOP LOADING BAR
 * ===========================
 * 
 * `NProgress` is a tiny progress bar that appears at the top of the page when
 * a navigation or data fetching event is in progress. It provides visual feedback
 * to the user, improving the perceived performance of the application.
 */
import NProgress from 'nprogress'

/**
 * NPROGRESS CSS - STYLING FOR THE LOADING BAR
 * ==========================================
 * 
 * This imports the default CSS for NProgress, giving it a basic look.
 * You could customize its appearance further in `resources/css/app.css` if needed.
 */
import 'nprogress/nprogress.css'

// =======================================================================
// INERTIA.JS GLOBAL CONFIGURATION AND EVENT LISTENERS
// =======================================================================

/**
 * NPROGRESS CONFIGURATION
 * =======================
 * 
 * Customizes the behavior and appearance of the NProgress loading bar.
 * `minimum`: The minimum percentage to display (prevents a bar from showing for very fast loads).
 * `speed`: How fast the animation runs.
 * `trickle`: Whether the bar should 'trickle' forward a little bit while waiting.
 * `showSpinner`: Set to `false` because we prefer to control our own spinners or loading states.
 * `template`: Custom HTML for the progress bar (we use the default one).
 */
NProgress.configure({
  minimum: 0.1,          // Starts at 10% progress
  speed: 200,            // Animation speed in milliseconds
  trickle: true,         // Enable gradual progress increments
  trickleSpeed: 200,     // Frequency of trickle increments
  showSpinner: false,    // Hide the default spinner (we use custom loading states)
  easing: 'ease',        // CSS easing function for animation
  template: '<div class="bar" role="bar"><div class="peg"></div></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
})

/**
 * INERTIA.JS ROUTER EVENTS - MANAGING UI FEEDBACK DURING NAVIGATION
 * =================================================================
 * 
 * Inertia.js emits various events during its page visits (navigation). We can listen
 * to these events to update our UI, show/hide loading indicators, or perform other actions.
 * 
 * 🎓 LEARN: How event listeners provide hooks into an application's lifecycle.
 */

// Re-import `router` to attach event listeners
import { router } from '@inertiajs/svelte'

/**
 * `router.on('start')` - NAVIGATION STARTED
 * ========================================
 * 
 * This event fires when a new Inertia visit begins (e.g., user clicks a `Link` component,
 * or a `router.post()` is initiated). It's the perfect time to show a loading indicator.
 */
router.on('start', (event) => {
  NProgress.start() // Start the NProgress loading bar
  
  // Hide the initial loading spinner/overlay if it's still visible from initial page load
  const loadingSpinner = document.getElementById('loading-spinner')
  if (loadingSpinner) {
    loadingSpinner.style.display = 'none'
  }
  
  // Log navigation for debugging purposes (only in development environment)
  if (import.meta.env.DEV) {
    console.log(`🚀 Navigating to: ${event.detail?.visit?.url || 'unknown URL'}`) // Log the destination URL
  }
})

/**
 * `router.on('progress')` - UPLOAD PROGRESS
 * =========================================
 * 
 * This event fires periodically during file uploads (e.g., submitting a form with `type="file"`).
 * It provides the upload percentage, which can be used to update progress indicators.
 */
router.on('progress', (event) => {
  // If there's upload progress, update NProgress bar
  if (event.detail.progress?.percentage) {
    NProgress.set(event.detail.progress.percentage / 100) // NProgress expects a value between 0 and 1
  }
})

/**
 * `router.on('success')` - NAVIGATION SUCCESSFUL
 * ============================================
 * 
 * This event fires when an Inertia visit successfully completes and the new page content is loaded.
 * It's the ideal time to hide loading indicators and perform post-navigation cleanup.
 */
router.on('success', (event) => {
  NProgress.done() // Complete and hide the NProgress loading bar
  
  // Scroll to the top of the page on new page loads (unless it's a form submission that should preserve scroll)
  if (event.detail?.visit?.method === 'get') {
    window.scrollTo({ top: 0, behavior: 'smooth' }) // Smoothly scroll to the top
  }
  
  // Log successful navigation (development only)
  if (import.meta.env.DEV) {
    console.log(`✅ Successfully loaded: ${event.detail?.visit?.url || 'page'}`) // Log the loaded URL
  }
})

/**
 * `router.on('error')` - NAVIGATION ERROR
 * ======================================
 * 
 * This event fires when an Inertia visit encounters an error (e.g., server-side 500 error,
 * network issue, or unhandled validation error). It's crucial for graceful error handling.
 */
router.on('error', (errors) => {
  NProgress.done() // Ensure the progress bar is hidden even on error
  
  console.error('❌ Navigation error:', errors) // Log the full error object for debugging
  
  // If the error object contains validation errors, log them specifically
  if (Object.keys(errors).length > 0) {
    console.warn('Form validation errors returned by server:', errors) // Useful for debugging form issues
  }
  // You could implement a global toast notification or error display here
})

/**
 * `router.on('finish')` - NAVIGATION COMPLETED
 * ==========================================
 * 
 * This event always fires after every Inertia visit, regardless of whether it was a success or an error.
 * It's useful for final cleanup tasks that should always run.
 */
router.on('finish', (event) => {
  NProgress.done() // Ensure the progress bar is always hidden
  
  // Re-enable any HTML elements that might have been temporarily disabled during the request
  document.querySelectorAll('[disabled]').forEach(element => {
    if (element.dataset.tempDisabled) { // Check for custom attribute used to mark elements for re-enabling
      element.disabled = false
      delete element.dataset.tempDisabled
    }
  })
})

// =======================================================================
// APPLICATION INITIALIZATION - Where the magic happens!
// =======================================================================

/**
 * `createInertiaApp({ ... })` - BOOTSTRAPPING THE SVELTE APPLICATION
 * ==================================================================
 * 
 * This is the core function that initializes the entire client-side Inertia application.
 * It connects your Laravel backend with your Svelte frontend, enabling the SPA experience.
 */
createInertiaApp({
  /**
   * `resolve` - DYNAMIC COMPONENT RESOLUTION
   * ========================================
   * 
   * This function tells Inertia how to find and load the correct Svelte component
   * based on the `page name` returned by your Laravel controllers.
   * 
   * For example, if Laravel returns `Inertia::render('BlogPosts/Index')`,
   * this `resolve` function will find and load `resources/js/Pages/BlogPosts/Index.svelte`.
   *
   * `import.meta.glob('./Pages/**//*.svelte')`: This Vite feature (glob import) automatically
   * creates a mapping of all `.svelte` files within the `./Pages` directory and its subdirectories.
   * This enables **code splitting** (or lazy loading), meaning only the necessary component's
   * JavaScript is loaded when a page is visited, improving initial load performance.
   *
   * COMPONENT PATH EXAMPLES:
   * - 'Auth/Login'            -> `resources/js/Pages/Auth/Login.svelte` 
   * - 'BlogPosts/Index'       -> `resources/js/Pages/BlogPosts/Index.svelte`
   * - 'Home'                  -> `resources/js/Pages/Home.svelte`
   */
  resolve: (name) => resolvePageComponent(
    `./Pages/${name}.svelte`, // Construct the full path to the Svelte component file
    import.meta.glob('./Pages/**/*.svelte') // Dynamically import all Svelte pages
  ),

  /**
   * `setup` - SVELTE APPLICATION MOUNTING
   * ====================================
   * 
   * This function runs once, after Inertia resolves the initial Svelte component
   * (for the very first page load) but before it's mounted to the DOM.
   * It's where the main Svelte application instance is created and attached to your HTML.
   *
   * PARAMETERS:
   * - `el`: The DOM element (`#app` in `resources/views/app.blade.php`) where the Svelte app will be mounted.
   * - `App`: The resolved Svelte component (the main page component for the current route).
   * - `props`: All data passed from the Laravel controller (`Inertia::render(..., $props)`),
   *   including shared data (like `auth`, `flash`, `errors`) from Laravel's `HandleInertiaRequests` middleware.
   */
  setup({ el, App, props }) {
    /**
     * CLEAR EXISTING HTML CONTENT & MOUNT SVELTE APP
     * ==============================================
     * 
     * **CRITICAL STEP**: We must clear any existing HTML content inside the `#app` element
     * (`el.innerHTML = ''`) before mounting the Svelte application.
     * 
     * **WHY?**: Without this, the initial loading spinner/message (defined in `app.blade.php`)
     * would remain visible, appearing above or behind your Svelte-rendered content.
     * Clearing `el.innerHTML` ensures a clean transition from the static loading state to the dynamic Svelte app.
     */
    el.innerHTML = '' // Clear the initial loading content
    
    // Use Svelte 5's new `mount()` API to create and attach the application instance.
    // This is the recommended and official way to initialize Svelte 5 applications.
    const app = mount(App, {
      target: el,    // The DOM element to mount the Svelte app into
      props,         // Pass all received props from Laravel to the Svelte component
    })

    /**
     * OPTIONAL: HIDE INITIAL LOADING SPINNER
     * ======================================
     * 
     * After the Svelte app is successfully mounted, we can explicitly hide any
     * splash screen or loading spinner that was displayed by the initial Blade template.
     */
    const loadingSpinner = document.getElementById('loading-spinner')
    if (loadingSpinner) {
      loadingSpinner.style.display = 'none'
    }
    
    /**
     * DEVELOPMENT LOGGING
     * ===================
     * 
     * Provides helpful console messages during development to confirm app initialization.
     * `import.meta.env.DEV` is a Vite-specific environment variable that is `true` in development.
     */
    if (import.meta.env.DEV) {
      console.log('🎉 Svelte 5 app mounted successfully with mount() API!')
    }
    
    // Return the Svelte application instance (useful for debugging or advanced scenarios)
    return app
  },

  /**
   * `progress` - INERTIA.JS PROGRESS INDICATOR CONFIGURATION
   * ========================================================
   * 
   * This section configures the visual progress bar that appears during Inertia page transitions.
   * It enhances the user experience by showing activity during navigation.
   */
  progress: {
    color: '#3B82F6', // Color of the progress bar (matches our primary brand blue)
    delay: 150,       // Minimum delay (in ms) before showing the progress bar (prevents flicker on fast loads)
    includeCSS: true, // Automatically include NProgress's default CSS
    showSpinner: false, // Do not show the default NProgress spinner (we use our own loading indicators)
  },

  /**
   * `title` - DYNAMIC PAGE TITLE FORMATTING
   * ======================================
   * 
   * This function defines how the browser tab title will be formatted for each page.
   * It takes the page's title (set in Svelte's `<svelte:head>`) and appends a consistent suffix.
   * 
   * USAGE EXAMPLE:
   * If `<svelte:head><title>My Blog Post</title></svelte:head>` in a Svelte component,
   * the browser tab will show "My Blog Post | Educational Blog".
   */
  title: title => title ? `${title} | Educational Blog` : 'Educational Blog', // Default to 'Educational Blog' if no title provided

  /**
   * `catch` - GLOBAL APPLICATION ERROR HANDLING
   * ==========================================
   * 
   * This `.catch()` block handles any unhandled errors that occur during the initial
   * setup or loading of the Inertia application (e.g., a component fails to resolve).
   * It provides a user-friendly fallback error screen instead of a blank page.
   */
}).catch(error => {
  console.error('❌ Failed to initialize Inertia app:', error) // Log the error for developers
  
  const appContainer = document.getElementById('app') // Get the main app container
  if (appContainer) {
    // Display a user-friendly error message directly into the #app container
    appContainer.innerHTML = `
      <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
          <div>
            <h2 class="text-3xl font-extrabold text-gray-900">
              Application Error
            </h2>
            <p class="mt-2 text-gray-600">
              Sorry, something went wrong loading the application.
            </p>
          </div>
          <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-sm text-red-700">
              <strong>Error:</strong> ${error.message}
            </p>
          </div>
          <div>
            <button 
              onclick="window.location.reload()" 
              class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Reload Page
            </button>
          </div>
        </div>
      </div>
    `
  }
  
  // Ensure any initial loading spinner is hidden if an error occurs
  const loadingSpinner = document.getElementById('loading-spinner')
  if (loadingSpinner) {
    loadingSpinner.style.display = 'none'
  }
})

// =======================================================================
// DEVELOPMENT HELPERS AND DEBUGGING - For use only in local development
// =======================================================================

/**
 * DEVELOPMENT TOOLS - Conditional Loading
 * =====================================
 * 
 * This block ensures that certain debugging and development-only features
 * are *only* included when the application is running in development mode (`import.meta.env.DEV`).
 * This keeps your production bundles lean and secure.
 */
if (import.meta.env.DEV) {
  /**
   * GLOBAL DEBUGGING HELPERS - `window.debugApp()`
   * ============================================
   * 
   * Makes a simple `debugApp()` function available globally in the browser's console.
   * This provides quick access to useful Inertia.js and environment information for debugging.
   */
  window.debugApp = () => {
    console.log('📱 App Debug Info:')
    console.log('- Current page:', router.page) // Current Inertia page object
    console.log('- Inertia version:', router.version) // Inertia.js client version
    console.log('- Environment:', import.meta.env) // Vite environment variables
  }
  
  /**
   * HOT MODULE REPLACEMENT (HMR) SUPPORT - Vite Specific
   * ===================================================
   * 
   * `import.meta.hot.accept()` enables Vite's Hot Module Replacement.
   * This allows changes to your Svelte components and other assets to be injected
   * into the browser without a full page reload, significantly speeding up development.
   */
  if (import.meta.hot) {
    import.meta.hot.accept() // Accept HMR updates
  }
  
  /**
   * INITIALIZATION CONFIRMATION LOG
   * ===============================
   * 
   * Simple console logs to confirm that the application has successfully started
   * and to remind developers about the `debugApp()` helper.
   */
  console.log('🎉 Educational Blog app initialized successfully!')
  console.log('📚 Built with Svelte 5 + Inertia.js + Laravel')
  console.log('🛠️  Type debugApp() in console for debugging helpers')
}

// =======================================================================
// OPTIONAL: PERFORMANCE MONITORING - For deeper insights
// =======================================================================

/**
 * BASIC PERFORMANCE TRACKING - `window.performance` API
 * ====================================================
 * 
 * This block demonstrates how to use the browser's native Performance API
 * to log basic page load metrics to the console. This can be helpful for
 * identifying performance bottlenecks during development.
 * 
 * 🎓 LEARN: How to access browser performance data.
 * 
 * NOTE: In a production environment, you would typically integrate a dedicated
 * analytics or Real User Monitoring (RUM) solution (e.g., Google Analytics, Sentry).
 */
if ('performance' in window) {
  window.addEventListener('load', () => {
    // Defer logging slightly to ensure all resources are loaded
    setTimeout(() => {
      const perfData = performance.getEntriesByType('navigation')[0] // Get navigation timing data
      if (perfData && import.meta.env.DEV) {
        console.log('⚡ Performance Metrics:')
        console.log(`- DOM Content Loaded: ${Math.round(perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart)}ms`) // Time to parse HTML and CSS
        console.log(`- Page Load Complete: ${Math.round(perfData.loadEventEnd - perfData.loadEventStart)}ms`) // Time until all resources (images, scripts) are loaded
        console.log(`- Total Load Time: ${Math.round(perfData.loadEventEnd - perfData.fetchStart)}ms`) // Total time from request start to full load
      }
    }, 100) // Small delay
  })
}

// =======================================================================
// EXPORTS - For external consumption (e.g., testing or SSR)
// =======================================================================

/**
 * EXPORTS FOR TESTING AND SSR (SERVER-SIDE RENDERING)
 * ===================================================
 * 
 * Exporting the `router` instance allows external scripts (like testing frameworks
 * or Node.js SSR environments) to interact with Inertia's routing capabilities.
 */
export { router } // Export the router instance

/*
 * 🎓 EDUCATIONAL SUMMARY - APP.JS (MAIN ENTRY POINT)
 * ===================================================
 * 
 * This `app.js` file is the heart of your Svelte + Inertia.js frontend application.
 * It orchestrates the entire client-side experience, from initial loading to dynamic navigation.
 * 
 * ✅ KEY LEARNINGS FROM THIS FILE:
 * - **Full SPA Initialization**: The complete process of starting an Inertia application.
 * - **Inertia.js Lifecycle**: Understanding and leveraging Inertia's `on` events for UI feedback.
 * - **Svelte 5 Integration**: How Svelte components are resolved and mounted.
 * - **Performance & UX**: Implementing loading indicators and handling page transitions.
 * - **Development vs. Production Practices**: Conditional code for debugging and optimization.
 * - **Modular Structure**: Organizing global application logic into logical sections.
 * 
 * This file serves as a foundational blueprint for setting up any modern
 * Laravel + Inertia.js + Svelte Single Page Application.
 */
