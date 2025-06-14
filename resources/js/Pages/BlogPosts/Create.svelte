<!--
  BLOG POST CREATE PAGE - NEW POST CREATION FORM FOR EDUCATIONAL APP
  ===================================================================
  
  This Svelte component provides a comprehensive form for authenticated users
  to create new blog posts. It showcases advanced form handling techniques,
  real-time validation, and dynamic content generation.
  
  🎓 BEGINNER LEARNING OBJECTIVES:
  ============================
  1. **Complex Form Management**: Handling multiple input types and associated data.
  2. **Reactive Forms (Svelte 5)**: Using `$state` for form data and `$derived` for dynamic validation.
  3. **Inertia.js Form Submission**: Sending `POST` requests for new resource creation.
  4. **Dynamic Slug Generation**: Automatically creating SEO-friendly URLs from a title.
  5. **Real-time Feedback**: Providing character counts and estimated reading time.
  6. **Conditional UI**: Adapting button text and messages based on form state.
  
  🔍 WHAT YOU'LL LEARN:
  ====================
  - How to bind form inputs to Svelte's `$state` variables.
  - Implementing debounced input for performance (though not explicitly here, it's shown in `Index.svelte`).
  - Creating custom utility functions (e.g., `generateSlug`, `estimateReadingTime`).
  - Using Svelte's `$effect` rune for reactive side effects (like auto-generating slugs).
  - Displaying server-side validation errors dynamically.
  - Managing different post statuses (draft, published).
  
  FEATURES IMPLEMENTED IN THIS COMPONENT:
  ======================================
  - Form fields for title, content, excerpt, slug, meta title, meta description, and featured image URL.
  - Automatic, editable slug generation from the post title.
  - Post status selection (`draft` or `published`).
  - Real-time character counters for title and excerpt.
  - Estimated reading time calculation for content.
  - Integration with Laravel backend for form submission and validation.
  - Clear feedback on form processing and validation errors.
  
  This component is heavily commented to guide you through each concept
  and pattern involved in building a rich content creation form.
-->

<script>
  /*
   * IMPORTS AND DEPENDENCIES - SVELTE 5 + INERTIA.JS
   * ================================================
   * 
   * We import necessary modules for form submission, navigation, and reusable components.
   * 🎓 LEARN: How to import and use external functionality in Svelte.
   */
  import { router } from '@inertiajs/svelte' // Inertia.js router for form submissions and navigation
  import { Link } from '@inertiajs/svelte'     // Inertia.js Link component for SPA-like navigation
  
  /*
   * COMPONENT PROPS - DATA FROM LARAVEL CONTROLLER
   * ==============================================
   * 
   * These properties are passed to this Svelte component from the
   * `BlogPostController::create()` method. Inertia.js automatically maps
   * the data returned by the controller to `$props` in Svelte.
   * 
   * 🎓 LEARN: How data flows from Laravel (backend) to Svelte (frontend).
   * 
   * - `statuses`: An object containing available post statuses (e.g., { draft: 'Draft', published: 'Published' }).
   * - `maxTitleLength`: The maximum allowed characters for the post title.
   * - `maxExcerptLength`: The maximum allowed characters for the post excerpt.
   * - `errors`: Validation errors sent back from Laravel after form submission.
   * - `auth`: Global authentication data (available on all Inertia pages via middleware).
   * - `flash`: One-time flash messages (e.g., success messages).
   */
  let { 
    statuses,         
    maxTitleLength,   
    maxExcerptLength, 
    errors = {},      
    auth = {},        
    flash = {}        
  } = $props()
  
  /*
   * FORM STATE MANAGEMENT - SVELTE 5 RUNES ($STATE)
   * ===============================================
   * 
   * `$state` creates reactive variables that hold the current values of our form inputs.
   * Using the same pattern as authentication forms for consistency.
   */
  let values = $state({
    title: '',
    slug: '',
    content: '',
    excerpt: '',
    status: 'draft',
    meta_title: '',
    meta_description: '',
    featured_image: ''
  })
  
  /*
   * UI STATE MANAGEMENT
   * ==================
   */
  let processing = $state(false)
  let autoGenerateSlug = $state(true)
  
  /*
   * COMPUTED VALUES - FORM VALIDATION
   * ================================
   */
  let isFormValid = $derived(
    values.title.length > 0 && 
    values.content.length > 0 &&
    values.title.length <= maxTitleLength &&
    (values.excerpt.length === 0 || values.excerpt.length <= maxExcerptLength)
  )
  
  let titleCount = $derived(values.title.length)
  let excerptCount = $derived(values.excerpt.length)
  let hasErrors = $derived(Object.keys(errors).length > 0)
  
  /*
   * SLUG GENERATION UTILITIES
   * ========================
   */
  function generateSlug(title) {
    return title
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '') // Remove special characters
      .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
      .replace(/^-+|-+$/g, '')  // Remove leading/trailing hyphens
  }
  
  /*
   * REACTIVE SLUG GENERATION - SVELTE 5 PATTERN
   * ===========================================
   * 
   * Modern approach using $effect to reactively update slug when title changes.
   * This is much cleaner than manual event handlers and works perfectly with
   * Svelte 5's reactivity system.
   */
  $effect(() => {
    // Only auto-generate slug if we're in auto-generation mode and title exists
    if (autoGenerateSlug && values.title) {
      const newSlug = generateSlug(values.title)
      if (newSlug !== values.slug) {
        values.slug = newSlug
      }
    }
  })
  
  /*
   * FORM HANDLERS - SIMPLIFIED
   * ==========================
   * 
   * With reactive slug generation, we only need handlers for special cases.
   */
  
  // Handle manual slug editing (disables auto-generation)
  function handleSlugManualEdit(event) {
    autoGenerateSlug = false // Disable auto-generation once user edits manually
    values.slug = generateSlug(event.target.value) // Still clean the slug
  }
  
  // Re-enable auto-generation if user clears slug or it matches generated version
  function handleSlugFocus() {
    if (!values.slug || values.slug === generateSlug(values.title)) {
      autoGenerateSlug = true
    }
  }
  
  /*
   * FORM HANDLERS - MINIMAL (USING BIND:VALUE)
   * ==========================================
   * 
   * With Svelte 5's bind:value, we don't need manual event handlers for
   * simple input updates. Only special cases need handlers.
   */
  
  /*
   * FORM SUBMISSION HANDLER
   * ======================
   */
  function handleSubmit(event) {
    event.preventDefault()
    
    console.log('🚀 Creating blog post...')
    console.log('Form data:', values)
    
    processing = true
    
    router.post('/posts', values, {
      onSuccess: () => {
        console.log('✅ Post created successfully!')
        processing = false
      },
      onError: (errors) => {
        console.log('❌ Create error:', errors)
        processing = false
      },
      onFinish: () => {
        processing = false
      }
    })
  }
  
  /*
   * UTILITY FUNCTIONS
   * ================
   */
  
  // Estimate reading time based on content
  function estimateReadingTime(content) {
    const wordsPerMinute = 200
    const words = content.trim().split(/\s+/).length
    const minutes = Math.ceil(words / wordsPerMinute)
    return minutes === 1 ? '1 minute read' : `${minutes} minutes read`
  }
  
  // Clear form
  function clearForm() {
    values.title = ''
    values.slug = ''
    values.content = ''
    values.excerpt = ''
    values.status = 'draft'
    values.meta_title = ''
    values.meta_description = ''
    values.featured_image = ''
    autoGenerateSlug = true
  }
</script>

<!--
  DYNAMIC PAGE HEAD
  ================
-->
<svelte:head>
  <title>Create New Post | jmrecodes Educational Blog</title>
  <meta name="description" content="Create a new blog post on jmrecodes Educational Blog" />
  <meta name="robots" content="noindex, nofollow" />
</svelte:head>

<!--
  MAIN CONTENT CONTAINER
  ======================
-->
<div class="min-h-screen bg-gray-50">
  <!-- Header -->
  <div class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Create New Post</h1>
          <p class="mt-1 text-sm text-gray-600">
            Share your knowledge with the community
          </p>
        </div>
        
        <!-- Navigation -->
        <div class="flex space-x-3">
          <Link 
            href="/manage-posts"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Manage Posts
          </Link>
          <Link 
            href="/posts" 
            class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
          >
            View Blog
          </Link>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Form Container -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <form onsubmit={handleSubmit} class="space-y-6">
      <!-- Main Content Card -->
      <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="p-6 space-y-6">
          <!-- Title Field -->
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
              Post Title *
            </label>
            <input
              type="text"
              id="title"
              bind:value={values.title}
              placeholder="Enter an engaging title for your post..."
              maxlength={maxTitleLength}
              required
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                     focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                     disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                     class:border-red-500={errors?.title}
                     disabled={processing}
            />
            
            <!-- Character count and error -->
            <div class="mt-1 flex justify-between items-center">
              <div>
                {#if errors.title}
                  <p class="text-sm text-red-600">{errors.title}</p>
                {/if}
              </div>
              <p class="text-sm text-gray-500">
                {titleCount}/{maxTitleLength}
              </p>
            </div>
          </div>
          
          <!-- Slug Field -->
          <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
              URL Slug
              {#if autoGenerateSlug}
                <span class="text-xs text-gray-500">(auto-generated)</span>
              {/if}
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">/posts/</span>
              </div>
              <input
                type="text"
                id="slug"
                bind:value={values.slug}
                oninput={handleSlugManualEdit}
                onfocus={handleSlugFocus}
                placeholder="url-friendly-slug"
                required
                class="block w-full pl-16 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                       disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm font-mono"
                       class:border-red-500={errors?.slug}
                       disabled={processing}
              />
            </div>
            {#if errors.slug}
              <p class="mt-1 text-sm text-red-600">{errors.slug}</p>
            {/if}
          </div>
          
          <!-- Content Field -->
          <div>
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
              Post Content *
            </label>
            <textarea
              id="content"
              bind:value={values.content}
              rows="12"
              required
              placeholder="Write your blog post content here..."
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                     focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                     disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                     class:border-red-500={errors?.content}
                     disabled={processing}
            ></textarea>
            
            <div class="mt-1 flex justify-between items-center">
              <div>
                {#if errors.content}
                  <p class="text-sm text-red-600">{errors.content}</p>
                {/if}
              </div>
              {#if values.content.trim()}
                <p class="text-sm text-gray-500">
                  Estimated reading time: {estimateReadingTime(values.content)}
                </p>
              {/if}
            </div>
          </div>
          
          <!-- Excerpt Field -->
          <div>
            <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">
              Post Excerpt
            </label>
            <textarea
              id="excerpt"
              bind:value={values.excerpt}
              rows="3"
              maxlength={maxExcerptLength}
              placeholder="Optional excerpt..."
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                     focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                     disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                     class:border-red-500={errors?.excerpt}
                     disabled={processing}
            ></textarea>
            
            <div class="mt-1 flex justify-between items-center">
              <div>
                {#if errors.excerpt}
                  <p class="text-sm text-red-600">{errors.excerpt}</p>
                {:else}
                  <p class="text-sm text-gray-500">
                    If left empty, an excerpt will be generated from your content
                  </p>
                {/if}
              </div>
              <p class="text-sm text-gray-500">
                {excerptCount}/{maxExcerptLength}
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- SEO & Settings Card -->
      <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">SEO & Settings</h3>
          
          <div class="space-y-6">
            <!-- Status Selection -->
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                Post Status *
              </label>
              <select
                id="status"
                bind:value={values.status}
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                       disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                       class:border-red-500={errors?.status}
                       disabled={processing}
              >
                {#each Object.entries(statuses) as [value, label]}
                  <option value={value}>{label}</option>
                {/each}
              </select>
              {#if errors.status}
                <p class="mt-1 text-sm text-red-600">{errors.status}</p>
              {/if}
            </div>
            
            <!-- Meta Title -->
            <div>
              <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                SEO Title
              </label>
              <input
                type="text"
                id="meta_title"
                bind:value={values.meta_title}
                placeholder="SEO title (optional)"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                       disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                       class:border-red-500={errors?.meta_title}
                       disabled={processing}
              />
              <p class="mt-1 text-sm text-gray-500">
                Recommended: 50-60 characters. Leave empty to use post title.
              </p>
            </div>
            
            <!-- Meta Description -->
            <div>
              <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                SEO Description
              </label>
              <textarea
                id="meta_description"
                bind:value={values.meta_description}
                rows="2"
                placeholder="SEO description (optional)"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                       disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                       class:border-red-500={errors?.meta_description}
                       disabled={processing}
              ></textarea>
              <p class="mt-1 text-sm text-gray-500">
                Recommended: 150-160 characters. Leave empty to use excerpt.
              </p>
            </div>
            
            <!-- Featured Image URL -->
            <div>
              <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">
                Featured Image URL
              </label>
              <input
                type="url"
                id="featured_image"
                bind:value={values.featured_image}
                placeholder="https://example.com/image.jpg"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                       disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                       class:border-red-500={errors?.featured_image}
                       disabled={processing}
              />
              <p class="mt-1 text-sm text-gray-500">
                Image that will appear in social media shares and post listings.
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Form Actions -->
      <div class="flex justify-between items-center bg-white px-6 py-4 rounded-lg border border-gray-200">
        <div class="flex space-x-3">
          <button
            type="button"
            onclick={clearForm}
            disabled={processing}
            class="inline-flex justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg
                   text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500
                   disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Clear Form
          </button>
        </div>
        
        <div class="flex space-x-3">
          <Link 
            href="/manage-posts"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
          >
            Cancel
          </Link>
          
          <button
            type="submit"
            disabled={!isFormValid || processing}
            class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg
                   text-white bg-accent-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500
                   disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {#if processing}
              <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating...
            {:else}
              {values.status === 'published' ? 'Publish Post' : 'Save as Draft'}
            {/if}
          </button>
        </div>
      </div>
    </form>
  </div>
</div>