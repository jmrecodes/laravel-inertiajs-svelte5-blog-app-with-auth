<!--
  BLOG POST SHOW PAGE - INDIVIDUAL POST DISPLAY FOR EDUCATIONAL APP
  ==================================================================
  
  This Svelte component is responsible for displaying a single blog post in its entirety.
  It focuses on providing a rich reading experience, comprehensive SEO, and responsive design.
  
  🎓 BEGINNER LEARNING OBJECTIVES:
  ============================
  1. **Content Rendering**: Displaying dynamic HTML content safely (using `@html`).
  2. **SEO Best Practices**: Implementing meta tags and structured data for search engines and social media.
  3. **User Experience**: Providing relevant metadata (author, reading time) and navigation.
  4. **Conditional UI**: Showing/hiding elements based on user authorization (e.g., edit button).
  5. **Web Share API**: Implementing native sharing functionality for modern browsers.
  6. **Data Flow**: Understanding how a single post object is passed from Laravel to Svelte.
  
  🔍 WHAT YOU'LL LEARN:
  ====================
  - How to set dynamic `<title>` and `<meta>` tags using `<svelte:head>`.
  - The structure and importance of JSON-LD for structured data.
  - How to conditionally display content (e.g., featured image, update notice).
  - Implementing client-side sharing capabilities.
  - Formatting dates for readability.
  - Integrating `Link` and `Footer` components for consistent navigation.
  
  FEATURES IMPLEMENTED IN THIS COMPONENT:
  ======================================
  - Full display of blog post title, excerpt, and rich content.
  - Dynamic author information, published date, and estimated reading time.
  - Comprehensive SEO optimization including Open Graph and Twitter Cards.
  - JSON-LD structured data for enhanced search engine visibility.
  - Native Web Share API integration with clipboard fallback.
  - Conditional edit link for authorized authors.
  - Navigation back to the blog listing and other user dashboards.
  
  This component is heavily commented to guide you through each concept
  and pattern involved in building a detailed single post view.
-->

<script>
  /*
   * IMPORTS AND DEPENDENCIES - SVELTE 5 + INERTIA.JS
   * ================================================
   * 
   * We import necessary modules for navigation, linking, and reusable components.
   * 🎓 LEARN: How to import and use external functionality in Svelte.
   */
  import { router } from '@inertiajs/svelte' // Inertia.js router for client-side navigation
  import { Link } from '@inertiajs/svelte'     // Inertia.js Link component for SPA-like navigation
  import Footer from '../../Components/Footer.svelte' // Reusable Footer component

  /*
   * COMPONENT PROPS - DATA FROM LARAVEL CONTROLLER
   * ==============================================
   * 
   * These properties are passed to this Svelte component from the
   * `BlogPostController::show()` method. Inertia.js automatically maps
   * the data returned by the controller to `$props` in Svelte.
   * 
   * 🎓 LEARN: How a single data object (the post) is passed from Laravel (backend) to Svelte (frontend).
   * 
   * - `post`: The full blog post object, including its content, metadata, and associated `user` object.
   * - `canEdit`: A boolean flag indicating if the current authenticated user has permission to edit this post.
   * - `auth`: Global authentication data (available on all Inertia pages via middleware).
   * - `flash`: One-time flash messages (e.g., success messages).
   * - `errors`: Validation errors (though less common on a show page).
   */
  let { 
    post,           
    canEdit = false, 
    auth = {},      
    flash = {},     
    errors = {}     
  } = $props()
  
  /*
   * UTILITY FUNCTIONS - DATA TRANSFORMATION AND BROWSER APIs
   * ========================================================
   * 
   * Helper functions for formatting data and interacting with browser features (like Web Share API).
   * 🎓 LEARN: How to create and use simple utility functions in Svelte components.
   */
  
  /**
   * FORMAT DATE FOR DISPLAY
   * -----------------------
   * Converts a date string (e.g., from Laravel) into a human-readable format.
   * Useful for displaying publication dates consistently.
   */
  function formatDate(dateString) {
    if (!dateString) return 'N/A' // Handle cases where date might be missing
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }
  
  /**
   * GET CURRENT PAGE URL
   * --------------------
   * Retrieves the full URL of the current page. Essential for social sharing and canonical links.
   */
  function getCurrentUrl() {
    // `window.location.href` gives the full URL of the current page.
    // This function is client-side only.
    return window.location.href
  }
  
  /**
   * SHARE POST FUNCTIONALITY (WEB SHARE API WITH FALLBACK)
   * ------------------------------------------------------
   * Implements the native Web Share API for sharing content on mobile devices.
   * Provides a fallback to copying the URL to clipboard for browsers that don't support it.
   * 
   * 🎓 LEARN:
   * - **`navigator.share`**: Modern browser API for native sharing.
   * - **`navigator.clipboard.writeText`**: Copying text to the user's clipboard.
   * - **Promises**: Handling asynchronous operations (share and clipboard).
   */
  function sharePost() {
    // Check if the Web Share API is supported by the browser
    if (navigator.share) {
      navigator.share({
        title: post.title, // Title of the content to share
        text: post.excerpt || `Read "${post.title}" on jmrecodes Educational Blog`, // Text content
        url: getCurrentUrl() // URL to share
      })
      .then(() => console.log('✅ Post shared successfully!'))
      .catch((error) => console.error('❌ Error sharing post:', error));
    } else {
      // Fallback for browsers that don't support Web Share API
      // Copy the current page URL to the clipboard
      navigator.clipboard.writeText(getCurrentUrl()).then(() => {
        alert('🔗 Post URL copied to clipboard!'); // Inform user
      }).catch(err => {
        console.error('❌ Failed to copy URL:', err);
        alert('Failed to copy URL. Please copy it manually.');
      });
    }
  }
</script>

<!--
  DYNAMIC PAGE HEAD - COMPREHENSIVE SEO OPTIMIZATION
  ===================================================
  
  `<svelte:head>` is used to set page-specific meta tags and structured data for SEO.
  This is crucial for search engine visibility, accurate social media previews, and accessibility.
  
  🎓 LEARN:
  - **Standard Meta Tags**: Title, description, robots, author for basic SEO.
  - **Open Graph (og:...)**: Specific tags for Facebook, LinkedIn, etc., to control how links appear.
  - **Twitter Cards (twitter:...)**: Specific tags for Twitter previews.
  - **Canonical URL**: Prevents duplicate content issues by specifying the preferred URL.
  - **JSON-LD Structured Data**: Provides rich snippets in search results (e.g., author, image, publication date).
-->
<svelte:head>
  <!-- Primary HTML Meta Tags for SEO -->
  <title>{post.meta_title || post.title} | jmrecodes Educational Blog</title> <!-- Dynamic title from post data -->
  <meta name="title" content="{post.meta_title || post.title} | jmrecodes Educational Blog" />
  <meta name="description" content={post.meta_description || post.excerpt || `Read ${post.title} on jmrecodes Educational Blog`} />
  <meta name="robots" content="index, follow" /> <!-- Allow search engines to index and follow links -->
  <meta name="author" content={post.user.name} />
  <meta name="article:published_time" content={post.published_at} />
  <meta name="article:modified_time" content={post.updated_at} />
  
  <!-- Open Graph / Facebook Meta Tags -->
  <meta property="og:type" content="article" /> <!-- Specify content type as an article -->
  <meta property="og:title" content={post.meta_title || post.title} />
  <meta property="og:description" content={post.meta_description || post.excerpt || `Read ${post.title} on jmrecodes Educational Blog`} />
  <meta property="og:url" content={getCurrentUrl()} /> <!-- The canonical URL of the post -->
  <meta property="og:site_name" content="Educational Blog" />
  <meta property="article:author" content={post.user.name} />
  <meta property="article:published_time" content={post.published_at} />
  
  <!-- Twitter Card Meta Tags -->
  <meta name="twitter:card" content="summary_large_image" /> <!-- Use large image summary card -->
  <meta name="twitter:title" content={post.meta_title || post.title} />
  <meta name="twitter:description" content={post.meta_description || post.excerpt || `Read ${post.title} on jmrecodes Educational Blog`} />
  
  <!-- Featured Image (Conditional) -->
  {#if post.featured_image} <!-- Only include if a featured image exists -->
    <meta property="og:image" content={post.featured_image} />
    <meta name="twitter:image" content={post.featured_image} />
  {/if}
  
  <!-- Canonical URL to prevent duplicate content issues -->
  <link rel="canonical" href={getCurrentUrl()} />
  
  <!-- 
    JSON-LD Structured Data for Rich Snippets
    =========================================
    
    This script provides structured data in JSON-LD format,
    helping search engines display rich results (e.g., author, image) in search results.
    
    🎓 LEARN: How structured data enhances SEO and user experience.
   -->
  <script type="application/ld+json">
    {JSON.stringify({
      "@context": "https://schema.org", // Specifies the vocabulary being used (Schema.org)
      "@type": "BlogPosting",          // Defines the type of content as a blog post
      "headline": post.title,          // The main title of the blog post
      "description": post.excerpt || post.meta_description, // A summary of the content
      "image": post.featured_image || "", // URL to the featured image
      "author": {
        "@type": "Person", // The author is a Person
        "name": post.user.name // Author's name
      },
      "publisher": {
        "@type": "Organization", // The publisher is an Organization
        "name": "Educational Blog" // Name of the publishing organization
      },
      "datePublished": post.published_at, // Date the post was originally published
      "dateModified": post.updated_at,   // Date the post was last modified
      "mainEntityOfPage": {
        "@type": "WebPage", // This blog post is a WebPage
        "@id": getCurrentUrl() // Canonical URL for this specific page
      }
    })}
  </script>
</svelte:head>

<!--
  MAIN CONTENT CONTAINER - OVERALL PAGE LAYOUT
  ===========================================
  
  This `div` provides the overall structure for the individual blog post page,
  applying a minimum height and background color for a consistent look.
  It uses responsive design principles with Tailwind CSS utilities.
-->
<div class="min-h-screen bg-gray-50">
  <!-- 
    NAVIGATION BAR - QUICK LINKS AND USER STATUS
    ============================================
    
    This header provides consistent navigation options and displays the authenticated user's status.
   -->
  <div class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <nav class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <!-- Back Navigation to Blog Posts List -->
          <Link 
            href="/posts" 
            class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-primary-700 transition-colors duration-200"
          >
            ← Back to posts
          </Link>
          {#if auth?.user} <!-- Conditionally display dashboard and my posts links if user is authenticated -->
            <!-- Link to Dashboard -->
            <Link href="/dashboard" class="text-sm text-gray-600 hover:text-blue-700 transition-colors duration-200">
              <span class="text-gray-400 mr-2">|</span>
                Dashboard
              <span class="text-gray-400 ml-2">|</span>
              </Link>
              
            <!-- Link to My Posts (Manage BlogPosts) -->
            <Link href="/manage-posts" class="text-sm text-gray-600 hover:text-blue-700 transition-colors duration-200">
              My Posts →
            </Link>
          {/if}
        </div>
        
        <div class="flex items-center space-x-3">
          <span class="text-sm text-gray-600">Welcome, {auth?.user?.name || 'User'}</span>

          <!-- Edit Link (Conditional for Authorized Users) -->
          <!-- Only shows if `canEdit` prop is true (meaning current user is the author) -->
          {#if canEdit}
            <Link
              href={`/posts/${post.id}/edit`}
              class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-accent-500 hover:bg-accent-600 transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Edit Post
            </Link>
          {/if}
          <!-- Button to Write New Post -->
          <Link href="/posts/create" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-cyan-500 hover:bg-cyan-600 rounded-md transition-colors duration-200">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Write Post
          </Link>
        </div>
      </nav>
    </div>
  </div>
  
  <!-- 
    MAIN ARTICLE CONTENT AREA
    =========================
    
    This `article` tag contains the main content of the blog post. It uses semantic HTML
    for better structure and accessibility, and applies styling for readability.
   -->
  <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- 
      ARTICLE HEADER - TITLE, EXCERPT, METADATA, AND SHARE BUTTON
      ==========================================================
      
      This section serves as the introduction to the blog post, displaying key information
      before the main content.
     -->
    <header class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
      <!-- Post Title -->
      <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 leading-tight mb-6">
        {post.title} <!-- Displays the main title of the blog post -->
      </h1>
      
      <!-- Post Excerpt (Conditional) -->
      {#if post.excerpt} <!-- Only display if an excerpt exists -->
        <p class="text-xl text-gray-600 leading-relaxed mb-6">
          {post.excerpt} <!-- Displays the short summary of the post -->
        </p>
      {/if}
      
      <!-- Post Metadata (Author, Published Date, Reading Time, Status) -->
      <div class="flex flex-wrap items-center gap-6 text-sm text-gray-500">
        <!-- Author Information -->
        <div class="flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
          <span class="font-medium text-gray-700">By {post.user.name}</span> <!-- Displays author's name -->
        </div>
        
        <!-- Published Date -->
        <div class="flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
          <time datetime={post.published_at}> <!-- HTML5 time element for semantic date -->
            {formatDate(post.published_at)} <!-- Formatted published date -->
          </time>
        </div>
        
        <!-- Reading Time -->
        <div class="flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>{post.reading_time}</span> <!-- Estimated reading time from Laravel model -->
        </div>
        
        <!-- Status Badge (Conditional - for drafts or archived posts, visible if user is authorized) -->
        {#if post.status !== 'published'}
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
            {post.status} <!-- Displays status like 'draft' or 'archived' -->
          </span>
        {/if}
      </div>
      
      <!-- Share Button -->
      <div class="mt-6">
        <button
          onclick={sharePost}
          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500 transition-colors duration-200"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
          </svg>
          Share this post
        </button>
      </div>
    </header>
    
    <!-- 
      FEATURED IMAGE (CONDITIONAL DISPLAY)
      ===================================
      
      Displays the featured image for the blog post if one exists.
      Applies responsive styling and lazy loading for performance.
     -->
    {#if post.featured_image}
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">
        <img 
          src={post.featured_image} 
          alt={`Featured image for ${post.title}`} 
          class="w-full h-auto object-cover"
          loading="lazy"
        />
      </div>
    {/if}
    
    <!-- 
      ARTICLE MAIN CONTENT - RICH TEXT DISPLAY
      =======================================
      
      This is where the main body of the blog post content is rendered.
      We use `{@html ...}` to display HTML generated by a rich text editor.
      
      🎓 LEARN:
      - **`{@html ...}`**: How to render raw HTML in Svelte (use with caution!).
      - **Tailwind Typography Plugin (`prose`)**: Automatically styles rich text content beautifully.
      
      SECURITY NOTE:
      Always ensure that any HTML rendered using `{@html}` is properly sanitized
      on the **server-side** to prevent XSS (Cross-Site Scripting) vulnerabilities.
      For this educational app, we assume content is safe as it's created by authenticated users.
     -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
      <!-- Main Content Block -->
      <div class="prose prose-lg max-w-none"> <!-- `prose` class from @tailwindcss/typography for rich text styling -->
        <div class="leading-relaxed">
          {@html post.content} <!-- Renders the HTML content of the post -->
        </div>
      </div>
      
      <!-- 
        ARTICLE FOOTER - AUTHOR INFO AND LAST UPDATED
        ===========================================
        
        Provides additional information about the author and content update times.
       -->
      <footer class="mt-12 pt-8 border-t border-gray-200">
        <!-- Author Information -->
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <!-- Author Avatar Placeholder (displays first letter of author's name) -->
            <div class="h-12 w-12 rounded-full bg-accent-500 flex items-center justify-center">
              <span class="text-white font-medium text-lg">
                {post.user.name.charAt(0).toUpperCase()}
              </span>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-lg font-medium text-gray-900">
              {post.user.name} <!-- Author's full name -->
            </p>
            <p class="text-sm text-gray-500">
              Published on {formatDate(post.published_at)} <!-- Formatted publication date -->
            </p>
          </div>
        </div>
        
        <!-- Updated Information (Conditional) -->
        {#if post.updated_at !== post.created_at} <!-- Only show if post has been updated after creation -->
          <p class="mt-4 text-sm text-gray-500">
            Last updated on {formatDate(post.updated_at)} <!-- Formatted last updated date -->
          </p>
        {/if}
      </footer>
    </div>
    
    <!-- 
      NAVIGATION ACTIONS - BOTTOM BUTTONS
      ===================================
      
      Provides navigation options at the bottom of the post for user convenience.
     -->
    <div class="mt-8 flex justify-between items-center">
      <!-- Button to View All Posts -->
      <Link 
        href="/posts" 
        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        View all posts
      </Link>
      
      {#if canEdit} <!-- Only show edit button if current user is authorized to edit this post -->
        <div class="flex space-x-3">
          <!-- Button to Edit Post -->
          <Link 
            href={`/posts/${post.id}/edit`}
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-accent-500 hover:bg-accent-600 transition-colors duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Post
          </Link>
        </div>
      {/if}
    </div>
  </article>
</div> 

<!-- 
  SITE FOOTER - CONSISTENT NAVIGATION AND LEGAL LINKS
  ===================================================
  
  The `Footer` component provides consistent navigation, legal links, and copyright information
  across the application. It's a reusable component for a professional look.
  
  🎓 LEARN: How to integrate and reuse Svelte components.
-->
<Footer {auth} />

<!--
  🎓 EDUCATIONAL SUMMARY - BLOG POST SHOW COMPONENT
  ==================================================
  
  This `BlogPosts/Show.svelte` component is an excellent example of building a detailed
  content display page in a modern web application.
  
  ✅ KEY LEARNINGS:
  - **Rich Content Display**: Safely rendering HTML content with formatting.
  - **Comprehensive SEO**: Implementing all key meta tags and structured data for visibility.
  - **Dynamic UI**: Adapting the interface based on data presence and user authorization.
  - **Interactive Features**: Adding social sharing functionality.
  - **Semantic HTML**: Using appropriate HTML5 elements for better structure and accessibility.
  - **Data Visualization**: Presenting post metadata (author, date, reading time) clearly.
  
  This component serves as a robust foundation for any content-heavy detail page
  of your web application, focusing on both rich content and technical best practices.
-->