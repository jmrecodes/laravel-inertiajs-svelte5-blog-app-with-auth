<!--
  MY POSTS PAGE - SIMPLIFIED AUTHOR DASHBOARD FOR EDUCATIONAL APP
  =================================================================
  
  This Svelte component serves as a personal dashboard for authenticated users
  to view and manage their own blog posts. It provides a clear overview of their content
  and direct links to perform common actions.
  
  🎓 BEGINNER LEARNING OBJECTIVES:
  ============================
  1. **User-Specific Data Display**: Fetching and rendering data owned by the logged-in user.
  2. **Dashboard UI Patterns**: Designing a simple, intuitive interface for content management.
  3. **Conditional Rendering**: Displaying different content based on post status or data availability (empty state).
  4. **Dynamic Styling**: Changing UI elements (like status badges) based on data values.
  5. **Navigation & Actions**: Providing clear pathways to view, edit, and create posts.
  6. **Data Formatting**: Using utility functions to present dates and text cleanly.
  
  🔍 WHAT YOU'LL LEARN:
  ====================
  - How Inertia.js passes authenticated user's posts and statistics as props.
  - Creating visual indicators (badges) for different data states.
  - Implementing a simple post list with essential information.
  - Handling pagination for user-specific content.
  - Designing a friendly "empty state" message for new users.
  - Ensuring navigation links are clear and functional.
  
  FEATURES IMPLEMENTED IN THIS COMPONENT:
  ======================================
  - Displays a list of all posts (published, drafts) by the authenticated user.
  - Summary statistics for total, published, and draft posts.
  - Quick action buttons to write new posts, view the public blog, or edit existing posts.
  - Responsive card/list layout for optimal viewing on all devices.
  - Clear visual status indicators for each post (e.g., Published, Draft).
  - Pagination for navigating through a large number of posts.
  - Informative empty state for users who haven't created any posts.
  
  This component is heavily commented to guide you through each concept
  and pattern involved in building a user-specific content management dashboard.
-->

<script>
  /*
   * IMPORTS AND DEPENDENCIES - SVELTE 5 + INERTIA.JS
   * ================================================
   * 
   * We import necessary modules for navigation and linking.
   * 🎓 LEARN: How to import and use external functionality in Svelte.
   */
  import { router } from '@inertiajs/svelte' // Inertia.js router for navigation
  import { Link } from '@inertiajs/svelte'     // Inertia.js Link component for SPA-like navigation
  
  /*
   * COMPONENT PROPS - DATA FROM LARAVEL CONTROLLER
   * ==============================================
   * 
   * These properties are passed to this Svelte component from the
   * `BlogPostController::manage()` method. Inertia.js automatically maps
   * the data returned by the controller to `$props` in Svelte.
   * 
   * 🎓 LEARN: How user-specific data flows from Laravel (backend) to Svelte (frontend).
   * 
   * - `posts`: A Laravel pagination object containing the authenticated user's blog posts.
   *   It includes `data` (the posts array), `current_page`, `last_page`,
   *   `next_page_url`, `prev_page_url`, `total`, etc.
   * - `stats`: An object containing aggregated statistics about the user's posts
   *   (e.g., `totalPosts`, `publishedPosts`, `draftPosts`, `totalViews`).
   */
  let { 
    posts,           
    stats = {}       
  } = $props()
  
  /*
   * UTILITY FUNCTIONS - DATA TRANSFORMATION AND STYLING
   * ===================================================
   * 
   * Helper functions for formatting data and applying dynamic styles based on data values.
   * 🎓 LEARN: How to create and use simple utility functions in Svelte components.
   */
  
  /**
   * FORMAT DATE FOR DISPLAY
   * -----------------------
   * Converts a date string (e.g., `created_at`, `published_at` from Laravel)
   * into a human-readable format. Useful for displaying timestamps consistently.
   */
  function formatDate(dateString) {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }
  
  /**
   * GET STATUS COLOR CLASS
   * ----------------------
   * Returns a Tailwind CSS class string based on the post's status.
   * This dynamically styles the status badges for visual clarity.
   * 
   * 🎓 LEARN: Using `switch` statements for conditional styling and mapping data to UI presentation.
   */
  function getStatusColor(status) {
    switch (status) {
      case 'published': 
        return 'bg-green-100 text-green-800 border-green-200' // Green for published posts
      case 'draft': 
        return 'bg-yellow-100 text-yellow-800 border-yellow-200' // Yellow for draft posts
      case 'archived': 
        return 'bg-gray-100 text-gray-800 border-gray-200' // Gray for archived posts
      default: 
        return 'bg-gray-100 text-gray-800 border-gray-200' // Default neutral color
    }
  }
  
  /**
   * TRUNCATE EXCERPT FOR LIST DISPLAY
   * ---------------------------------
   * Limits the length of the post content/excerpt to provide a short preview
   * in the list view, ensuring consistent layout.
   */
  function truncateExcerpt(text, maxLength = 100) {
    if (!text) return ''
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
  }
</script>

<!--
  DYNAMIC PAGE HEAD - SEO FOR AUTHOR DASHBOARD
  ===========================================
  
  `<svelte:head>` is used to set page-specific meta tags for search engines and browser tabs.
  For dashboards, we typically set `noindex, nofollow` to prevent search engines from indexing
  private user areas.
  
  🎓 LEARN: How `<svelte:head>` manages dynamic HTML `<head>` content and `robots` directives.
-->
<svelte:head>
  <title>My Posts | jmrecodes Educational Blog</title>
  <meta name="description" content="Manage your blog posts" />
  <meta name="robots" content="noindex, nofollow" /> <!-- Prevent search engines from indexing user dashboards -->
</svelte:head>

<!--
  MAIN CONTENT CONTAINER - OVERALL PAGE LAYOUT
  ===========================================
  
  This `div` defines the main structure and background for the "My Posts" page,
  applying a minimum height and background color for a consistent look.
  It uses responsive design principles with Tailwind CSS utilities.
-->
<div class="min-h-screen bg-gray-50">
  
  <!-- 
    HEADER SECTION WITH NAVIGATION AND QUICK ACTIONS
    ================================================
    
    This header provides breadcrumb navigation (showing the user's current location),
    the page title, and quick action buttons for common tasks.
   -->
  <div class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      
      <!-- Breadcrumb Navigation -->
      <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
        <!-- Link to Dashboard -->
        <Link href="/dashboard" class="hover:text-blue-700 transition-colors duration-200">
          Dashboard
        </Link>
        <!-- Separator Icon -->
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <!-- Current Page Indicator -->
        <span class="text-gray-900 font-medium">My Posts</span>
      </nav>
      
      <!-- Page Title and Description -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">My Posts</h1>
          <p class="mt-1 text-sm text-gray-600">
            Manage your blog posts and create new content
          </p>
        </div>
        
        <!-- Quick Action Buttons -->
        <div class="flex space-x-3">
          <!-- Button to Create New Post -->
          <Link 
            href="/posts/create"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-accent-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500 transition-colors duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Write New Post
          </Link>
          
          <!-- Button to View Public Blog -->
          <Link 
            href="/posts"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500 transition-colors duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            View Blog
          </Link>
        </div>
      </div>
      
      <!-- 
        STATISTICS SECTION - AUTHOR METRICS
        ===================================
        
        Displays key metrics about the user's blog posts in an easy-to-understand format.
        This provides quick insights into their content creation activity.
        
        🎓 LEARN: How to display aggregated data (stats from Laravel controller) in a visually appealing way.
       -->
      {#if stats && Object.keys(stats).length > 0} <!-- Only show statistics if data is available -->
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
          <!-- Total Posts Stat -->
          <div class="bg-gray-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-gray-900">{stats.totalPosts || 0}</div>
            <div class="text-sm text-gray-600">Total Posts</div>
          </div>
          
          <!-- Published Posts Stat -->
          <div class="bg-green-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-900">{stats.publishedPosts || 0}</div>
            <div class="text-sm text-green-600">Published</div>
          </div>
          
          <!-- Draft Posts Stat -->
          <div class="bg-yellow-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-yellow-900">{stats.draftPosts || 0}</div>
            <div class="text-sm text-yellow-600">Drafts</div>
          </div>
          
          <!-- Total Views Stat (Future Enhancement) -->
          <div class="bg-blue-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-900">{stats.totalViews || 0}</div>
            <div class="text-sm text-blue-600">Total Views</div>
          </div>
        </div>
      {/if}
    </div>
  </div>
  
  <!-- 
    POSTS LIST SECTION - USER'S BLOG POSTS
    =======================================
    
    This section displays the actual list of blog posts created by the logged-in user.
    Each post is presented in a clean, scannable format with key information and actions.
   -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {#if posts.data && posts.data.length > 0} <!-- Conditionally render if the user has posts -->
      <!-- Posts List Container -->
      <div class="space-y-4"> <!-- Vertical spacing between post items -->
        {#each posts.data as post} <!-- Loop through each post in the `posts.data` array -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200"> <!-- Stylish card for each post -->
            
            <!-- Post Header (Title, Status, Dates, Reading Time) -->
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1 min-w-0">
                <!-- Post Title (Linked if Published, otherwise just text) -->
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                  {#if post.status === 'published'} <!-- Only show link if post is published -->
                    <Link 
                      href="/posts/{post.slug}" 
                      class="hover:text-primary-700 transition-colors duration-200"
                    >
                      {post.title}
                    </Link>
                  {:else}
                    <span class="text-gray-700">{post.title}</span>
                  {/if}
                </h3>
                
                <!-- Post Status and Date Information -->
                <div class="flex items-center space-x-3 mb-2">
                  <!-- Status Badge -->
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {getStatusColor(post.status)}">
                    {post.status === 'published' ? '✓ Published' : post.status === 'draft' ? '📝 Draft' : '🗄️ Archived'}
                  </span>
                  
                  <!-- Date Info (Published or Created Date) -->
                  <span class="text-sm text-gray-500">
                    {post.status === 'published' && post.published_at 
                      ? `Published ${formatDate(post.published_at)}`
                      : `Created ${formatDate(post.created_at)}`
                    }
                  </span>
                  
                  <!-- Reading Time (Conditional) -->
                  {#if post.reading_time} <!-- Only show if reading time is available -->
                    <span class="text-sm text-gray-500">
                      {post.reading_time}
                    </span>
                  {/if}
                </div>
                
                <!-- Post Excerpt (Short Summary) -->
                {#if post.excerpt} <!-- Only show if an excerpt exists -->
                  <p class="text-gray-600 text-sm leading-relaxed">
                    {truncateExcerpt(post.excerpt, 120)}
                  </p>
                {/if}
              </div>
              
              <!-- Action Buttons (View & Edit) -->
              <div class="flex items-center space-x-2 ml-4">
                <!-- View Button (only for published posts) -->
                {#if post.status === 'published'} <!-- Only show view button if post is published -->
                  <Link 
                    href="/posts/{post.slug}"
                    class="inline-flex items-center px-3 py-2 border border-logo-navy shadow-sm text-sm font-medium rounded-md text-logo-navy bg-white hover:bg-logo-navy hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-logo-navy transition-colors duration-200"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View
                  </Link>
                {/if}
                
                <!-- Edit Button -->
                <Link 
                  href="/posts/{post.id}/edit"
                  class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-accent-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500 transition-colors duration-200"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  Edit
                </Link>
              </div>
            </div>
          </div>
        {/each}
      </div>
      
      <!-- 
        PAGINATION NAVIGATION - FOR LARGE POST LISTS
        ============================================
        
        Displays simple pagination links to navigate through the user's posts.
        Only appears if there are multiple pages of content.
        
        🎓 LEARN:
        - How Inertia.js consumes paginated data from Laravel.
        - Conditionally displaying navigation elements.
        - Simple Previous/Next page links.
       -->
      {#if posts.last_page > 1} <!-- Only show pagination if there's more than one page -->
        <div class="mt-8 flex justify-center">
          <nav class="flex items-center space-x-2">
            <!-- Previous Page Button -->
            {#if posts.current_page > 1} <!-- Only enable if not on the first page -->
              <Link
                href={posts.prev_page_url} <!-- URL to the previous page of posts -->
                class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:text-primary-700 transition-colors duration-200"
              >
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Previous
              </Link>
            {/if}
            
            <!-- Current Page Information -->
            <span class="text-sm text-gray-600 px-3 py-2">
              Page {posts.current_page} of {posts.last_page} <!-- Displays current page number and total pages -->
            </span>
            
            <!-- Next Page Button -->
            {#if posts.current_page < posts.last_page} <!-- Only enable if not on the last page -->
              <Link
                href={posts.next_page_url} <!-- URL to the next page of posts -->
                class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:text-primary-700 transition-colors duration-200"
              >
                Next
                <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </Link>
            {/if}
          </nav>
        </div>
      {/if}
    
    {:else} <!-- If the user has no posts -->
      <!-- 
        EMPTY STATE - NO POSTS MESSAGE AND CALL TO ACTION
        =================================================
        
        This section is displayed when the authenticated user has not yet created any blog posts.
        It provides a friendly message and a clear call to action to encourage content creation.
        
        🎓 LEARN: How to implement helpful empty states in your UI.
       -->
      <div class="text-center py-12">
        <div class="max-w-md mx-auto">
          <!-- Illustrative Icon -->
          <div class="mx-auto h-16 w-16 bg-accent-50 rounded-full flex items-center justify-center mb-6">
            <svg class="h-8 w-8 text-accent-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
          </div>
          
          <!-- Empty State Heading -->
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            No blog posts yet
          </h3>
          <!-- Empty State Description -->
          <p class="text-gray-600 mb-6">
            You haven't created any blog posts yet. Start writing to share your thoughts with the world!
          </p>
          
          <!-- Call to Action Button -->
          <div class="space-y-3">
            <Link 
              href="/posts/create"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-accent-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500 transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Write Your First Post
            </Link>
            
            <!-- Link for Inspiration -->
            <div class="text-sm text-gray-500">
              or <Link href="/posts" class="text-accent-600 hover:text-accent-700 underline">browse existing posts</Link> for inspiration
            </div>
          </div>
        </div>
      </div>
    {/if}
  </div>
</div>

<!--
  🎓 EDUCATIONAL SUMMARY - BLOG POSTS MANAGE COMPONENT
  =====================================================
  
  This `BlogPosts/Manage.svelte` component is a practical example of building a user-centric
  dashboard for content management in a modern web application.
  
  ✅ KEY LEARNINGS:
  - **User-Specific Data**: Displaying content tailored to the authenticated user.
  - **Dashboard Design**: Creating a functional and intuitive content overview.
  - **Dynamic UI**: Using data to control styling and display different information (e.g., status colors, dates).
  - **Effective Navigation**: Providing clear links to related actions and pages.
  - **Empty States**: Designing user-friendly messages when no data is available.
  - **Component Reusability**: Utilizing helper functions and nested components.
  
  This component serves as a robust foundation for any user dashboard or content management
  interface in your web application, focusing on both functionality and user experience.
--> 