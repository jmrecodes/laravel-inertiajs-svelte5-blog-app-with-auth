<script>
  /*
   * TERMS OF SERVICE PAGE - LEGAL COMPLIANCE FOR EDUCATIONAL APP
   * ===========================================================
   * 
   * This Svelte component displays the Terms of Service for our jmrecodes Educational Blog application.
   * It serves as an example of how to implement legal documents in a web application,
   * emphasizing transparency and user understanding.
   * 
   * 🎓 BEGINNER LEARNING OBJECTIVES:
   * ================================
   * 1. **Legal Compliance**: Understanding the importance of Terms of Service in web applications.
   * 2. **Static Content Pages**: How to structure and display lengthy static text content.
   * 3. **Semantic HTML**: Using appropriate HTML5 tags (e.g., `<section>`, `<h2>`, `<ul>`) for structure.
   * 4. **Internal Navigation**: Linking to related legal documents (Privacy Policy) and main site pages.
   * 5. **Responsive Design**: Ensuring readability on various screen sizes.
   * 
   * 🔍 WHAT YOU'LL LEARN:
   * =====================
   * - How to use `<svelte:head>` to set dynamic page titles and meta tags for legal documents.
   * - Integrating `Link` components for seamless SPA-like navigation to other parts of the app.
   * - Styling long-form content with Tailwind CSS (e.g., `prose` plugin for typography).
   * - Adding disclaimers and notices to clarify the educational nature of the app.
   * - Structuring sections with clear headings for readability.
   *
   * KEY SECTIONS OF THIS COMPONENT:
   * - Agreement to Terms: The core statement of user acceptance.
   * - Educational Purpose and Limitations: Clarifies the app's learning focus and disclaims production use.
   * - User Accounts and Registration: Outlines responsibilities for account holders.
   * - Content and User Conduct: Rules for posting content and acceptable behavior.
   * - Data Collection and Usage: References the Privacy Policy for detailed data handling.
   * - Educational Disclaimers: Important warnings about data persistence and security for a demo app.
   * - Service Availability: Explains potential downtime and data resets.
   * - Termination: Conditions for account termination.
   * - Changes to Terms: How updates to these terms will be handled.
   * - Contact Information: How users can reach the developer with questions.
   */
  
  /*
   * IMPORTS AND DEPENDENCIES - SVELTE 5 + INERTIA.JS
   * ================================================
   * 
   * We import the `Link` component from Inertia.js for client-side navigation.
   * 🎓 LEARN: Why `Link` components are preferred over standard `<a>` tags in Inertia.js apps.
   */
  import { Link } from '@inertiajs/svelte'
  
  /*
   * COMPONENT PROPS - DATA FROM LARAVEL CONTROLLER
   * ==============================================
   * 
   * These properties are passed to this Svelte component from the
   * `LegalController::terms()` method. In this case, we only expect shared data
   * like `auth` (authentication status), as legal pages are mostly static.
   * 
   * 🎓 LEARN: How minimal data flows from Laravel (backend) to Svelte (frontend).
   */
  let { auth = {} } = $props() // `auth` object indicates if a user is logged in
</script>

<!--
  DYNAMIC PAGE HEAD - SEO OPTIMIZATION FOR LEGAL PAGES
  ====================================================
  
  `<svelte:head>` is used to set page-specific meta tags for search engines and browser tabs.
  For legal documents, we typically set `noindex, nofollow` to prevent search engines from indexing
  these pages, as they are not primary content for search results.
  
  🎓 LEARN: How `<svelte:head>` manages dynamic HTML `<head>` content and `robots` directives.
-->
<svelte:head>
  <title>Terms of Service | jmrecodes Educational Blog</title>
  <meta name="description" content="Terms of Service for jmrecodes Educational Blog - an educational web application demonstrating modern development practices." />
  <meta name="robots" content="noindex, nofollow" /> <!-- Prevents search engines from indexing this page -->
</svelte:head>

<!--
  MAIN CONTENT LAYOUT CONTAINER - OVERALL PAGE STRUCTURE
  ======================================================
  
  This `div` defines the main structure and background for the Terms of Service page,
  applying a minimum height and background color for a consistent look.
  It uses responsive design principles with Tailwind CSS utilities.
-->
<div class="min-h-screen bg-gray-50">
  <!-- 
    HEADER NAVIGATION BAR - CONSISTENT SITE NAVIGATION
    ================================================
    
    This navigation bar provides quick links to the home page and authentication-related pages.
    Its presence ensures users can easily navigate back to the main application.
    
    🎓 LEARN: How to create a consistent header for static pages and conditionally display links.
   -->
  <nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <!-- Brand Logo and Name -->
        <Link href="/" class="flex items-center space-x-2">
          <div class="h-8 w-8 bg-gradient-to-br from-logo-navy to-logo-cyan rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-sm">EB</span>
          </div>
          <img src="/logo.jpg" alt="jmrecodes Educational Blog" class="h-8 w-8">
          <span class="text-xl font-bold text-gray-900">jmrecodes Educational Blog</span>
        </Link>
        
        <!-- User Navigation Links (Conditional) -->
        <div class="flex items-center space-x-4">
          {#if auth.user} <!-- If a user is logged in, show Dashboard link -->
            <Link href="/dashboard" class="text-sm text-gray-600 hover:text-gray-900">Dashboard</Link>
          {:else} <!-- If no user is logged in, show Login and Sign Up links -->
            <Link href="/login" class="text-sm text-gray-600 hover:text-gray-900">Login</Link>
            <!-- Register link is only shown in development mode -->
            {#if import.meta.env.DEV || import.meta.env.MODE === 'development'}
            <Link href="/register" class="text-sm bg-accent-500 text-white px-3 py-1 rounded-md hover:bg-accent-600">Sign Up</Link>
            {/if}
          {/if}
        </div>
      </div>
    </div>
  </nav>

  <!-- 
    MAIN CONTENT AREA - TERMS OF SERVICE DOCUMENT
    =============================================
    
    This section contains the actual text content of the Terms of Service.
    It uses Tailwind CSS's `prose` plugin to automatically style the rich text
    content for optimal readability, mimicking professional document layouts.
   -->
  <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-8 sm:px-8">
        <!-- Page Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-2">Terms of Service</h1>
          <p class="text-sm text-gray-600">
            Last updated: June 10, 2025
          </p>
          <!-- Educational Notice Box -->
          <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
            <div class="flex">
              <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div class="ml-3">
                <p class="text-sm text-blue-800">
                  <strong>Educational Application Notice:</strong> This is an educational demonstration application built to showcase modern web development practices. These terms serve as an example of legal compliance in web applications.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Terms Content (Styled with Tailwind's @tailwindcss/typography plugin) -->
        <div class="prose prose-sm max-w-none space-y-6">
          
          <!-- Section 1: Agreement to Terms -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Agreement to Terms</h2>
            <p class="text-gray-700 leading-relaxed">
              By accessing and using jmrecodes Educational Blog ("the Service"), you agree to be bound by these Terms of Service ("Terms"). 
              If you disagree with any part of these terms, then you may not access the Service.
            </p>
            <p class="text-gray-700 leading-relaxed mt-2">
              This Service is operated by jmrecodes for educational and demonstration purposes. It showcases modern web development 
              technologies including Svelte 5, Inertia.js, and Laravel.
            </p>
          </section>

          <!-- Section 2: Educational Purpose and Limitations -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Educational Purpose and Limitations</h2>
            <p class="text-gray-700 leading-relaxed">
              jmrecodes Educational Blog is designed for educational purposes to demonstrate:
            </p>
            <ul class="list-disc pl-6 mt-2 space-y-1 text-gray-700">
              <li>Modern web application development patterns</li>
              <li>Authentication and user management systems</li>
              <li>Blog content management functionality</li>
              <li>Security best practices in web development</li>
              <li>User interface and user experience design</li>
            </ul>
            <p class="text-gray-700 leading-relaxed mt-3">
              <strong>Important:</strong> This application is not intended for production use or commercial purposes. 
              Data may be reset periodically for educational demonstrations.
            </p>
          </section>

          <!-- Section 3: User Accounts and Registration -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">3. User Accounts and Registration</h2>
            <p class="text-gray-700 leading-relaxed">
              When you create an account with us, you must provide information that is accurate, complete, and current at all times. 
              You are responsible for:
            </p>
            <ul class="list-disc pl-6 mt-2 space-y-1 text-gray-700">
              <li>Safeguarding your password and all activities under your account</li>
              <li>Maintaining the security of your account credentials</li>
              <li>Notifying us immediately of any unauthorized use of your account</li>
              <li>Using only one account per person</li>
            </ul>
          </section>

          <!-- Section 4: Content and User Conduct -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Content and User Conduct</h2>
            <p class="text-gray-700 leading-relaxed">
              Users may create and publish blog posts for educational purposes. By posting content, you agree that:
            </p>
            <ul class="list-disc pl-6 mt-2 space-y-1 text-gray-700">
              <li>You own or have the right to use the content you post</li>
              <li>Your content will not violate any laws or infringe on others' rights</li>
              <li>You will not post spam, malicious content, or inappropriate material</li>
              <li>You understand that content may be used for educational demonstrations</li>
            </ul>
            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
              <p class="text-sm text-yellow-800">
                <strong>Educational Use:</strong> Content posted may be used as examples in educational contexts. 
                Do not post sensitive or personal information.
              </p>
            </div>
          </section>

          <!-- Section 5: Data Collection and Usage -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Data Collection and Usage</h2>
            <p class="text-gray-700 leading-relaxed">
              We collect minimal data necessary for the application to function:
            </p>
            <ul class="list-disc pl-6 mt-2 space-y-1 text-gray-700">
              <li>Name and email address for account creation</li>
              <li>Blog posts and associated metadata</li>
              <li>Basic usage analytics for educational purposes</li>
            </ul>
            <p class="text-gray-700 leading-relaxed mt-2">
              For detailed information about data handling, please review our 
              <Link href="/privacy" class="text-blue-600 hover:text-blue-800 underline">Privacy Policy</Link>.
            </p>
          </section>

          <!-- Section 6: Educational Disclaimers -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Educational Disclaimers</h2>
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
              <p class="text-sm text-red-800 leading-relaxed">
                <strong>IMPORTANT DISCLAIMERS:</strong>
              </p>
              <ul class="list-disc pl-4 mt-2 space-y-1 text-sm text-red-800">
                <li>This application is provided "as-is" for educational purposes only</li>
                <li>No warranty or guarantee of data persistence or security</li>
                <li>Data may be reset or lost during educational demonstrations</li>
                <li>Not suitable for storing important or sensitive information</li>
                <li>May contain bugs or incomplete features as part of the learning process</li>
              </ul>
            </div>
          </section>

          <!-- Section 7: Service Availability -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Service Availability</h2>
            <p class="text-gray-700 leading-relaxed">
              jmrecodes Educational Blog is provided for learning purposes and may experience:
            </p>
            <ul class="list-disc pl-6 mt-2 space-y-1 text-gray-700">
              <li>Planned downtime for educational demonstrations</li>
              <li>Data resets for testing purposes</li>
              <li>Feature changes as part of the learning curriculum</li>
              <li>Limited support availability</li>
            </ul>
          </section>

          <!-- Section 8: Termination -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Termination</h2>
            <p class="text-gray-700 leading-relaxed">
              We may terminate or suspend access immediately, without prior notice, for conduct that we believe 
              violates these Terms or is harmful to other users, us, or third parties, or for any other reason.
            </p>
            <p class="text-gray-700 leading-relaxed mt-2">
              You may delete your account at any time through the account settings page.
            </p>
          </section>

          <!-- Section 9: Changes to Terms -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Changes to Terms</h2>
            <p class="text-gray-700 leading-relaxed">
              We reserve the right to modify these terms at any time for educational purposes. 
              We will notify users of significant changes through the application interface.
            </p>
          </section>

          <!-- Section 10: Contact -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">10. Contact Information</h2>
            <p class="text-gray-700 leading-relaxed">
              For questions about these Terms of Service or the jmrecodes Educational Blog application, please contact:
            </p>
            <div class="mt-2 p-3 bg-gray-50 rounded border">
              <p class="text-sm text-gray-700">
                <strong>Email:</strong> admin@jmrecodes.com<br>
                <strong>Project:</strong> jmrecodes Educational Blog - Web Development Demonstration<br>
                <strong>Purpose:</strong> Educational and portfolio demonstration
              </p>
            </div>
          </section>

        </div>
        
        <!-- 
          FOOTER ACTIONS - NAVIGATION TO RELATED PAGES
          ============================================
          
          This section provides quick links to the Privacy Policy and the Home page,
          as well as the copyright notice.
         -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
            <div class="text-sm text-gray-600">
              <Link href="/privacy" class="text-blue-600 hover:text-blue-800 underline">Privacy Policy</Link>
              <span class="mx-2">•</span>
              <Link href="/" class="text-blue-600 hover:text-blue-800 underline">Return to Home</Link>
            </div>
            <div class="text-sm text-gray-500">
              © 2025 jmrecodes. Educational use only.
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</div>

<!--
  🎓 EDUCATIONAL SUMMARY - TERMS OF SERVICE COMPONENT
  ==================================================
  
  This `Legal/Terms.svelte` component is a practical example of building essential legal pages
  in a modern web application.
  
  ✅ KEY LEARNINGS:
  - **Legal Document Structure**: Organizing complex text into readable sections.
  - **Semantic HTML**: Using appropriate tags for accessibility and structure.
  - **Content Styling**: Applying Tailwind CSS for professional typography.
  - **Transparency**: Clearly communicating the educational nature and limitations of the app.
  - **Internal Linking**: Connecting legal documents with each other and the main site.
  
  This component serves as a robust foundation for any legal documentation interface
  in your web application, focusing on clarity, compliance, and user understanding.
--> 