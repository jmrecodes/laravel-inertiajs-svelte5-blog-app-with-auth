<!--
  🎓 EDUCATIONAL 404 ERROR PAGE - HELPFUL USER EXPERIENCE
  =======================================================
  
  This component demonstrates a user-friendly 404 error page that helps users
  navigate when they encounter a missing page. It showcases error handling,
  navigation patterns, and positive user experience design.
  
  🎓 BEGINNER LEARNING OBJECTIVES:
  ================================
  1. **Error Handling UX**: Turning frustrating errors into helpful experiences
  2. **Navigation Recovery**: Providing clear paths back to useful content
  3. **Search Integration**: Offering users ways to find what they're looking for
  4. **Responsive Design**: Working beautifully on all device sizes
  5. **Accessibility**: Ensuring error pages are accessible to all users
  6. **SEO Considerations**: Proper meta tags and status codes for search engines
  
  🔍 WHAT YOU'LL LEARN:
  =====================
  - How to create helpful error pages that guide users instead of frustrating them
  - Using Svelte 5 reactivity for interactive search and navigation
  - Implementing proper semantic HTML for error states
  - Building trust through professional error handling
  - Progressive enhancement for form functionality
-->

<script>
  /*
   * IMPORTS AND DEPENDENCIES - SVELTE 5 + INERTIA.JS
   * ================================================
   */
  import { router } from '@inertiajs/svelte' // Inertia.js router for client-side navigation
  import { Link } from '@inertiajs/svelte'     // Inertia.js Link component for SPA-like navigation
  import Footer from '/resources/js/Components/Footer.svelte' // Reusable Footer component
  
  /*
   * COMPONENT PROPS - ERROR CONTEXT DATA
   * ===================================
   * 
   * These props might be passed from Laravel's error handler
   * to provide context about the missing resource or suggest alternatives.
   */
  let { 
    requestedUrl = '',
    suggestions = [],
    auth = {},
    recentPosts = []
  } = $props()
  
  /*
   * COMPONENT STATE - SEARCH AND NAVIGATION
   * =======================================
   */
  let searchQuery = $state('')
  let isSearching = $state(false)
  
  /*
   * COMPUTED VALUES
   * ==============
   */
  let hasSearchQuery = $derived(searchQuery.trim().length > 0)
  
  /*
   * SEARCH HANDLER
   * =============
   */
  function handleSearch(event) {
    event.preventDefault()
    
    if (!hasSearchQuery) return
    
    isSearching = true
    
    // Navigate to blog posts with search query
    router.get('/posts', { search: searchQuery.trim() }, {
      onFinish: () => {
        isSearching = false
      }
    })
  }
  
  /*
   * QUICK ACTIONS
   * ============
   */
  function goHome() {
    router.get('/')
  }
  
  function goBrowsePosts() {
    router.get('/posts')
  }
  
  /*
   * REPORT BROKEN LINK (placeholder for future functionality)
   * ========================================================
   */
  function reportBrokenLink() {
    // In a real app, this could send feedback to developers
    alert('Thank you for reporting this! We\'ll investigate the broken link.')
  }
</script>

<!--
  DYNAMIC HEAD CONTENT - SEO AND ACCESSIBILITY
  ===========================================
-->
<svelte:head>
  <title>Page Not Found (404) | jmrecodes Educational Blog</title>
  <meta name="description" content="The page you're looking for doesn't exist. Find what you need with our helpful navigation and search." />
  <meta name="robots" content="noindex" />
</svelte:head>

<!--
  MAIN 404 PAGE LAYOUT
  ===================
-->
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-100 flex flex-col">
  
  <!-- Main Content -->
  <div class="flex-1 flex items-center justify-center px-4 py-16">
    <div class="max-w-4xl mx-auto text-center">
      
      <!-- 404 Icon and Number -->
      <div class="mb-8">
        <!-- Decorative Background -->
        <div class="relative">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-400/20 to-purple-400/20 rounded-full blur-3xl"></div>
          
          <!-- 404 Number Display -->
          <div class="relative bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-2xl border border-gray-200/50">
            <div class="text-8xl md:text-9xl font-bold bg-gradient-to-r from-gray-900 via-blue-800 to-purple-800 bg-clip-text text-transparent mb-4">
              404
            </div>
            <div class="w-16 h-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full mx-auto"></div>
          </div>
        </div>
      </div>
      
      <!-- Error Message -->
      <div class="mb-12">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Oops! Page Not Found
        </h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
          The page you're looking for doesn't exist or may have been moved. 
          Don't worry though – let's help you find what you need!
        </p>
        
        {#if requestedUrl}
          <div class="mt-4 text-sm text-gray-500">
            <span class="font-medium">Requested URL:</span> 
            <code class="bg-gray-100 px-2 py-1 rounded text-gray-700">{requestedUrl}</code>
          </div>
        {/if}
      </div>
      
      <!-- Search Box -->
      <div class="mb-12">
        <form onsubmit={handleSearch} class="max-w-md mx-auto">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input
              type="text"
              bind:value={searchQuery}
              placeholder="Search blog posts..."
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white/80 backdrop-blur-sm"
              disabled={isSearching}
            />
            <button
              type="submit"
              disabled={!hasSearchQuery || isSearching}
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
            >
              {#if isSearching}
                <div class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
              {:else}
                <span class="text-blue-600 hover:text-blue-700 font-medium text-sm">Search</span>
              {/if}
            </button>
          </div>
        </form>
      </div>
      
      <!-- Quick Action Buttons -->
      <div class="mb-12">
        <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-lg mx-auto">
          <!-- Go Home Button -->
          <button
            onclick={goHome}
            class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Go Home
          </button>
          
          <!-- Browse Posts Button -->
          <button
            onclick={goBrowsePosts}
            class="flex-1 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 font-semibold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
            </svg>
            Browse Posts
          </button>
        </div>
      </div>
      
      <!-- Helpful Links Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
        
        <!-- Popular Pages -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-200/50">
          <div class="text-blue-600 mb-3">
            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
          </div>
          <h3 class="font-semibold text-gray-900 mb-3">Popular Pages</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <Link href="/" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                🏠 Home
              </Link>
            </li>
            <li>
              <Link href="/posts" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                📝 All Blog Posts
              </Link>
            </li>
            {#if auth.user}
              <li>
                <Link href="/dashboard" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                  📊 Dashboard
                </Link>
              </li>
              <li>
                <Link href="/posts/create" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                  ✍️ Create Post
                </Link>
              </li>
            {:else}
              <li>
                <Link href="/login" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                  🔐 Login
                </Link>
              </li>
              <!-- Register link is only shown in development mode -->
              {#if import.meta.env.DEV || import.meta.env.MODE === 'development'}
              <li>
                <Link href="/register" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                  📝 Register
                </Link>
              </li>
              {/if}
            {/if}
          </ul>
        </div>
        
        <!-- Help & Support -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-200/50">
          <div class="text-green-600 mb-3">
            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="font-semibold text-gray-900 mb-3">Need Help?</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <Link href="/terms" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                📋 Terms of Service
              </Link>
            </li>
            <li>
              <Link href="/privacy" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                🔒 Privacy Policy
              </Link>
            </li>
          </ul>
        </div>
        
        <!-- Recent Posts (if available) -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-200/50">
          <div class="text-purple-600 mb-3">
            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="font-semibold text-gray-900 mb-3">Latest Content</h3>
          {#if recentPosts.length > 0}
            <ul class="space-y-2 text-sm">
              {#each recentPosts.slice(0, 3) as post}
                <li>
                  <Link href="/posts/{post.slug}" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors line-clamp-1">
                    📄 {post.title}
                  </Link>
                </li>
              {/each}
            </ul>
          {:else}
            <p class="text-sm text-gray-500">
              No recent posts available.
            </p>
            <Link href="/posts" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors text-sm">
              Browse all posts →
            </Link>
          {/if}
        </div>
        
      </div>
      
      <!-- Footer Message -->
      <div class="mt-12 text-sm text-gray-500">
        <p>
          If you think this is a mistake, please 
          <button onclick={reportBrokenLink} class="text-blue-600 hover:text-blue-700 underline">
            let us know
          </button>
          and we'll fix it right away!
        </p>
      </div>
      
    </div>
  </div>
  
  <!-- Footer -->
  <Footer {auth} />
  
</div>
