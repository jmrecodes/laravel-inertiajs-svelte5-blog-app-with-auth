<!--
  BLOG POST EDIT PAGE - EXISTING POST EDITING FORM FOR EDUCATIONAL APP
  ====================================================================
  
  This Svelte component provides a comprehensive form for authenticated users
  to edit their existing blog posts. It showcases advanced form handling techniques,
  real-time validation, tracking unsaved changes, and secure deletion.
  
  🎓 BEGINNER LEARNING OBJECTIVES:
  ============================
  1. **Form Prefilling**: Loading existing data into form fields for editing.
  2. **HTTP PUT Requests**: Using Inertia.js `router.put()` for updating resources.
  3. **Authorization & Ownership**: Ensuring only the post's author can edit/delete.
  4. **Unsaved Changes Warning**: Implementing a common UX pattern to prevent data loss.
  5. **Dynamic UI**: Adapting form fields and buttons based on existing data and user interaction.
  6. **Safe Deletion**: Providing confirmation for irreversible actions.
  
  🔍 WHAT YOU'LL LEARN:
  ====================
  - How to initialize form state with existing `post` data from props.
  - Using `$effect` to detect and warn about unsaved changes.
  - Implementing manual vs. auto-generated slug behavior.
  - Handling multiple form actions (update, delete) on a single page.
  - Displaying server-side validation errors for each field.
  - Integrating utility functions for reading time and slug generation.
  
  FEATURES IMPLEMENTED IN THIS COMPONENT:
  ======================================
  - Pre-filled form with title, slug, content, excerpt, SEO fields, and featured image URL.
  - Option to manually edit or auto-generate slug from title.
  - Post status management (draft, published, archived).
  - Real-time character counters for title and excerpt.
  - **Unsaved changes detection** with a user warning on navigation.
  - Secure **delete post functionality** with a confirmation step.
  - Integration with Laravel backend for update, delete, and validation.
  - Clear feedback on form processing and validation errors.
  
  This component is heavily commented to guide you through each concept
  and pattern involved in building a robust content editing form.
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
   * `BlogPostController::edit()` method. Inertia.js automatically maps
   * the data returned by the controller to `$props` in Svelte.
   * 
   * 🎓 LEARN: How an existing data object (`post`) is passed from Laravel (backend) to Svelte (frontend).
   * 
   * - `post`: The existing blog post object, including its content, metadata, and associated `user` object.
   * - `statuses`: An object containing available post statuses (e.g., { draft: 'Draft', published: 'Published', archived: 'Archived' }).
   * - `maxTitleLength`: The maximum allowed characters for the post title.
   * - `maxExcerptLength`: The maximum allowed characters for the post excerpt.
   * - `errors`: Validation errors sent back from Laravel after form submission.
   * - `auth`: Global authentication data (available on all Inertia pages via middleware).
   * - `flash`: One-time flash messages (e.g., success messages).
   */
  let { 
    post,             
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
   * These are initialized with the existing `post` data, allowing the user to edit them.
   * Changes to these variables automatically update the UI.
   * 
   * 🎓 LEARN: How `$state` manages form data, especially when pre-filling from existing data.
   */
  let values = $state({
    title: post.title || '',              // Pre-fill with existing title
    slug: post.slug || '',                 // Pre-fill with existing slug
    content: post.content || '',
    excerpt: post.excerpt || '',
    status: post.status || 'draft',
    meta_title: post.meta_title || '',
    meta_description: post.meta_description || '',
    featured_image: post.featured_image || ''
  })
  
  /*
   * UI STATE MANAGEMENT ($STATE)
   * ============================
   * 
   * These variables control the interactive elements and loading indicators of the UI.
   * They also track specific states like unsaved changes or delete confirmation.
   * 🎓 LEARN: How `$state` manages temporary UI states and user interaction flags.
   */
  let processing = $state(false)         // True when the form is submitting (saving updates)
  let autoGenerateSlug = $state(false)   // Controls whether the slug is auto-generated or manually edited.
                                         // Starts as false for existing posts to preserve current slug.
  let showDeleteConfirm = $state(false)  // Controls visibility of the post deletion confirmation modal
  let hasUnsavedChanges = $state(false)  // Flag to track if the user has made any changes to the form
  
  /*
   * INITIAL VALUES FOR UNSAVED CHANGES TRACKING
   * ===========================================
   * 
   * We store a copy of the *original* post values when the component loads.
   * This allows us to compare the current `values` state against these original values
   * to determine if any changes have been made (`hasUnsavedChanges`).
   * 🎓 LEARN: A common pattern for tracking form dirty states.
   */
  let originalValues = {
    title: post.title || '',
    slug: post.slug || '',
    content: post.content || '',
    excerpt: post.excerpt || '',
    status: post.status || 'draft',
    meta_title: post.meta_title || '',
    meta_description: post.meta_description || '',
    featured_image: post.featured_image || ''
  }
  
  /*
   * COMPUTED VALUES - SVELTE 5 DERIVED STATE ($DERIVED)
   * ====================================================
   * 
   * `$derived` creates values that automatically re-calculate whenever their
   * dependencies (`$state` variables) change. This is perfect for real-time
   * validation status and character counters.
   * 
   * 🎓 LEARN: How `$derived` simplifies reactive logic for form validation and display.
   */
  let isFormValid = $derived(
    // Basic client-side validation: title and content must not be empty
    values.title.length > 0 && 
    values.content.length > 0 &&
    // Ensure title length doesn't exceed maximum
    values.title.length <= maxTitleLength &&
    // Ensure excerpt length is within limits if provided
    (values.excerpt.length === 0 || values.excerpt.length <= maxExcerptLength)
  )
  
  let titleCount = $derived(values.title.length)   // Live character count for title
  let excerptCount = $derived(values.excerpt.length) // Live character count for excerpt
  let hasErrors = $derived(Object.keys(errors).length > 0) // Check if any server-side validation errors exist
  
  /*
   * REACTIVE SIDE EFFECT - DETECT UNSAVED CHANGES ($EFFECT)
   * ========================================================
   * 
   * This `$effect` continuously compares the current `values` with `originalValues`.
   * If they differ, it sets `hasUnsavedChanges` to true, triggering a UI update (e.g., a warning).
   * `JSON.stringify()` is used for a simple deep comparison of the objects.
   * 
   * 🎓 LEARN: How `$effect` is used for side effects and how to implement change detection.
   */
  $effect(() => {
    hasUnsavedChanges = JSON.stringify(values) !== JSON.stringify(originalValues)
  })
  
  /*
   * SLUG GENERATION UTILITY FUNCTION
   * ================================
   * 
   * This function converts a given string (e.g., a post title) into a URL-friendly slug.
   * It removes special characters, replaces spaces with hyphens, and cleans up dashes.
   * 
   * 🎓 LEARN: Basic string manipulation and regex for creating clean URLs.
   */
  function generateSlug(title) {
    return title
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '') // Remove all non-word, non-space, non-hyphen characters
      .replace(/[\s_-]+/g, '-') // Replace any sequence of spaces/underscores/hyphens with a single hyphen
      .replace(/^-+|-+$/g, '')  // Remove leading or trailing hyphens
  }
  
  /*
   * FORM INPUT HANDLERS - UPDATING STATE AND CONTROLLING SLUG BEHAVIOR
   * ==================================================================
   * 
   * These functions manage updates from form inputs and control the auto-generation
   * or manual editing of the slug.
   * 🎓 LEARN: How to bind inputs and implement custom input logic in Svelte.
   */
  
  /**
   * HANDLE TITLE INPUT
   * ------------------
   * Updates the `values.title` and conditionally updates `values.slug` if auto-generation is enabled.
   */
  function handleTitleInput(event) {
    values.title = event.target.value
    if (autoGenerateSlug) {
      values.slug = generateSlug(values.title)
    }
  }
  
  /**
   * HANDLE SLUG INPUT
   * -----------------
   * When the user manually types into the slug field, we disable auto-generation.
   */
  function handleSlugInput(event) {
    autoGenerateSlug = false // Disable auto-generation to respect user's manual input
    values.slug = generateSlug(event.target.value) // Still sanitize the input to ensure it's a valid slug
  }
  
  /**
   * HANDLE CONTENT INPUT
   * --------------------
   * Updates the `values.content` when the user types in the content textarea.
   */
  function handleContentInput(event) {
    values.content = event.target.value
  }
  
  /**
   * HANDLE EXCERPT INPUT
   * --------------------
   * Updates the `values.excerpt` when the user types in the excerpt textarea.
   */
  function handleExcerptInput(event) {
    values.excerpt = event.target.value
  }
  
  /**
   * HANDLE STATUS CHANGE
   * --------------------
   * Updates the `values.status` when the user selects a new status from the dropdown.
   */
  function handleStatusChange(event) {
    values.status = event.target.value
  }
  
  /**
   * HANDLE META TITLE INPUT
   * -----------------------
   * Updates the `values.meta_title` for SEO purposes.
   */
  function handleMetaTitleInput(event) {
    values.meta_title = event.target.value
  }
  
  /**
   * HANDLE META DESCRIPTION INPUT
   * -----------------------------
   * Updates the `values.meta_description` for SEO purposes.
   */
  function handleMetaDescriptionInput(event) {
    values.meta_description = event.target.value
  }
  
  /**
   * HANDLE FEATURED IMAGE URL INPUT
   * -------------------------------
   * Updates the `values.featured_image` URL.
   */
  function handleFeaturedImageInput(event) {
    values.featured_image = event.target.value
  }
  
  /*
   * FORM SUBMISSION HANDLER - INERTIA.JS `router.put()`
   * ====================================================
   * 
   * This function sends the updated form data to the Laravel backend to update the existing post.
   * It uses Inertia.js's `router.put()` method for a seamless SPA experience.
   * 
   * 🎓 LEARN: The standard way to submit update forms (PUT requests) in Inertia.js with Svelte 5.
   */
  function handleSubmit(event) {
    event.preventDefault() // Prevent default browser form submission (full page reload)
    
    console.log('🚀 Updating blog post...')
    console.log('Form data:', values)
    
    processing = true // Set processing state to show loading indicator
    
    router.put(`/posts/${post.id}`, values, { // Send PUT request to the specific post's update route
      onSuccess: () => {
        console.log('✅ Post updated successfully!')
        processing = false // Reset processing state on success
        hasUnsavedChanges = false // Clear unsaved changes flag after successful save
        // Update original values to reflect saved state, so further changes are tracked correctly
        originalValues = { ...values } 
      },
      onError: (serverErrors) => {
        console.log('❌ Update error:', serverErrors) // Log errors for debugging
        processing = false // Reset processing state on error
        // Inertia automatically passes errors to $props.errors
      },
      onFinish: () => {
        processing = false // Always reset processing state after request finishes (success or error)
      }
    })
  }
  
  /*
   * DELETE POST HANDLER - INERTIA.JS `router.delete()`
   * ==================================================
   * 
   * This function handles the deletion of the current blog post. It includes a confirmation step
   * to prevent accidental data loss.
   * 
   * 🎓 LEARN: How to perform DELETE requests with Inertia.js and implement user confirmation.
   * 
   * ⚠️ IMPORTANT: Deletion is a destructive operation. Always prompt for confirmation!
   */
  function handleDelete() {
    // If confirmation is not yet shown, show it and stop here
    if (!showDeleteConfirm) {
      showDeleteConfirm = true
      return
    }
    
    console.log('🗑️ Deleting blog post...')
    
    processing = true // Use general processing for delete action as well

    router.delete(`/posts/${post.id}`, { // Send DELETE request to the specific post's delete route
      onSuccess: () => {
        console.log('✅ Post deleted successfully!')
        // Laravel will redirect to /dashboard after deletion, so no manual navigation needed here.
      },
      onError: (serverErrors) => {
        console.log('❌ Delete error:', serverErrors) // Log errors
        showDeleteConfirm = false // Hide confirmation modal on error
        processing = false // Reset processing state
      }
    })
  }
  
  /*
   * UTILITY FUNCTIONS - GENERAL HELPERS
   * ===================================
   * 
   * These functions perform calculations or actions not directly related to form submission.
   * 🎓 LEARN: How to write modular utility functions and integrate browser APIs.
   */
  
  /**
   * ESTIMATE READING TIME
   * ---------------------
   * Calculates an approximate reading time based on the content's word count.
   * Provides a helpful metric for readers.
   */
  function estimateReadingTime(content) {
    const wordsPerMinute = 200 // Average reading speed
    const words = content.trim().split(/\s+/).length // Count words by splitting on whitespace
    const minutes = Math.ceil(words / wordsPerMinute) // Calculate minutes, round up
    return minutes === 1 ? '1 minute read' : `${minutes} minutes read` // Format output string
  }
  
  /**
   * HANDLE NAVIGATION WITH UNSAVED CHANGES WARNING
   * --------------------------------------------
   * 
   * This function provides a warning to the user if they try to navigate away
   * from the page while having unsaved changes in the form.
   * 
   * 🎓 LEARN:
   * - **`window.confirm()`**: A simple browser dialog for user confirmation.
   * - **`router.visit()`**: Performing an Inertia.js client-side navigation.
   */
  function handleNavigation(href) {
    if (hasUnsavedChanges) {
      // If there are unsaved changes, prompt the user
      if (confirm('You have unsaved changes. Are you sure you want to leave?')) {
        router.visit(href) // Proceed with navigation if confirmed
      }
    } else {
      router.visit(href) // No unsaved changes, navigate directly
    }
  }
</script>

<!--
  DYNAMIC PAGE HEAD - SEO FOR EDIT POST PAGE
  ==========================================
  
  `<svelte:head>` is used to set page-specific meta tags for search engines and browser tabs.
  This helps with basic SEO for the editing page.
  
  🎓 LEARN: How `<svelte:head>` sets dynamic HTML `<head>` content.
-->
<svelte:head>
  <title>Edit: {post.title} | jmrecodes Educational Blog</title>
  <meta name="description" content="Edit your blog post on jmrecodes Educational Blog" />
  <meta name="robots" content="noindex, nofollow" /> <!-- Prevent search engines from indexing edit forms -->
</svelte:head>

<!--
  MAIN CONTENT CONTAINER - OVERALL PAGE LAYOUT
  ===========================================
  
  This `div` provides the overall structure for the post editing page,
  applying a minimum height and background color for a consistent look.
  It uses responsive design principles with Tailwind CSS utilities.
-->
<div class="min-h-screen bg-gray-50">
  <!-- 
    HEADER SECTION - PAGE TITLE AND NAVIGATION BUTTONS
    ================================================
    
    This header provides a clear title for the page and quick navigation
    options for the user (e.g., view post, back to manage posts).
   -->
  <div class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Edit Post</h1>
          <p class="mt-1 text-sm text-gray-600">
            Make changes to "{post.title}"
            {#if hasUnsavedChanges} <!-- Displays a warning if changes are unsaved -->
              <span class="text-amber-600 font-medium">• Unsaved changes</span>
            {/if}
          </p>
        </div>
        
        <!-- Navigation Links -->
        <div class="flex space-x-3">
          <!-- Link to View Post -->
          <Link 
            href={`/posts/${post.slug}`}
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            View Post
          </Link>
          
          <!-- Button to Go Back to Manage Posts (with unsaved changes warning) -->
          <Link 
            href="/manage-posts"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Manage Posts
          </Link>
        </div>
      </div>
    </div>
  </div>
  
  <!-- 
    MAIN FORM CONTAINER - CONTENT CARDS
    ===================================
    
    This section holds the main form fields for editing a blog post,
    organized into logical cards for better user experience.
   -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <form onsubmit={handleSubmit} class="space-y-8"> <!-- The main form, submits data to handleSubmission -->
      <!-- 
        MAIN CONTENT CARD - TITLE, SLUG, CONTENT, EXCERPT
        =================================================
        
        This card groups the primary content fields of the blog post.
       -->
      <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="p-6 space-y-6">
          <!-- Title Field -->
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
              Post Title <span class="text-red-500">*</span> <!-- Required field indicator -->
            </label>
            <input
              type="text"
              id="title"
              bind:value={values.title} 
              oninput={handleTitleInput} 
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
                {titleCount}/{maxTitleLength} <!-- Live character count -->
              </p>
            </div>
          </div>
          
          <!-- Slug Field -->
          <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
              URL Slug
              {#if autoGenerateSlug}
                <span class="text-xs text-gray-500">(auto-generated)</span> <!-- Indicates auto-generation -->
              {/if}
            </label>
            <div class="space-y-2">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">/posts/</span> <!-- Base URL path -->
                </div>
                <input
                  type="text"
                  id="slug"
                  bind:value={values.slug}
                  oninput={handleSlugInput} 
                  placeholder="url-friendly-slug"
                  required
                  class="block w-full pl-16 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm
                         focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                         disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm font-mono"
                         class:border-red-500={errors?.slug} 
                         disabled={processing} 
                />
              </div>
              {#if !autoGenerateSlug} <!-- Show button to re-enable auto-gen if currently manual -->
                <button
                  type="button"
                  onclick={() => {
                    autoGenerateSlug = true // Enable auto-generation
                    values.slug = generateSlug(values.title) // Re-generate slug from current title
                  }}
                  class="px-3 py-2 text-sm text-accent-600 hover:text-accent-700 underline"
                >
                  Auto-generate from title
                </button>
              {/if}
            </div>
            {#if errors.slug}
              <p class="mt-1 text-sm text-red-600">{errors.slug}</p>
            {/if}
          </div>
          
          <!-- Content Field (Textarea for Rich Content) -->
          <div>
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
              Post Content <span class="text-red-500">*</span> <!-- Required field -->
            </label>
            <textarea
              id="content"
              bind:value={values.content} 
              oninput={handleContentInput} 
              rows="12"
              required
              placeholder="Write your blog post content here..."
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                     focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                     disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                     class:border-red-500={errors?.content}
                     disabled={processing} 
            ></textarea>
            
            <!-- Content error and estimated reading time -->
            <div class="mt-1 flex justify-between items-center">
              <div>
                {#if errors.content}
                  <p class="text-sm text-red-600">{errors.content}</p>
                {/if}
              </div>
              {#if values.content.trim()} <!-- Only show reading time if content is not empty -->
                <p class="text-sm text-gray-500">
                  Estimated reading time: {estimateReadingTime(values.content)}
                </p>
              {/if}
            </div>
          </div>
          
          <!-- Excerpt Field (Optional Summary) -->
          <div>
            <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">
              Post Excerpt
            </label>
            <textarea
              id="excerpt"
              bind:value={values.excerpt}
              oninput={handleExcerptInput} 
              rows="3"
              maxlength={maxExcerptLength} 
              placeholder="Optional excerpt..."
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                     focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                     disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                     class:border-red-500={errors?.excerpt}
                     disabled={processing} 
            ></textarea>
            
            <!-- Excerpt error and guidance -->
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
                {excerptCount}/{maxExcerptLength} <!-- Live character count -->
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- 
        SEO & SETTINGS CARD - METADATA AND STATUS
        =========================================
        
        This card groups fields related to SEO (Search Engine Optimization)
        and post status management.
       -->
      <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">SEO & Settings</h3>
          
          <div class="space-y-6">
            <!-- Post Status Selection -->
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                Post Status <span class="text-red-500">*</span> <!-- Required field -->
              </label>
              <select
                id="status"
                bind:value={values.status}
                onchange={handleStatusChange} 
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                       disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                       class:border-red-500={errors?.status} 
                       disabled={processing} 
              >
                {#each Object.entries(statuses) as [value, label]} <!-- Loop through `statuses` prop to create options -->
                  <option value={value}>{label}</option>
                {/each}
              </select>
              {#if errors.status}
                <p class="mt-1 text-sm text-red-600">{errors.status}</p>
              {/if}
            </div>
            
            <!-- Meta Title Field (for SEO) -->
            <div>
              <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                SEO Title
              </label>
              <input
                type="text"
                id="meta_title"
                bind:value={values.meta_title} 
                oninput={handleMetaTitleInput} 
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
            
            <!-- Meta Description Field (for SEO) -->
            <div>
              <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                SEO Description
              </label>
              <textarea
                id="meta_description"
                bind:value={values.meta_description} 
                oninput={handleMetaDescriptionInput} 
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
            
            <!-- Featured Image URL Field (Optional) -->
            <div>
              <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">
                Featured Image URL
              </label>
              <input
                type="url"
                id="featured_image"
                bind:value={values.featured_image} 
                oninput={handleFeaturedImageInput} 
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
      
      <!-- 
        FORM ACTIONS - BUTTONS FOR CLEAR, CANCEL, SAVE/PUBLISH
        =======================================================
        
        This section contains the action buttons for the form (Delete, Cancel, Save/Publish).
        It demonstrates dynamic button text and loading states.
       -->
      <div class="flex justify-between items-center bg-white px-6 py-4 rounded-lg border border-gray-200">
        <!-- Danger Zone (Delete Post) -->
        <div>
          <!-- Conditional display for Delete button or confirmation -->
          {#if !showDeleteConfirm} 
            <button
              type="button" 
              onclick={handleDelete} 
              disabled={processing} 
              class="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-lg
                     text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500
                     disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
              Delete Post
            </button>
          {:else} <!-- Display confirmation message and buttons -->
            <div class="flex items-center space-x-3">
              <span class="text-sm text-red-600 font-medium">Are you sure?</span>
              <!-- Confirm Delete Button -->
              <button
                type="button" 
                onclick={handleDelete} 
                class="inline-flex items-center px-3 py-1 border border-red-300 shadow-sm text-sm font-medium rounded text-red-700 bg-red-50 hover:bg-red-100 transition-colors duration-200"
              >
                Yes, delete
              </button>
              <!-- Cancel Delete Button -->
              <button
                type="button" 
                onclick={() => showDeleteConfirm = false} 
                class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm font-medium rounded text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
              >
                Cancel
              </button>
            </div>
          {/if}
        </div>
        
        <!-- Save Actions -->
        <div class="flex space-x-3">
          <!-- Cancel Button (with unsaved changes warning) -->
          <button
            type="button" 
            onclick={() => showDeleteConfirm ? (showDeleteConfirm = false) : (hasUnsavedChanges ? confirm('You have unsaved changes. Are you sure you want to leave?') && router.get('/manage-posts') : router.get('/manage-posts'))} 
            disabled={processing} 
            class="inline-flex justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg
                   text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500
                   disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancel
          </button>
          
          <!-- Submit Button (Update Post) -->
          <button
            type="submit" 
            disabled={!isFormValid || processing || !hasUnsavedChanges} 
            class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg
                   text-white bg-accent-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500
                   disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {#if processing}
              <!-- Loading spinner SVG -->
              <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Saving...
            {:else}
              Update Post
            {/if}
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!--
  🎓 EDUCATIONAL SUMMARY - BLOG POST EDIT COMPONENT
  ==================================================
  
  This `BlogPosts/Edit.svelte` component is a comprehensive example of building a rich content
  editing form in a modern web application.
  
  ✅ KEY LEARNINGS:
  - **Data Initialization**: Pre-filling forms with existing data for a smooth editing experience.
  - **Change Tracking**: Implementing a mechanism to detect and warn about unsaved changes.
  - **Backend Integration**: Performing `PUT` and `DELETE` requests for resource updates and deletion.
  - **Dynamic Form Behavior**: Controlling slug auto-generation vs. manual editing.
  - **User Experience**: Providing clear feedback, loading states, and confirmation for destructive actions.
  - **Modular Design**: Breaking down a complex form into logical sections for readability.
  
  This component serves as a robust foundation for any content editing interface
  in your web application, focusing on both functionality and user experience.
-->