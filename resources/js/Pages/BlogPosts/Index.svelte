<!--
  BLOG POSTS INDEX PAGE - PUBLIC BLOG LISTING FOR EDUCATIONAL APP
  ===============================================================
  
  This Svelte component is the public face of our blog. It displays all **published** blog posts
  to visitors, demonstrating how to build a dynamic, interactive listing page.
  
  🎓 BEGINNER LEARNING OBJECTIVES:
  ============================
  1. **Dynamic Data Display**: Fetching and rendering lists of items from a backend.
  2. **Pagination**: Efficiently displaying large datasets in manageable chunks.
  3. **Search Functionality**: Implementing real-time search with URL synchronization.
  4. **Responsive UI**: Designing a layout that adapts gracefully to different screen sizes.
  5. **SEO & Metadata**: Setting dynamic titles and descriptions for search engines.
  6. **Component Reusability**: Using imported components like `Footer.svelte`.
  
  🔍 WHAT YOU'LL LEARN:
  ====================
  - How Inertia.js passes paginated data and query parameters to Svelte components.
  - Implementing a debounced search input to optimize network requests.
  - Dynamically generating pagination links with `Link` components.
  - Displaying different UI states (e.g., empty search results).
  - Formatting dates and truncating text for better presentation.
  - Ensuring navigation remains smooth with `preserveState` and `preserveScroll`.
  
  FEATURES IMPLEMENTED IN THIS COMPONENT:
  ======================================
  - Paginated display of blog posts (10 posts per page).
  - Real-time search by title, content, or excerpt.
  - URL query parameter synchronization for search and pagination.
  - Responsive card-based layout for individual blog posts.
  - Estimated reading time display for each post.
  - Author information and publication dates.
  - Optimized SEO with dynamic `<svelte:head>` meta tags.
  - Clear empty states for when no posts are found.
  
  This component is heavily commented to guide you through each concept
  and pattern involved in building a public blog listing.
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
  import Footer from '/resources/js/Components/Footer.svelte' // Reusable Footer component
  
  /*
   * COMPONENT PROPS - DATA FROM LARAVEL CONTROLLER
   * ==============================================
   * 
   * These properties are passed to this Svelte component from the
   * `BlogPostController::index()` method. Inertia.js automatically maps
   * the data returned by the controller to `$props` in Svelte.
   * 
   * 🎓 LEARN: How data flows from Laravel (backend) to Svelte (frontend).
   * 
   * - `posts`: A Laravel pagination object containing the blog post data.
   *   It includes `data` (the posts array), `current_page`, `last_page`,
   *   `next_page_url`, `prev_page_url`, `total`, etc.
   * - `search`: The current search query string, passed from the URL.
   * - `meta`: An object containing SEO-related metadata (title, description).
   * - `auth`: Global authentication data (available on all Inertia pages via middleware).
   * - `flash`: One-time flash messages (e.g., success messages).
   */
  let { 
    posts,      
    search = '', 
    meta,       
    auth = {},  
    flash = {}  
  } = $props()
  
  /*
   * REACTIVE STATE - SEARCH FUNCTIONALITY ($STATE)
   * =============================================
   * 
   * `$state` creates reactive variables that trigger UI updates when they change.
   * 🎓 LEARN: How to manage local component state in Svelte 5.
   */
  let searchQuery = $state(search) // Binds to the search input field
  let searchTimeout = null           // Used for debouncing the search input
  
  /*
   * SEARCH HANDLER - DEBOUNCED LIVE SEARCH
   * ======================================
   * 
   * This function handles input changes in the search bar. It implements **debouncing**
   * to prevent sending a network request on every single keystroke. This improves
   * performance by reducing server load.
   * 
   * 🎓 LEARN:
   * - **Debouncing**: Waiting for a pause in user input before triggering an action.
   * - **Inertia.js `router.get()`**: Making an Inertia visit to update the URL and data.
   * - **`preserveState` & `preserveScroll`**: Maintaining scroll position and form state.
   * - **URL Query Parameters**: How to update the URL with search terms.
   */
  function handleSearch(event) {
    searchQuery = event.target.value // Update the reactive search query state
    
    // Clear any previous debounce timeout to reset the timer
    if (searchTimeout) {
      clearTimeout(searchTimeout)
    }
    
    // Set a new timeout: send request after 300ms of no further typing
    searchTimeout = setTimeout(() => {
      router.get('/posts', 
        searchQuery ? { search: searchQuery } : {}, // Send search term if not empty, otherwise empty object
        { 
          preserveState: true,   // Keep the current component instance and state (e.g., form input)
          preserveScroll: true // Maintain the user's scroll position after the update
        }
      )
    }, 300) // 300 milliseconds debounce time
  }
  
  /*
   * UTILITY FUNCTIONS - DATA TRANSFORMATION
   * =======================================
   * 
   * Helper functions to format data for display in the UI.
   * 🎓 LEARN: How to create and use simple utility functions in Svelte.
   */
  
  /**
   * FORMAT DATE FOR DISPLAY
   * -----------------------
   * Converts a date string (e.g., from Laravel) into a human-readable format.
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
   * TRUNCATE EXCERPT FOR CARD DISPLAY
   * ---------------------------------
   * Limits the length of the post excerpt to fit neatly into cards.
   */
  function truncateExcerpt(excerpt, maxLength = 150) {
    if (!excerpt) return ''
    return excerpt.length > maxLength 
      ? excerpt.substring(0, maxLength) + '...'
      : excerpt
  }
</script>

<!--
  DYNAMIC PAGE HEAD - SEO OPTIMIZATION FOR BLOG LISTING
  =====================================================
  
  `<svelte:head>` is used to set page-specific meta tags for SEO.
  This helps search engines understand and rank your content.
  
  🎓 LEARN:
  - **<title>**: The main title displayed in the browser tab and search results.
  - **<meta name="description">**: A short summary for search engine results.
  - **<meta name="robots">**: Tells search engines whether to index and follow links.
  - **Open Graph (og:...)**: Metadata for social media sharing (e.g., Facebook, LinkedIn).
  - **Twitter Cards (twitter:...)**: Metadata for Twitter previews.
-->
<svelte:head>
  <title>{meta.title}</title> <!-- Dynamic page title from Laravel -->
  <meta name="description" content={meta.description} /> <!-- Dynamic meta description -->
  <meta name="robots" content="index, follow" /> <!-- Allow search engines to index and follow links -->
  
  <!-- Open Graph / Social Media Sharing Tags -->
  <meta property="og:type" content="website" />
  <meta property="og:title" content={meta.title} />
  <meta property="og:description" content={meta.description} />
  
  <!-- Twitter Card Tags -->
  <meta name="twitter:card" content="summary" />
  <meta name="twitter:title" content={meta.title} />
  <meta name="twitter:description" content={meta.description} />
</svelte:head>

<!--
  MAIN CONTENT CONTAINER - OVERALL PAGE LAYOUT
  ===========================================
  
  This `div` defines the main structure and background for the entire blog listing page.
  It follows responsive design principles with Tailwind CSS utilities.
-->
<div class="min-h-screen bg-gray-50">
  <!-- 
    NAVIGATION HEADER (CONDITIONAL FOR AUTHENTICATED USERS)
    =======================================================
    
    This header provides quick links for logged-in users to navigate to their dashboard
    and post management pages. It is only shown if `auth?.user` exists (i.e., user is logged in).
    
    🎓 LEARN: How to conditionally render UI elements based on authentication status.
   -->
  {#if auth?.user}
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <!-- Link to Dashboard -->
            <Link href="/dashboard" class="text-sm text-gray-600 hover:text-blue-700 transition-colors duration-200">
              ← Dashboard
            </Link>
            <span class="text-gray-400">|</span>
            <!-- Link to My Posts (Manage BlogPosts) -->
            <Link href="/manage-posts" class="text-sm text-gray-600 hover:text-blue-700 transition-colors duration-200">
              My Posts
            </Link>
          </div>
          <div class="flex items-center space-x-3">
            <span class="text-sm text-gray-600">Welcome, {auth?.user?.name || 'User'}</span>
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
  {/if}

  <!-- 
    HERO SECTION WITH BLOG TITLE AND SEARCH BAR
    ===========================================
    
    This section provides the main title of the blog and the search functionality.
    It uses consistent styling and centers content for a clean look.
   -->
  <div class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Blog Header Text -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
          jmrecodes Educational Blog
        </h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
          Discover tutorials and insights about modern web development.
          Learn Svelte 5, Laravel, Inertia.js, and more.
        </p>
      </div>
      
      <!-- Search Bar Input -->
      <div class="max-w-md mx-auto">
        <label for="search" class="sr-only">Search blog posts</label> <!-- sr-only hides label visually but keeps for screen readers -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <!-- Search Icon SVG -->
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <input
            type="text"
            id="search"
            bind:value={searchQuery}
            oninput={handleSearch}
            placeholder="Search posts..."
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg
                   focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                   placeholder-gray-500 text-gray-900 sm:text-sm"
          />
        </div>
        
        <!-- Search Results Information (Conditional Display) -->
        {#if search} <!-- Only show if there's an active search query -->
          <p class="mt-2 text-sm text-gray-600 text-center">
            {posts.total} {posts.total === 1 ? 'result' : 'results'} for "{search}"
            <button 
              type="button"
              onclick={() => {
                searchQuery = '' // Clear client-side search input
                router.get('/posts') // Perform a new Inertia visit without search parameter
              }}
              class="ml-2 text-cyan-600 hover:text-cyan-700 underline"
            >
              Clear search
            </button>
          </p>
        {/if}
      </div>
    </div>
  </div>
  
  <!-- 
    BLOG POSTS LISTING AREA
    =======================
    
    This section displays the actual list of blog posts, either in a grid layout
    or an empty state message if no posts are found.
   -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {#if posts.data.length > 0} <!-- Conditionally render if there are posts -->
      <!-- Posts Grid Layout -->
      <div class="grid gap-6 md:gap-8"> <!-- Tailwind CSS grid for responsive layout -->
        {#each posts.data as post} <!-- Loop through each post in the `posts.data` array -->
          <article class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200"> <!-- Stylish card for each post -->
            <!-- Post Content - ENTIRE CARD IS CLICKABLE -->
            <Link href="/posts/{post.slug}" class="block p-6 hover:bg-gray-50 transition-colors duration-200"> <!-- Make the whole card a clickable link to the post -->
              <!-- Post Header (Title and Meta) -->
              <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                  <h2 class="text-xl font-semibold text-gray-900 mb-2 hover:text-blue-700 transition-colors duration-200">
                    {post.title} <!-- Blog Post Title -->
                  </h2>
                  
                  <!-- Post Meta Information (Author, Date, Reading Time) -->
                  <div class="flex items-center text-sm text-gray-500 space-x-4">
                    <!-- Author Name -->
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                      {post.user.name}
                    </span>
                    
                    <!-- Published Date -->
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                      {formatDate(post.published_at)}
                    </span>
                    
                    <!-- Reading Time -->
                      <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {post.reading_time} <!-- Estimated reading time, computed in Laravel model -->
                      </span>
                  </div>
                </div>
                
                <!-- Status Badge (Only for non-published posts, e.g., drafts, for admin view or debugging) -->
                {#if post.status !== 'published'}
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    {post.status} <!-- Displays post status like 'draft' or 'archived' -->
                  </span>
                {/if}
              </div>
              
              <!-- Post Excerpt (Short Summary) -->
              {#if post.excerpt}
                <p class="text-gray-600 leading-relaxed mb-4">
                  {truncateExcerpt(post.excerpt)} <!-- Displays a truncated version of the excerpt -->
                </p>
              {/if}
              
              <!-- Read More Link/Indicator -->
              <div class="flex justify-end">
                <span class="inline-flex items-center text-cyan-600 font-medium text-sm">
                  Read more
                  <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </span>
              </div>
            </Link>
          </article>
        {/each}
      </div>
      
      <!-- 
        PAGINATION NAVIGATION
        =====================
        
        Displays pagination links generated by Laravel. This allows users to navigate
        between pages of blog posts without full page reloads.
        
        🎓 LEARN:
        - How Inertia.js handles paginated data (Laravel's `paginate()` method).
        - Conditionally showing/disabling Previous/Next buttons.
        - Dynamically generating page number links.
        - Preserving search queries across pagination clicks.
       -->
      {#if posts.last_page > 1} <!-- Only show pagination if there's more than one page -->
        <div class="mt-8 flex justify-center">
          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <!-- Previous Page Button -->
            {#if posts.current_page > 1} <!-- Enable if not on the first page -->
              <Link
                href={posts.prev_page_url}
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 hover:text-blue-700 transition-colors duration-200"
                aria-label="Previous page"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
              </Link>
            {:else} <!-- Disabled previous button for the first page -->
              <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
              </span>
            {/if}
            
            <!-- Page Numbers Loop -->
            {#each Array(posts.last_page) as _, i} <!-- Loop from 0 to last_page-1 -->
              {#if i + 1 === posts.current_page} <!-- Current page styling -->
                <span class="relative inline-flex items-center px-4 py-2 border border-cyan-500 bg-cyan-500 text-sm font-medium text-white">
                  {i + 1} <!-- Display current page number -->
                </span>
              {:else if Math.abs(i + 1 - posts.current_page) <= 2 || i === 0 || i === posts.last_page - 1} <!-- Show nearby pages and first/last -->
                <Link
                  href="/posts?page={i + 1}{search ? '&search=' + encodeURIComponent(search) : ''}"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-700 transition-colors duration-200"
                >
                  {i + 1} <!-- Display page number -->
                </Link>
              {:else if Math.abs(i + 1 - posts.current_page) === 3} <!-- Display ellipsis for skipped pages -->
                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-400">
                  ... <!-- Ellipsis indicating skipped pages -->
                </span>
              {/if}
            {/each}
            
            <!-- Next Page Button -->
            {#if posts.current_page < posts.last_page} <!-- Enable if not on the last page -->
              <Link
                href={posts.next_page_url}
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 hover:text-blue-700 transition-colors duration-200"
                aria-label="Next page"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </Link>
            {:else} <!-- Disabled next button for the last page -->
              <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </span>
            {/if}
          </nav>
        </div>
      {/if}
    {:else} <!-- If no posts are found (either initially or after search) -->
      <!-- 
        EMPTY STATE MESSAGE - USER FEEDBACK
        ===================================
        
        Displays a friendly message when there are no blog posts to show,
        adapting the message based on whether a search was performed.
       -->
      <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">
          {search ? 'No posts found' : 'No blog posts yet'} <!-- Dynamic heading based on search -->
        </h3>
        <p class="mt-1 text-sm text-gray-500">
          {search 
            ? `No posts match your search for "${search}". Try a different search term.` // Message for no search results
            : 'Check back soon for new content!' // Message for empty blog
          }
        </p>
        
        {#if search} <!-- Show 'Clear search' button only if a search was active -->
          <div class="mt-6">
            <button
              type="button"
              onclick={() => {
                searchQuery = '' // Clear the search input
                router.get('/posts') // Reload posts without any search query
              }}
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-cyan-500 hover:bg-cyan-600 transition-colors duration-200"
            >
              Clear search
            </button>
          </div>
        {/if}
      </div>
    {/if}
  </div>
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
  🎓 EDUCATIONAL SUMMARY - BLOG POSTS INDEX COMPONENT
  ==================================================
  
  This `BlogPosts/Index.svelte` component is a strong example of building a public-facing
  content listing page in a modern web application.
  
  ✅ KEY LEARNINGS:
  - **Efficient Data Display**: Mastering pagination for performance.
  - **Interactive Search**: Implementing debounced live search for better UX.
  - **Inertia.js Dynamics**: How Inertia.js updates the UI efficiently with server-side data.
  - **Responsive Design**: Building layouts that look great on all devices.
  - **SEO Fundamentals**: Dynamically setting `<head>` tags for search engines.
  - **User Feedback**: Providing clear messages for empty states and search results.
  
  This component serves as a robust foundation for any content-heavy section
  of your web application, focusing on both functionality and user experience.
-->