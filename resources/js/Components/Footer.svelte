<script>
  /*
   * FOOTER COMPONENT - REUSABLE SITE FOOTER FOR EDUCATIONAL APP
   * ==========================================================
   * 
   * This Svelte component provides a consistent footer for the entire application.
   * It demonstrates how to create reusable UI components, manage dynamic content
   * (like the current year), and include essential legal and navigation links.
   * 
   * 🎓 BEGINNER LEARNING OBJECTIVES:
   * ================================
   * 1. **Component Reusability**: Designing components that can be dropped into multiple pages.
   * 2. **Props System**: Accepting configuration data from parent components to customize behavior.
   * 3. **Dynamic Content**: Generating values like the current year automatically.
   * 4. **Responsive Layout**: Creating a footer that adapts gracefully to different screen sizes.
   * 5. **Site Navigation**: Providing secondary navigation links to key areas of the application.
   * 6. **Legal Compliance**: Including links to Terms of Service and Privacy Policy.
   * 7. **Accessibility**: Ensuring the footer is navigable and readable for all users.
   * 
   * 🔍 WHAT YOU'LL LEARN:
   * =====================
   * - How to use Svelte's `$props()` to configure component behavior.
   * - Using `$derived()` for computed CSS classes based on props (though simplified here).
   * - Implementing `Link` components for seamless internal navigation.
   * - Structuring footer content with CSS Grid for flexible layouts.
   * - Handling external links (e.g., social media) with `target="_blank"` and `rel="noopener noreferrer"`.
   * - Dynamically displaying the current year for copyright notices.
   *
   * KEY SECTIONS OF THIS COMPONENT:
   * - About Section: Brief description of the project and its educational purpose.
   * - Navigation Section: Links to main application pages.
   * - Account Section: Links to login, register, and password reset.
   * - Legal Section: Links to Terms of Service and Privacy Policy.
   * - Social Links Section (Optional): Demonstrates conditional rendering of external links.
   * - Copyright Section: Dynamic copyright notice.
   */
  
  /*
   * IMPORTS AND DEPENDENCIES - SVELTE 5 + INERTIA.JS
   * ================================================
   * 
   * We import the `Link` component from Inertia.js for client-side navigation.
   * 🎓 LEARN: Why `Link` components are preferred over standard `<a>` tags for internal navigation in Inertia.js apps.
   * We also import the `router` component from Inertia.js for client-side navigation.
   * 🎓 LEARN: How to use the `router` component for client-side navigation.  
   */
   import { router } from '@inertiajs/svelte' // Inertia.js router for client-side navigation
   import { Link } from '@inertiajs/svelte'     // Inertia.js Link component for SPA-like navigation
  
  /*
   * COMPONENT PROPS - CONFIGURATION FROM PARENT COMPONENTS
   * =====================================================
   * 
   * These properties are passed to this Svelte component from any page that uses it.
   * They allow the footer's behavior and content to be customized.
   * 
   * 🎓 LEARN: How `$props()` enables flexible and reusable components.
   * 
   * - `auth`: Object, global authentication data (used to conditionally show login/register links).
   * - `showSocialLinks`: Boolean, controls whether the social media links section is displayed.
   * - `companyName`: String, the name to be used in the copyright notice.
   * - `currentYear`: Number, automatically defaults to the current year, but can be overridden.
   */
  let { 
    auth = {},
    showSocialLinks = true,
    companyName = 'jmrecodes',         
    currentYear = new Date().getFullYear()  
  } = $props()
  
  /*
   * COMPUTED VALUES - SVELTE 5 DERIVED STATE ($DERIVED)
   * ====================================================
   * 
   * `$derived` creates values that automatically re-calculate whenever their
   * dependencies (`$state` or `$props` variables) change. This is useful for
   * dynamic styling or derived text.
   * 
   * 🎓 LEARN: How `$derived` simplifies reactive logic for dynamic class names.
   */
  let footerClasses = $derived(
    'bg-white text-gray-600 border-gray-200' // Static classes for the footer container
  )
  
  let linkClasses = $derived(
    'text-gray-600 hover:text-gray-900' // Static classes for general footer links
  )
  
  let copyrightClasses = $derived(
    'text-gray-500' // Static classes for copyright text
  )

  /*
   * COMPONENT STATE - UI INTERACTIONS
   * ================================
   */
   let isLoggingOut = $state(false)

  /*
   * LOGOUT HANDLER - SECURE SESSION TERMINATION
   * ===========================================
   */
   async function handleLogout() {
    if (isLoggingOut) return
    
    isLoggingOut = true
    
    try {
      await router.post('/logout', {}, {
        onSuccess: () => {
          console.log('👋 User logged out successfully')
        },
        
        onError: (errors) => {
          console.error('❌ Logout failed:', errors)
          isLoggingOut = false
        }
      })
      
    } catch (error) {
      console.error('❌ Unexpected logout error:', error)
      isLoggingOut = false
    }
  }    
</script>

<!--
  MAIN FOOTER STRUCTURE - SEMANTIC HTML LAYOUT
  ============================================
  
  The `<footer>` element provides the overall semantic structure for the footer content.
  It uses a responsive grid layout to organize different sections.
  
  🎓 LEARN:
  - **Semantic HTML**: Using `<footer>` for better document outline and accessibility.
  - **CSS Grid**: A powerful layout system for creating complex, responsive grid structures.
  - **Accessibility**: Ensuring navigation is keyboard-friendly and screen-reader compatible.
-->
<footer class="{footerClasses} border-t">
  <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
    <!--
      MAIN FOOTER CONTENT GRID - RESPONSIVE COLUMN LAYOUT
      ===================================================
      
      This `div` uses Tailwind CSS's `grid` utilities to create a flexible layout
      that changes from a single column on mobile to multiple columns on larger screens.
      
      🎓 LEARN:
      - **Mobile-First Grid**: Designing for small screens first, then adding columns for larger screens.
      - `grid-cols-1`: One column on small screens.
      - `sm:grid-cols-2`: Two columns on small-medium screens and up.
      - `lg:grid-cols-4`: Four columns on large screens and up.
     -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
      
      <!--
        ABOUT SECTION - PROJECT INFORMATION & BRANDING
        =============================================
        
        Provides a brief overview of the application and its purpose, along with branding.
       -->
      <div class="col-span-1 sm:col-span-2 lg:col-span-1"> <!-- Takes up full width on mobile, 2/4 on small-medium, 1/4 on large -->
        <div class="flex items-center space-x-2 mb-4">
          <!-- jmrecodes Educational Blog Logo -->
          <Link href="/" class="flex items-center space-x-2">
          <div class="h-8 w-8 bg-gradient-to-br from-logo-navy to-logo-cyan rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-sm">EB</span>
          </div>
          <span class="text-lg font-bold text-gray-900">
            jmrecodes Educational Blog
          </span>
        </Link>
        </div>
        <p class="text-sm text-gray-600 leading-relaxed">
          A modern web application demonstrating Svelte 5, Inertia.js, and Laravel. 
          Built for learning and showcasing contemporary web development practices.
        </p>
        <div class="mt-3 text-xs {copyrightClasses}">
          <p>Educational project by {companyName}</p>
          <p>Svelte 5 • Inertia.js • Laravel • Tailwind CSS</p>
        </div>
      </div>
      
      <!--
        NAVIGATION SECTION - MAIN SITE LINKS
        ====================================
        
        Provides quick access to primary application features.
        This helps users navigate the site from the footer.
       -->
      <div>
        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
          Navigation
        </h3>
        <ul class="space-y-3">
          <li>
            <Link href="/" class="{linkClasses} text-sm hover:underline transition-colors">
              Home
            </Link>
          </li>
          <li>
            <Link href="/posts" class="{linkClasses} text-sm hover:underline transition-colors">
              Blog Posts
            </Link>
          </li>
          <li>
            <Link href="/about" class="{linkClasses} text-sm hover:underline transition-colors">
              About
            </Link>
          </li>
        </ul>
      </div>
      
      <!--
        ACCOUNT SECTION - USER-RELATED LINKS
        ====================================
        
        Provides quick access to user account management and authentication-related actions.
        Links are conditionally displayed based on whether a user is logged in.
       -->
      <div>
        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
          Account
        </h3>
        <ul class="space-y-3">
          {#if !auth.user} <!-- Only show Login/Register if user is NOT logged in -->
          <li>
            <Link href="/login" class="{linkClasses} text-sm hover:underline transition-colors">
              Login
            </Link>
          </li>
            <!-- Register link is only shown in development mode -->
            {#if import.meta.env.DEV || import.meta.env.MODE === 'development'}
            <li>
              <Link href="/register" class="{linkClasses} text-sm hover:underline transition-colors">
                Register
              </Link>
            </li>
            {/if}
          <li>
            <Link href="/forgot-password" class="{linkClasses} text-sm hover:underline transition-colors">
              Reset Password
            </Link>
          </li>
          {:else}
          <li>
            <button
              onclick={handleLogout}
              disabled={isLoggingOut}
              class="{linkClasses} text-sm hover:underline transition-colors cursor-pointer"
            >
                  {#if isLoggingOut}
                    Logging out...
                  {:else}
                    Logout
                  {/if}
            </button>
          </li>
          {/if}
        </ul>
      </div>
      
      <!--
        LEGAL SECTION - COMPLIANCE AND LEGAL LINKS
        ==========================================
        
        This section includes essential links to legal documents like Terms of Service and Privacy Policy.
        These are crucial for legal compliance and building user trust.
        
        🎓 LEARNING CONCEPT: Legal Compliance in Web Applications.
       -->
      <div>
        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
          Legal
        </h3>
        <ul class="space-y-3">
          <li>
            <Link href="/terms" class="{linkClasses} text-sm hover:underline transition-colors">
              Terms of Service
            </Link>
          </li>
          <li>
            <Link href="/privacy" class="{linkClasses} text-sm hover:underline transition-colors">
              Privacy Policy
            </Link>
          </li>
          <li>
            <a href="mailto:admin@jmrecodes.com" class="{linkClasses} text-sm hover:underline transition-colors" aria-label="Contact Support via Email">
              Contact Support
            </a>
          </li>
          <li>
            <Link href="/about" class="{linkClasses} text-sm hover:underline transition-colors">
              About This Project
            </Link>
          </li>
        </ul>
      </div>
      
      <!--
        SOCIAL LINKS SECTION (OPTIONAL) - EXTERNAL PROFILES
        ====================================================
        
        This section conditionally displays links to social media profiles.
        It demonstrates how to integrate external links safely.
        
        🎓 LEARNING CONCEPT: Conditional Rendering for Optional Features.
       -->
      {#if showSocialLinks}
        <div class="col-span-1 sm:col-span-2 lg:col-span-1">
          <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
            Connect
          </h3>
          <div class="flex space-x-4">
            <!-- GitHub Link -->
            <a href="https://github.com/jmrecodes" 
               target="_blank" 
               rel="noopener noreferrer"
               class="{linkClasses} hover:scale-110 transition-transform"
               aria-label="GitHub Profile">
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
              </svg>
            </a>
            
            <!-- LinkedIn Link -->
            <a href="https://linkedin.com/in/jmrecodes" 
               target="_blank" 
               rel="noopener noreferrer"
               class="{linkClasses} hover:scale-110 transition-transform"
               aria-label="LinkedIn Profile">
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
              </svg>
            </a>
            
            <!-- Portfolio/Website Link -->
            <a href="https://jmrecodes.com" 
               target="_blank" 
               rel="noopener noreferrer"
               class="{linkClasses} hover:scale-110 transition-transform"
               aria-label="Portfolio Website">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
              </svg>
            </a>
          </div>
        </div>
      {/if}
    </div>
    
    <!--
      COPYRIGHT AND BOTTOM SECTION - LEGAL & EDUCATIONAL DISCLAIMER
      ===============================================================
      
      This bottom section contains the copyright notice and a reiteration of the
      app's educational purpose. It uses responsive layout for different screen sizes.
      
      🎓 LEARN: How to implement copyright notices and consistent footers.
     -->
    <div class="mt-8 pt-6 border-t border-gray-200">
      <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        
        <!-- Copyright Notice -->
        <div class="text-center md:text-left">
          <p class="text-sm {copyrightClasses}">
            © {currentYear} {companyName}. All rights reserved.
          </p>
          <p class="text-xs {copyrightClasses} mt-1">
            jmrecodes Educational Blog - Modern Web Development Demonstration
          </p>
        </div>
        
        <!-- Educational Context / Disclaimer -->
        <div class="text-center md:text-right">
          <p class="text-xs {copyrightClasses}">
            Built for learning • Open for educational use
          </p>
          <p class="text-xs {copyrightClasses} mt-1">
            Demonstrating Svelte 5, Inertia.js & Laravel best practices
          </p>
        </div>
      </div>
    </div>
  </div>
</footer>

<!--
  🎓 EDUCATIONAL SUMMARY - REUSABLE FOOTER COMPONENT
  ==================================================
  
  This `Components/Footer.svelte` component is a prime example of building a reusable,
  maintainable, and well-structured UI component in a modern web application.
  
  ✅ KEY LEARNINGS:
  - **Modular Component Design**: Creating a self-contained, configurable UI block.
  - **Flexible Props**: Using `$props()` to adapt the component's behavior (e.g., show social links).
  - **Responsive Layouts**: Implementing dynamic grid and flexbox structures for all screen sizes.
  - **Comprehensive Navigation**: Providing links to all major parts of the application, including legal pages.
  - **Accessibility**: Ensuring semantic HTML and ARIA attributes for screen reader compatibility.
  - **Best Practices**: Demonstrating clean code, clear naming, and thoughtful UX.
  
  This component serves as an invaluable resource for understanding how to build
  reusable UI elements in Svelte applications that contribute to a cohesive design system.
-->