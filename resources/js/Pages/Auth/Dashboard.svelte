<!--
  EDUCATIONAL AUTHENTICATION - USER DASHBOARD COMPONENT
  ====================================================
  
  A streamlined dashboard for authenticated users focusing on essential blog management
  features and clear navigation patterns.
  
  EDUCATIONAL CONCEPTS COVERED:
  ============================
  1. AUTHENTICATED LAYOUTS: Clean UI patterns for logged-in users
  2. USER DATA DISPLAY: Essential metrics and personalized content
  3. LOGOUT HANDLING: Secure session termination patterns
  4. STREAMLINED NAVIGATION: Focused user experience
  5. BRAND CONSISTENCY: Using our navy/blue/accent color scheme
-->

<script>
  /*
   * IMPORTS AND DEPENDENCIES
   * ========================
   */
  import { router } from '@inertiajs/svelte' // Inertia.js router for client-side navigation
  import { Link } from '@inertiajs/svelte'     // Inertia.js Link component for SPA-like navigation
  import Footer from '/resources/js/Components/Footer.svelte' // Reusable Footer component
  
  /*
   * COMPONENT PROPS - AUTHENTICATED USER DATA
   * ========================================
   */
  let { 
    user,                    // Current authenticated user object
    stats = {},              // User statistics (posts, views, etc.)
    recentPosts = [],        // Recent blog posts by the user
    flashMessage = null,     // Success/welcome message from login
    auth = {}                // auth object from Laravel
  } = $props()
  
  /*
   * COMPONENT STATE - UI INTERACTIONS
   * ================================
   */
  let isLoggingOut = $state(false)
  let showUserMenu = $state(false)
  let currentTime = $state(new Date())
  
  /*
   * COMPUTED VALUES - DERIVED DATA
   * =============================
   */
  let greeting = $derived(
    currentTime.getHours() < 12 ? 'Good morning' :
    currentTime.getHours() < 17 ? 'Good afternoon' : 
    'Good evening'
  )
  
  let memberSince = $derived(
    !user?.created_at ? '' : 
    new Date(user.created_at).toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'long' 
    })
  )
  
  /*
   * LIFECYCLE - UPDATE CURRENT TIME
   * ===============================
   */
  let timeInterval = setInterval(() => {
    currentTime = new Date()
  }, 60000)
  
  $effect(() => {
    return () => {
      if (timeInterval) {
        clearInterval(timeInterval)
      }
    }
  })
  
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
  
  /*
   * UI HELPER FUNCTIONS
   * ==================
   */
  function toggleUserMenu() {
    showUserMenu = !showUserMenu
  }
  
  function closeUserMenu() {
    setTimeout(() => {
      showUserMenu = false
    }, 200)
  }
</script>

<!--
  PAGE HEAD - PERSONALIZED META TAGS
  =================================
-->
<svelte:head>
  <title>Dashboard | jmrecodes Educational Blog</title>
  <meta name="description" content="Manage your blog posts and account settings in your personal dashboard." />
  <meta name="robots" content="noindex" />
  <meta name="favicon" content="/favicon.ico" />
</svelte:head>

<!--
  MAIN DASHBOARD LAYOUT
  =====================
-->
<div class="min-h-screen bg-gray-50">
  
  <!--
    DASHBOARD HEADER - STREAMLINED NAVIGATION
    ========================================
  -->
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        
        <!-- Logo/Brand -->
        <div class="flex items-center">
          <Link href="/" class="flex items-center space-x-2 text-xl font-bold text-gray-900 hover:text-primary-600 transition-colors">
            <img src="/logo.jpg" alt="Educational Blog" class="h-8 w-8">
            <span class="text-lg font-bold text-gray-900">
              jmrecodes Educational Blog
            </span>
          </Link>
        </div>
        
        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-4">
          <Link 
            href="/dashboard" 
            class="text-primary-600 px-3 py-2 rounded-md text-sm font-medium bg-primary-50"
          >
            Dashboard
          </Link>
          <Link 
            href="/posts" 
            class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
          >
            View Blog
          </Link>
          <Link 
            href="/manage-posts" 
            class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
          >
            My Posts
          </Link>
          <Link 
            href="/posts/create"
            class="bg-cyan-500 text-white hover:bg-cyan-600 px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 shadow-sm hover:shadow-md"
          >
            New Post
          </Link>
        </nav>
        
        <!-- User Menu -->
        <div class="relative">
          <button
            onclick={toggleUserMenu}
            onblur={closeUserMenu}
            class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2 rounded-md p-2"
          >
            <!-- User Avatar -->
            <div class="h-8 w-8 bg-primary-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
              {user?.name?.charAt(0).toUpperCase() || 'U'}
            </div>
            <span class="hidden sm:block text-sm font-medium">{user?.name || 'User'}</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          
          <!-- User Dropdown Menu -->
          {#if showUserMenu}
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50">
              <div class="py-1">
                <div class="px-4 py-2 border-b border-gray-100">
                  <p class="text-sm font-medium text-gray-900">{user?.name}</p>
                  <p class="text-xs text-gray-500">{user?.email}</p>
                </div>
                
                <Link 
                  href="/profile" 
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                >
                  Profile Settings
                </Link>
                
                <Link 
                  href="/manage-posts" 
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                >
                  Manage Posts
                </Link>
                
                <Link 
                  href="/posts" 
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                >
                  View Blog
                </Link>
                
                <hr class="my-1">
                
                <button
                  onclick={handleLogout}
                  disabled={isLoggingOut}
                  class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors disabled:opacity-50"
                >
                  {#if isLoggingOut}
                    Logging out...
                  {:else}
                    Logout
                  {/if}
                </button>
              </div>
            </div>
          {/if}
        </div>
      </div>
    </div>
  </header>
  
  <!--
    MAIN DASHBOARD CONTENT
    =====================
  -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Welcome Message -->
    {#if flashMessage}
      <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
        <div class="flex">
          <svg class="h-5 w-5 text-green-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <p class="text-sm text-green-700">{flashMessage}</p>
        </div>
      </div>
    {/if}
    
    <!-- Dashboard Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 leading-tight">
        {greeting}, {user?.name || 'User'}! 👋
      </h1>
      <p class="mt-2 text-gray-600 leading-normal">
        Welcome to your dashboard. Manage your blog and track your progress.
      </p>
    </div>
    
    <!--
      STATISTICS CARDS - SIMPLIFIED METRICS
      =====================================
    -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      
      <!-- Total Posts -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Posts</p>
            <p class="text-2xl font-bold text-gray-900">{stats?.totalPosts || 0}</p>
          </div>
        </div>
      </div>
      
      <!-- Published Posts -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Published</p>
            <p class="text-2xl font-bold text-gray-900">{stats?.publishedPosts || 0}</p>
          </div>
        </div>
      </div>
      
      <!-- Draft Posts -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-accent-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Drafts</p>
            <p class="text-2xl font-bold text-gray-900">{stats?.draftPosts || 0}</p>
          </div>
        </div>
      </div>
      
      <!-- Total Views -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
                         <svg class="h-8 w-8 text-accent-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Views</p>
            <p class="text-2xl font-bold text-gray-900">{stats?.totalViews || 0}</p>
          </div>
        </div>
      </div>
    </div>
    
    <!--
      STREAMLINED CONTENT GRID
      =========================
    -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!--
        QUICK ACTIONS PANEL - FOCUSED
        =============================
      -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
          
          <div class="space-y-3">
            <Link
              href="/posts/create"
              class="w-full flex items-center justify-center px-4 py-3 bg-cyan-500 text-white rounded-md hover:bg-cyan-600 transition-colors duration-200"
            >
              <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Create New Post
            </Link>
            
            <Link
              href="/manage-posts"
              class="w-full flex items-center justify-center px-4 py-3 bg-primary-50 text-primary-700 border border-primary-200 rounded-md hover:bg-primary-100 transition-colors duration-200"
            >
              <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Manage Posts
            </Link>
            
            <Link
              href="/posts"
              class="w-full flex items-center justify-center px-4 py-3 bg-accent-50 text-accent-700 border border-accent-200 rounded-md hover:bg-accent-100 transition-colors duration-200"
            >
              <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              View Blog
            </Link>
            
            <Link
              href="/profile"
              class="w-full flex items-center justify-center px-4 py-3 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-200"
            >
              <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Account Settings
            </Link>
          </div>
          
          <!-- User Info Card -->
          <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Account Info</h3>
            <div class="space-y-2 text-sm text-gray-600">
              <p><span class="font-medium">Email:</span> {user?.email}</p>
              <p><span class="font-medium">Member since:</span> {memberSince}</p>
              <p><span class="font-medium">Role:</span> {user?.role || 'Author'}</p>
            </div>
          </div>
        </div>
      </div>
      
      <!--
        RECENT POSTS - STREAMLINED
        ==========================
      -->
      <div class="lg:col-span-2">        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Recent Posts</h2>
            <Link 
              href="/manage-posts" 
              class="text-sm text-primary-600 hover:text-primary-500 font-medium"
            >
              View all
            </Link>
          </div>
          
          {#if recentPosts.length > 0}
            <div class="space-y-4">
              {#each recentPosts as post}
                <div class="flex items-start space-x-3 p-3 border border-gray-100 rounded-md hover:bg-gray-50 transition-colors">
                  <div class="flex-shrink-0">
                    <div class="h-2 w-2 bg-primary-600 rounded-full mt-2"></div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                      {post.title}
                    </p>
                    <p class="text-xs text-gray-500">
                      {post.status} • {post.created_at}
                    </p>
                  </div>
                  <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                 {post.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-accent-100 text-accent-800'}">
                      {post.status}
                    </span>
                  </div>
                </div>
              {/each}
            </div>
          {:else}
            <div class="text-center py-8">
              <svg class="h-12 w-12 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              <h3 class="text-lg font-medium text-gray-900 mb-2">No posts yet</h3>
              <p class="text-gray-500 text-sm mb-4">Start creating content to share your ideas with the world.</p>
              <Link
                href="/posts/create"
                class="inline-flex items-center px-4 py-2 bg-cyan-500 text-white rounded-md hover:bg-cyan-600 transition-colors duration-200"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create your first post
              </Link>
            </div>
          {/if}
        </div>
      </div>
    </div>
  </main>
</div>

<Footer {auth} />

<!--
  EDUCATIONAL SUMMARY - STREAMLINED DASHBOARD
  ===========================================
  
  This simplified Dashboard component demonstrates:
  
  ✅ CLEAN AUTHENTICATED INTERFACE:
  - Personalized greeting with time-based logic
  - Essential user statistics and metrics
  - Streamlined navigation with blog viewing
  - Secure logout functionality
  
  ✅ BRAND-CONSISTENT DESIGN:
  - Navy/blue/accent color scheme
  - Consistent visual hierarchy
  - Modern card-based layout
  - Responsive grid system
  
  ✅ FOCUSED USER EXPERIENCE:
  - Clear quick actions for common tasks
  - Recent posts overview
  - Essential account information
  - Removed distracting activity feed
  
  ✅ ENHANCED NAVIGATION:
  - Direct "View Blog" access in header and actions
  - Clear current page indication
  - Logical flow between dashboard and blog sections
  
  This streamlined version focuses on what users need most:
  creating, managing, and viewing their blog content.
--> 