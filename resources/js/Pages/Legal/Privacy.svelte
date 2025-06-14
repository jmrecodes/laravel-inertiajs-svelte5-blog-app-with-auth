<script>
  /*
   * PRIVACY POLICY PAGE - EDUCATIONAL WEB DEVELOPMENT
   * ================================================
   * 
   * This Svelte component displays the Privacy Policy for our jmrecodes Educational Blog application.
   * It demonstrates how to implement a transparent privacy policy in a web application,
   * covering data collection, usage, and user rights.
   * 
   * 🎓 BEGINNER LEARNING OBJECTIVES:
   * ================================
   * 1. **Data Privacy**: Understanding why privacy policies are crucial for user trust and legal compliance.
   * 2. **Static Content Management**: Structuring and presenting detailed textual content effectively.
   * 3. **Information Transparency**: Clearly explaining data practices to users.
   * 4. **User Rights**: Informing users about their control over their personal data.
   * 5. **Educational Disclaimers**: Highlighting the non-production nature and limitations of a demo app.
   * 
   * 🔍 WHAT YOU'LL LEARN:
   * =====================
   * - How to use `<svelte:head>` for setting SEO-friendly meta tags (e.g., `noindex, nofollow` for private pages).
   * - Integrating `Link` components for smooth navigation to related legal documents (Terms of Service) and other site areas.
   * - Applying Tailwind CSS typography (`prose` plugin) for well-formatted, readable long-form content.
   * - Structuring legal sections with clear headings and bullet points for easy readability.
   * - Adding distinct notice boxes for important educational messages and disclaimers.
   *
   * KEY SECTIONS OF THIS COMPONENT:
   * - Introduction: Overview of the Privacy Policy.
   * - Information We Collect: Details on what data is collected (minimal for this app).
   * - How We Use Your Information: Explanation of data usage (solely for educational demo).
   * - Data Security: Measures taken to protect data in an educational context.
   * - Your Rights and Control: How users can manage their data (access, edit, delete).
   * - Data Retention: Policy on how long data is kept.
   * - Educational Context and Limitations: Crucial disclaimers about production readiness.
   * - Changes to This Policy: How policy updates will be communicated.
   * - Contact Information: How to get in touch with questions.
   */
  
  /*
   * IMPORTS AND DEPENDENCIES - SVELTE 5 + INERTIA.JS
   * ================================================
   * 
   * We import the `Link` component from Inertia.js for client-side navigation.
   * 🎓 LEARN: Why `Link` components are preferred over standard `<a>` tags for internal navigation in Inertia.js apps.
   */
  import { Link } from '@inertiajs/svelte'
  
  /*
   * COMPONENT PROPS - DATA FROM LARAVEL CONTROLLER
   * ==============================================
   * 
   * These properties are passed to this Svelte component from the
   * `LegalController::privacy()` method. Similar to `Terms.svelte`, we mainly expect
   * shared data like `auth` (authentication status) as this page is mostly static.
   * 
   * 🎓 LEARN: How minimal data flows from Laravel (backend) to Svelte (frontend) for static content.
   */
  let { 
    auth = {}  // `auth` object indicates if a user is logged in (used for conditional navigation)
  } = $props()
</script>

<!--
  DYNAMIC PAGE HEAD - SEO OPTIMIZATION FOR LEGAL PAGES
  ====================================================
  
  `<svelte:head>` is used to set page-specific meta tags for search engines and browser tabs.
  For legal documents like a Privacy Policy, we typically set `noindex, nofollow` to prevent
  search engines from indexing these pages, as they are not primary content for search results.
  
  🎓 LEARN: How `<svelte:head>` manages dynamic HTML `<head>` content and `robots` directives.
-->
<svelte:head>
  <title>Privacy Policy | jmrecodes Educational Blog</title>
  <meta name="description" content="Privacy Policy for jmrecodes Educational Blog - learn how we handle your data in this educational web application." />
  <meta name="robots" content="noindex, nofollow" /> <!-- Prevents search engines from indexing this page -->
</svelte:head>

<!--
  MAIN PAGE LAYOUT STRUCTURE - OVERALL PAGE CONTAINER
  ===================================================
  
  This `div` defines the main structure and background for the Privacy Policy page,
  applying a minimum height and background color for a consistent look.
  It uses responsive design principles with Tailwind CSS utilities.
-->
<div class="min-h-screen bg-gray-50">
  
  <!-- 
    NAVIGATION HEADER - CONSISTENT SITE NAVIGATION
    =============================================
    
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
            <Link href="/dashboard" class="text-sm text-gray-600 hover:text-gray-900">
              Dashboard
            </Link>
          {:else} <!-- If no user is logged in, show Login and Sign Up links -->
            <Link href="/login" class="text-sm text-gray-600 hover:text-gray-900">
              Login
            </Link>
            <!-- Register link is only shown in development mode -->
            {#if import.meta.env.DEV || import.meta.env.MODE === 'development'}
            <Link href="/register" class="text-sm bg-accent-500 text-white px-3 py-1 rounded-md hover:bg-accent-600">
              Sign Up
            </Link>
            {/if}
          {/if}
        </div>
      </div>
    </div>
  </nav>

  <!-- 
    MAIN CONTENT AREA - PRIVACY POLICY DOCUMENT
    ===========================================
    
    This section contains the actual text content of the Privacy Policy.
    It uses Tailwind CSS's `prose` plugin to automatically style the rich text
    content for optimal readability, mimicking professional document layouts.
   -->
  <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-8 sm:px-8">
        
        <!-- Page Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
          <p class="text-sm text-gray-600">
            Last updated: June 10, 2025
          </p>
          
          <!-- Educational Context Notice -->
          <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
            <div class="flex">
              <!-- Information icon SVG -->
              <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div class="ml-3">
                <p class="text-sm text-blue-800">
                  <strong>Educational Application Notice:</strong> This privacy policy demonstrates how to properly handle user data in web applications. This is a learning project showcasing best practices for privacy compliance.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- 
          PRIVACY POLICY CONTENT SECTIONS - ORGANIZED FOR READABILITY
          ===========================================================
          
          Standard privacy policy sections, each with a clear heading and explanatory text.
          Uses bulleted lists for easy scanning of information.
          
          🎓 LEARN: How to structure legal documents for clarity and compliance.
         -->
        <div class="prose prose-sm max-w-none space-y-6">
          
          <!-- Section 1: Introduction and Scope -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Introduction</h2>
            <p class="text-gray-700 leading-relaxed">
              This Privacy Policy explains how jmrecodes Educational Blog ("we," "our," or "us") collects, uses, and protects 
              your personal information when you use our educational web application. This application is designed 
              for learning purposes to demonstrate modern web development practices.
            </p>
            <p class="text-gray-700 leading-relaxed mt-2">
              By using jmrecodes Educational Blog, you agree to the collection and use of information in accordance with this policy.
            </p>
          </section>

          <!-- Section 2: Information We Collect -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Information We Collect</h2>
            <p class="text-gray-700 leading-relaxed">
              For educational purposes, we collect minimal information necessary to demonstrate web application functionality:
            </p>
            
            <!-- Subsection: Account Information -->
            <div class="mt-4">
              <h3 class="text-lg font-medium text-gray-800 mb-2">Account Information:</h3>
              <ul class="list-disc pl-6 space-y-1 text-gray-700">
                <li><strong>Name:</strong> To personalize your experience and identify your content</li>
                <li><strong>Email Address:</strong> For account creation, login, and password reset functionality</li>
                <li><strong>Password:</strong> Securely hashed for account security (we never see your actual password)</li>
              </ul>
            </div>
            
            <!-- Subsection: Content Information -->
            <div class="mt-4">
              <h3 class="text-lg font-medium text-gray-800 mb-2">Content Information:</h3>
              <ul class="list-disc pl-6 space-y-1 text-gray-700">
                <li><strong>Blog Posts:</strong> Content, titles, and metadata of posts you create</li>
                <li><strong>Publishing Status:</strong> Whether posts are drafts or published</li>
                <li><strong>Timestamps:</strong> When content is created, updated, or published</li>
              </ul>
            </div>
            
            <!-- Educational note about data collection -->
            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded">
              <p class="text-sm text-green-800">
                <strong>Educational Focus:</strong> We collect only the minimum data needed to demonstrate 
                authentication, content management, and user experience patterns. No tracking, analytics, 
                or third-party data collection is implemented.
              </p>
            </div>
          </section>

          <!-- Section 3: How We Use Your Information -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">3. How We Use Your Information</h2>
            <p class="text-gray-700 leading-relaxed">
              Your information is used exclusively for educational demonstration purposes:
            </p>
            <ul class="list-disc pl-6 mt-2 space-y-1 text-gray-700">
              <li>To provide account authentication and user login functionality</li>
              <li>To enable blog post creation, editing, and management features</li>
              <li>To demonstrate user-specific content and dashboard functionality</li>
              <li>To showcase password reset and account management features</li>
              <li>To provide examples of proper data handling in web applications</li>
            </ul>
            
            <!-- What we DON'T do (Important Disclaimer) -->
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded">
              <p class="text-sm text-red-800">
                <strong>What We DON'T Do:</strong> We do not sell, rent, or share your data with third parties. 
                We do not use your data for marketing, advertising, or any commercial purposes. This is purely 
                an educational demonstration.
              </p>
            </div>
          </section>

          <!-- Section 4: Data Security -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Data Security</h2>
            <p class="text-gray-700 leading-relaxed">
              We implement security measures appropriate for an educational application:
            </p>
            <ul class="list-disc pl-6 mt-2 space-y-1 text-gray-700">
              <li><strong>Password Hashing:</strong> Passwords are securely hashed using Laravel's built-in bcrypt</li>
              <li><strong>CSRF Protection:</strong> Forms are protected against cross-site request forgery</li>
              <li><strong>Input Validation:</strong> All user input is validated and sanitized</li>
              <li><strong>Session Security:</strong> Secure session management with automatic expiration</li>
              <li><strong>Database Security:</strong> SQLite database with proper query protection</li>
            </ul>
            
            <!-- Educational disclaimer about security (Important Warning) -->
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
              <p class="text-sm text-yellow-800">
                <strong>Educational Context:</strong> While we implement security best practices, 
                this is a learning environment. Do not store sensitive personal information in this application.
              </p>
            </div>
          </section>

          <!-- Section 5: Your Rights and Control -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Your Rights and Control</h2>
            <p class="text-gray-700 leading-relaxed">
              You have full control over your data in this educational application:
            </p>
            <ul class="list-disc pl-6 mt-2 space-y-1 text-gray-700">
              <li><strong>Access:</strong> View all your account information and blog posts</li>
              <li><strong>Edit:</strong> Update your name, email, and password at any time</li>
              <li><strong>Delete:</strong> Remove individual blog posts or your entire account</li>
              <li><strong>Export:</strong> Copy your content for use elsewhere (manual process)</li>
              <li><strong>Portability:</strong> Your data is not locked into our system</li>
            </ul>
            
            <!-- How to exercise rights (Links to Profile and Manage Posts) -->
            <div class="mt-3">
              <h4 class="font-medium text-gray-800">How to Exercise These Rights:</h4>
              <ul class="list-disc pl-6 mt-1 space-y-1 text-gray-700 text-sm">
                <li>Account settings: Visit your <Link href="/profile" class="text-blue-600 hover:text-blue-800 underline">Profile</Link> page</li>
                <li>Content management: Use the <Link href="/manage-posts" class="text-blue-600 hover:text-blue-800 underline">Manage Posts</Link> page</li>
                <li>Account deletion: Use the "Delete Account" option in profile settings</li>
              </ul>
            </div>
          </section>

          <!-- Section 6: Data Retention -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Data Retention</h2>
            <p class="text-gray-700 leading-relaxed">
              As an educational application, data retention follows learning requirements:
            </p>
            <ul class="list-disc pl-6 mt-2 space-y-1 text-gray-700">
              <li>Account data is retained while your account is active</li>
              <li>Blog posts are retained until you delete them or your account</li>
              <li>Data may be reset periodically for educational demonstrations</li>
              <li>Deleted accounts and posts are permanently removed from our database</li>
            </ul>
          </section>

          <!-- Section 7: Educational Context and Limitations -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Educational Context and Limitations</h2>
            <div class="bg-orange-50 border border-orange-200 rounded-md p-4">
              <p class="text-sm text-orange-800 leading-relaxed">
                <strong>Important Educational Disclaimers:</strong>
              </p>
              <ul class="list-disc pl-4 mt-2 space-y-1 text-sm text-orange-800">
                <li>This application is designed for learning and demonstration purposes</li>
                <li>Data persistence is not guaranteed during educational resets</li>
                <li>No commercial data processing or analytics are performed</li>
                <li>Security measures are appropriate for educational use, not production</li>
                <li>Do not store sensitive or important personal information</li>
              </ul>
            </div>
          </section>

          <!-- Section 8: Changes to This Policy -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Changes to This Privacy Policy</h2>
            <p class="text-gray-700 leading-relaxed">
              We may update this Privacy Policy for educational purposes to demonstrate:
            </p>
            <ul class="list-disc pl-6 mt-2 space-y-1 text-gray-700">
              <li>How to properly communicate policy changes to users</li>
              <li>Best practices for privacy policy versioning</li>
              <li>User notification patterns for legal updates</li>
            </ul>
            <p class="text-gray-700 leading-relaxed mt-2">
              Changes will be posted on this page with an updated "Last updated" date.
            </p>
          </section>

          <!-- Section 9: Contact Information -->
          <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Contact Information</h2>
            <p class="text-gray-700 leading-relaxed">
              For questions about this Privacy Policy or the jmrecodes Educational Blog application:
            </p>
            <div class="mt-2 p-3 bg-gray-50 rounded border">
              <p class="text-sm text-gray-700">
                <strong>Developer:</strong> jmrecodes<br>
                <strong>Email:</strong> admin@jmrecodes.com<br>
                <strong>Project Type:</strong> Educational Web Development Demonstration<br>
                <strong>Learning Focus:</strong> Modern web application development with Svelte 5, Inertia.js, and Laravel
              </p>
            </div>
          </section>

        </div>
        
        <!-- 
          FOOTER NAVIGATION - LINKS TO RELATED LEGAL PAGES
          ================================================
          
          This section provides quick links to the Terms of Service and the Home page,
          along with the copyright notice. It ensures consistent bottom navigation.
         -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
            <div class="text-sm text-gray-600">
              <Link href="/terms" class="text-blue-600 hover:text-blue-800 underline">Terms of Service</Link>
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
  🎓 EDUCATIONAL SUMMARY - PRIVACY POLICY COMPONENT
  ==================================================
  
  This `Legal/Privacy.svelte` component is a strong example of building an essential legal page
  for data privacy in a modern web application.
  
  ✅ KEY LEARNINGS:
  - **Data Privacy Principles**: Understanding how to communicate data collection and usage.
  - **Structured Content**: Organizing complex legal text for clarity and readability.
  - **Transparency**: Clearly outlining user rights and app limitations for an educational context.
  - **Semantic Markup**: Using HTML tags that enhance accessibility and structure.
  - **Consistent UI**: Applying a reusable layout and navigation patterns.
  
  This component serves as a robust foundation for any legal documentation interface
  in your web application, focusing on clarity, user rights, and compliance.
-->