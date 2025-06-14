<!--
  POST NOT FOUND ERROR PAGE - CUSTOM 404 FOR BLOG POSTS
  =====================================================
  
  This component displays a user-friendly error page when a blog post
  cannot be found. It provides helpful suggestions and maintains a good
  user experience even when encountering errors.
  
  EDUCATIONAL CONCEPTS:
  - Error handling in SPAs
  - User experience during errors
  - Providing helpful alternatives
  - SEO considerations for 404 pages
-->

<script>
  import { router } from '@inertiajs/svelte' // Inertia.js router for client-side navigation
  import { Link } from '@inertiajs/svelte'     // Inertia.js Link component for SPA-like navigation
  import Footer from '/resources/js/Components/Footer.svelte' // Reusable Footer component
  
  // Props passed from the Laravel exception handler
  let { 
    title = 'Blog Post Not Found',
    message = 'The blog post you are looking for does not exist.',
    suggestions = [],
    searchedSlug = null,
    auth = {}
  } = $props()
</script>

<svelte:head>
  <title>{title} | jmrecodes Educational Blog</title>
  <meta name="robots" content="noindex, follow" /> <!-- Don't index error pages -->
</svelte:head>

<div class="min-h-screen bg-gray-50 flex flex-col">
  <!-- Navigation Header -->
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <Link href="/" class="text-xl font-bold text-gray-900 hover:text-gray-700 transition-colors">
          jmrecodes Educational Blog
        </Link>
        
        <nav class="flex items-center space-x-4">
          <Link href="/posts" class="text-gray-600 hover:text-gray-900 transition-colors">
            All Posts
          </Link>
          {#if auth.user}
            <Link href="/dashboard" class="text-gray-600 hover:text-gray-900 transition-colors">
              Dashboard
            </Link>
          {:else}
            <Link href="/login" class="text-gray-600 hover:text-gray-900 transition-colors">
              Login
            </Link>
          {/if}
        </nav>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="flex-grow flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
      <div class="text-center">
        <!-- Error Icon -->
        <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-red-100 mb-8 mt-12">
          <svg class="h-12 w-12 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>

        <!-- Error Message -->
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{title}</h1>
        <p class="text-xl text-gray-600 mb-8">{message}</p>

        {#if searchedSlug}
          <p class="text-sm text-gray-500 mb-8">
            You searched for: <code class="bg-gray-100 px-2 py-1 rounded">{searchedSlug}</code>
          </p>
        {/if}

        <!-- Suggested Posts -->
        {#if suggestions.length > 0}
          <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">You might be interested in:</h2>
            <div class="space-y-4">
              {#each suggestions as post}
                <Link 
                  href="/posts/{post.slug}"
                  class="block bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow text-left"
                >
                  <h3 class="font-semibold text-gray-900 mb-2">{post.title}</h3>
                  {#if post.excerpt}
                    <p class="text-gray-600 text-sm line-clamp-2">{post.excerpt}</p>
                  {/if}
                </Link>
              {/each}
            </div>
          </div>
        {/if}

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Link 
            href="/posts" 
            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors"
          >
            Browse All Posts
          </Link>
          
          <Link 
            href="/" 
            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors"
          >
            Go to Homepage
          </Link>
        </div>

        <!-- Search Tip -->
        <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-4 mb-12">
          <p class="text-sm text-blue-800">
            <strong>Tip:</strong> Try using the search feature on our 
            <Link href="/posts" class="underline hover:no-underline">posts page</Link> 
            to find the content you're looking for.
          </p>
        </div>
      </div>
    </div>
  </main>

  <Footer {auth} />
</div>

<style>
  /* Tailwind's line-clamp utility */
  .line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    line-clamp: 2;
  }
</style> 