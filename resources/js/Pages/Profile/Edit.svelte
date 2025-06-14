<script>
  /*
   * USER PROFILE EDIT PAGE - ACCOUNT MANAGEMENT FOR EDUCATIONAL APP
   * ==============================================================
   * 
   * This Svelte component provides a comprehensive interface for authenticated users
   * to manage their profile. It demonstrates multiple forms for different actions
   * (updating info, changing password, deleting account) and robust validation.
   * 
   * 🎓 BEGINNER LEARNING OBJECTIVES:
   * ================================
   * 1. **Multi-Form Management**: Handling separate forms on a single page.
   * 2. **Reactive Forms (Svelte 5)**: Using `$state` for form data and `$derived` for validation.
   * 3. **Inertia.js Form Submissions**: Sending PUT/DELETE requests for updates.
   * 4. **User Statistics Display**: Fetching and showing aggregated user data.
   * 5. **Security UI Patterns**: Implementing password re-verification and confirmation for sensitive actions.
   * 6. **Flash Messages**: Displaying one-time success/error notifications.
   * 7. **Navigation & Quick Actions**: Streamlined user experience within the profile area.
   * 
   * 🔍 WHAT YOU'LL LEARN:
   * =====================
   * - How to structure a profile page with distinct sections.
   * - Using Inertia.js `router.put()` and `router.delete()` for updates.
   * - Implementing client-side validation logic for form fields.
   * - Managing loading states for individual forms.
   * - Conditional rendering for confirmation modals (e.g., account deletion).
   * - Integrating Laravel's backend errors with Svelte's UI.
   * - Displaying user-specific data from props.
   *
   * KEY SECTIONS OF THIS COMPONENT:
   * - Account Overview (User Statistics)
   * - Profile Information Form (Name & Email)
   * - Password Change Form (Current, New, Confirm Passwords)
   * - Danger Zone (Account Deletion with Confirmation)
   * - Quick Actions (Navigation shortcuts)
   */
  import { router } from '@inertiajs/svelte'
  import { Link } from '@inertiajs/svelte'
  
  /*
   * COMPONENT PROPS - DATA FROM LARAVEL CONTROLLER
   * ==============================================
   * 
   * These properties are passed to this Svelte component from the
   * `ProfileController::edit()` method. Inertia.js automatically maps
   * the data returned by the controller to `$props` in Svelte.
   * 
   * 🎓 LEARN: How data flows from Laravel (backend) to Svelte (frontend).
   */
  let { 
    user = {},           // The authenticated user object (e.g., { id, name, email, created_at })
    stats = {},          // Aggregated user statistics (e.g., { totalPosts, publishedPosts, memberSince })
    errors = {},         // Validation errors sent back from Laravel after form submission
    auth = {},           // Global authentication data (available on all Inertia pages)
    flash = {}           // One-time flash messages (e.g., success messages after an action)
  } = $props()
  
  /*
   * FORM STATE MANAGEMENT - SVELTE 5 RUNES ($STATE)
   * ===============================================
   * 
   * We use separate `$state` variables for each form to manage their data independently.
   * This makes the code cleaner and prevents unintended side effects between forms.
   * 
   * 🎓 LEARN: How `$state` creates reactive variables in Svelte 5.
   */
  
  // 1. Profile Information Form State (for Name and Email updates)
  let profileForm = $state({
    name: user.name || '',  // Pre-fill with current user's name
    email: user.email || '' // Pre-fill with current user's email
  })
  
  // 2. Password Change Form State
  let passwordForm = $state({
    current_password: '', // User's current password for verification
    password: '',         // The new password
    password_confirmation: '' // Confirmation of the new password
  })
  
  // 3. Account Deletion Form State
  let deleteForm = $state({
    password: '' // User's password to confirm account deletion
  })
  
  /*
   * UI STATE MANAGEMENT ($STATE)
   * ============================
   * 
   * These variables control the interactive elements and loading indicators of the UI.
   * 🎓 LEARN: How `$state` manages temporary UI states.
   */
  let processing = $state(false)         // True when profile form is submitting
  let processingPassword = $state(false) // True when password form is submitting
  let processingDelete = $state(false)   // True when delete account form is submitting
  let showDeleteConfirm = $state(false)  // Controls visibility of the account deletion confirmation modal
  
  /*
   * COMPUTED VALUES - SVELTE 5 DERIVED STATE ($DERIVED)
   * ====================================================
   * 
   * `$derived` creates values that automatically re-calculate whenever their
   * dependencies (`$state` variables or other `$derived` values) change.
   * This is perfect for real-time form validation status.
   * 
   * 🎓 LEARN: How `$derived` simplifies reactive logic in Svelte 5.
   */
  let profileFormValid = $derived(
    // Check if name is not empty and email is not empty and contains '@'
    profileForm.name.trim().length > 0 && 
    profileForm.email.trim().length > 0 &&
    profileForm.email.includes('@')
  )
  
  let passwordFormValid = $derived(
    // Check if all password fields are filled and new passwords match, and new password is at least 8 chars
    passwordForm.current_password.length > 0 &&
    passwordForm.password.length >= 8 && // Basic client-side check, server will do full validation
    passwordForm.password === passwordForm.password_confirmation
  )
  
  let deleteFormValid = $derived(
    // Check if password for deletion is entered
    deleteForm.password.length > 0
  )
  
  /*
   * FORM SUBMISSION HANDLERS - INERTIA.JS `router` METHODS
   * =====================================================
   * 
   * These functions handle submitting data to the Laravel backend using Inertia.js's `router`.
   * Each function manages its own loading state and reacts to success or error responses.
   * 🎓 LEARN: The proper way to make AJAX-like form submissions with Inertia.js + Svelte 5.
   */
  
  /**
   * UPDATE PROFILE INFORMATION (NAME & EMAIL)
   * ========================================
   * 
   * Sends a PUT request to update the user's name and email.
   * This corresponds to the `ProfileController::update()` method.
   */
  function handleProfileUpdate(event) {
    event.preventDefault() // Prevent default browser form submission
    
    if (!profileFormValid) return // Prevent submission if client-side validation fails
    
    processing = true // Set loading state
    
    router.put('/profile', profileForm, { // Send PUT request to /profile route with form data
      onFinish: () => {
        processing = false // Always reset processing state after request finishes
      },
      onSuccess: () => {
        // Laravel will redirect and send new user data, so no manual form clearing needed here.
        // The page will re-render with fresh props.
        console.log('✅ Profile information updated successfully!')
      },
      onError: (serverErrors) => {
        console.error('❌ Profile update failed:', serverErrors) // Log errors for debugging
        // Inertia automatically passes errors to $props.errors
      }
    })
  }
  
  /**
   * UPDATE PASSWORD
   * ==============
   * 
   * Sends a PUT request to change the user's password.
   * This corresponds to the `ProfileController::updatePassword()` method.
   * Requires current password for security.
   */
  function handlePasswordUpdate(event) {
    event.preventDefault() // Prevent default browser form submission
    
    if (!passwordFormValid) return // Prevent submission if client-side validation fails
    
    processingPassword = true // Set loading state for password form
    
    router.put('/profile/password', passwordForm, { // Send PUT request to /profile/password
      onFinish: () => {
        processingPassword = false // Always reset processing state
      },
      onSuccess: () => {
        // Clear password fields on successful update for security and UX
        passwordForm.current_password = ''
        passwordForm.password = ''
        passwordForm.password_confirmation = ''
        console.log('✅ Password updated successfully!')
      },
      onError: (serverErrors) => {
        console.error('❌ Password update failed:', serverErrors) // Log errors
      }
    })
  }
  
  /**
   * DELETE ACCOUNT
   * =============
   * 
   * Sends a DELETE request to permanently remove the user's account.
   * This corresponds to the `ProfileController::destroy()` method.
   * Requires password re-entry for confirmation (critical security measure).
   * 
   * ⚠️ IMPORTANT: This is an irreversible action! In real apps, consider soft deletes.
   */
  function handleDeleteAccount(event) {
    event.preventDefault() // Prevent default browser form submission
    
    if (!deleteFormValid) return // Prevent submission if password not entered
    
    processingDelete = true // Set loading state for delete form
    
    router.delete('/profile', deleteForm, { // Send DELETE request to /profile
      onFinish: () => {
        processingDelete = false // Always reset processing state
        showDeleteConfirm = false // Close confirmation modal after attempt
      },
      onSuccess: () => {
        // Laravel will log out the user and redirect to home page, so no manual action needed here.
        console.log('✅ Account deleted successfully!')
      },
      onError: (serverErrors) => {
        console.error('❌ Account deletion failed:', serverErrors) // Log errors
      }
    })
  }
  
  /**
   * NAVIGATION HELPER FUNCTION
   * ==========================
   * 
   * A simple utility function to navigate to different parts of the application.
   * `router.visit()` performs an Inertia.js visit, preventing full page reloads.
   */
  function navigateTo(href) {
    router.visit(href)
  }
</script>

<!--
  DYNAMIC PAGE HEAD - SEO AND BROWSER TAB INFORMATION
  ==================================================
  
  Using Svelte's `<svelte:head>` for setting page-specific metadata.
  This is crucial for SEO and displaying a meaningful title in the browser tab.
  
  🎓 LEARN: How `<svelte:head>` works for dynamic `<head>` content.
-->
<svelte:head>
  <title>Profile Settings | jmrecodes Educational Blog</title>
  <meta name="description" content="Update your account information and preferences" />
  <meta name="robots" content="noindex, nofollow" /> <!-- Prevent search engines from indexing profile pages -->
</svelte:head>

<!--
  MAIN PROFILE PAGE LAYOUT CONTAINER
  ==================================
  
  This `div` provides the overall structure for the profile page,
  applying a minimum height and background color for a consistent look.
-->
<div class="min-h-screen bg-gray-50">
  <!-- 
    HEADER SECTION - PAGE TITLE AND NAVIGATION BUTTONS
    ================================================
    
    This header provides a consistent visual identity and quick navigation
    options for the user.
   -->
  <div class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <!-- User Avatar Placeholder -->
          <div class="h-12 w-12 bg-primary-600 rounded-full flex items-center justify-center text-white text-lg font-medium">
            {user.name?.charAt(0).toUpperCase() || 'U'} <!-- Displays first letter of user's name -->
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
            <p class="text-sm text-gray-600">Manage your account information and preferences</p>
          </div>
        </div>
        
        <div class="flex items-center space-x-3">
          <!-- Link to Dashboard -->
          <Link 
            href="/dashboard" 
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
          >
            ← Dashboard
          </Link>
          
          <!-- Link to View Blog -->
          <Link 
            href="/posts" 
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-accent-500 hover:bg-accent-600 rounded-md transition-colors duration-200"
          >
            View Blog
          </Link>
        </div>
      </div>
    </div>
  </div>
  
  <!-- 
    FLASH MESSAGES - USER FEEDBACK
    ==============================
    
    Displays one-time success messages (e.g., "Profile updated successfully!")
    that come from Laravel via Inertia.js's flash session data.
    
    🎓 LEARN: How to display flash messages in Svelte components.
   -->
  {#if flash.success}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
      <div class="bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
          <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="ml-3">
            <p class="text-sm font-medium text-green-800">{flash.success}</p>
          </div>
        </div>
      </div>
    </div>
  {/if}
  
  <!-- 
    MAIN CONTENT AREA - PROFILE SECTIONS
    ===================================
    
    This section contains all the different cards/forms for managing the user's profile.
    It uses a consistent spacing (`space-y-8`) between sections.
   -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="space-y-8">
      
      <!-- 
        ACCOUNT STATISTICS CARD - USER OVERVIEW
        =====================================
        
        Displays key metrics about the user's activity on the blog.
        
        🎓 LEARN: How to present aggregated data in a digestible format.
       -->
      <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Total Posts Stat -->
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-2xl font-bold text-primary-600">{stats.totalPosts || 0}</div>
            <div class="text-sm text-gray-600">Total Posts</div>
          </div>
          <!-- Published Posts Stat -->
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-2xl font-bold text-green-600">{stats.publishedPosts || 0}</div>
            <div class="text-sm text-gray-600">Published</div>
          </div>
          <!-- Draft Posts Stat -->
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-2xl font-bold text-yellow-600">{stats.draftPosts || 0}</div>
            <div class="text-sm text-gray-600">Drafts</div>
          </div>
          <!-- Member Since Stat -->
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-sm font-medium text-gray-700">Member Since</div>
            <div class="text-sm text-gray-600">{stats.memberSince || 'Unknown'}</div>
          </div>
        </div>
      </div>
      
      <!-- 
        PROFILE INFORMATION FORM - NAME & EMAIL UPDATE
        ==============================================
        
        Allows the user to update their basic profile details.
        
        🎓 LEARN: Standard form input patterns with reactive binding and error display.
       -->
      <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-5 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Profile Information</h2>
          <p class="mt-1 text-sm text-gray-600">Update your account details and email address.</p>
        </div>
        
        <form onsubmit={handleProfileUpdate} class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name Field -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Full Name <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="name"
                bind:value={profileForm.name}
                placeholder="Enter your full name"
                required
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                       disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                       class:border-red-500={errors?.name} 
                       disabled={processing} 
              />
              {#if errors.name} <!-- Displays server-side validation error for 'name' -->
                <p class="mt-1 text-sm text-red-600">{errors.name}</p>
              {/if}
            </div>
            
            <!-- Email Field -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email Address <span class="text-red-500">*</span>
              </label>
              <input
                type="email"
                id="email"
                bind:value={profileForm.email} 
                placeholder="Enter your email address"
                required
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                       disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                       class:border-red-500={errors?.email} 
                       disabled={processing} 
              />
              {#if errors.email} <!-- Displays server-side validation error for 'email' -->
                <p class="mt-1 text-sm text-red-600">{errors.email}</p>
              {/if}
            </div>
          </div>
          
          <!-- Submit Button for Profile Information -->
          <div class="flex justify-end">
            <button
              type="submit"
              disabled={!profileFormValid || processing} 
              class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg
                     text-white bg-accent-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500
                     disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {#if processing}
                <!-- Loading spinner SVG -->
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Updating...
              {:else}
                Update Profile
              {/if}
            </button>
          </div>
        </form>
      </div>
      
      <!-- 
        PASSWORD CHANGE FORM
        ====================
        
        Allows the user to update their password. Requires current password for security.
        
        🎓 LEARN: How to implement a secure password change form with validation and loading states.
       -->
      <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-5 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Change Password</h2>
          <p class="mt-1 text-sm text-gray-600">Update your password to keep your account secure.</p>
        </div>
        
        <form onsubmit={handlePasswordUpdate} class="p-6 space-y-6">
          <div class="space-y-4">
            <!-- Current Password Field -->
            <div>
              <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                Current Password <span class="text-red-500">*</span>
              </label>
              <input
                type="password"
                id="current_password"
                bind:value={passwordForm.current_password} 
                placeholder="Enter your current password"
                required
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                       disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                       class:border-red-500={errors?.current_password} 
                       disabled={processingPassword}
              />
              {#if errors.current_password} <!-- Server error for current password -->
                <p class="mt-1 text-sm text-red-600">{errors.current_password}</p>
              {/if}
            </div>
            
            <!-- New Password Field -->
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                New Password <span class="text-red-500">*</span>
              </label>
              <input
                type="password"
                id="password"
                bind:value={passwordForm.password} 
                placeholder="Enter new password (min. 8 characters)"
                required
                minlength="8" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                       disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                       class:border-red-500={errors?.password} 
                       disabled={processingPassword}
              />
              {#if errors.password} <!-- Server error for new password -->
                <p class="mt-1 text-sm text-red-600">{errors.password}</p>
              {/if}
              <p class="mt-1 text-sm text-gray-500">Must be at least 8 characters with letters and numbers.</p>
            </div>
            
            <!-- Confirm New Password Field -->
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                Confirm New Password <span class="text-red-500">*</span>
              </label>
              <input
                type="password"
                id="password_confirmation"
                bind:value={passwordForm.password_confirmation} 
                placeholder="Confirm your new password"
                required
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-accent-500
                       disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                       disabled={processingPassword}
              />
              {#if passwordForm.password !== passwordForm.password_confirmation && passwordForm.password_confirmation.length > 0} <!-- Client-side mismatch error -->
                <p class="mt-1 text-sm text-red-600">Passwords do not match.</p>
              {/if}
            </div>
          </div>
          
          <!-- Submit Button for Password Change -->
          <div class="flex justify-end">
            <button
              type="submit"
              disabled={!passwordFormValid || processingPassword} 
              class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg
                     text-white bg-accent-500 hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent-500
                     disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {#if processingPassword}
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Updating Password...
              {:else}
                Update Password
              {/if}
            </button>
          </div>
        </form>
      </div>
      
      <!-- 
        DANGER ZONE - ACCOUNT DELETION
        ==============================
        
        This section contains the highly sensitive account deletion feature.
        It emphasizes the irreversibility of the action and requires explicit confirmation.
        
        🎓 LEARN: How to handle destructive actions with confirmation and clear warnings.
       -->
      <div class="bg-white shadow-sm rounded-lg border border-red-200">
        <div class="px-6 py-5 border-b border-red-200">
          <h2 class="text-lg font-semibold text-red-900">Danger Zone</h2>
          <p class="mt-1 text-sm text-red-600">Irreversible actions that will permanently affect your account.</p>
        </div>
        
        <div class="p-6">
          {#if !showDeleteConfirm} <!-- Show initial delete button if not confirmed -->
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-medium text-gray-900">Delete Account</h3>
                <p class="text-sm text-gray-600">Permanently delete your account and all associated data.</p>
              </div>
              <button
                onclick={() => showDeleteConfirm = true} 
                class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md
                       text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                Delete Account
              </button>
            </div>
          {:else} <!-- Show confirmation form if delete is initiated -->
            <form onsubmit={handleDeleteAccount} class="space-y-4">
              <!-- Warning Message -->
              <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                  <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                  </svg>
                  <div class="ml-3">
                    <h4 class="text-sm font-medium text-red-800">This action cannot be undone</h4>
                    <p class="mt-1 text-sm text-red-700">
                      This will permanently delete your account and all your blog posts. Enter your password to confirm.
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Password Confirmation for Deletion -->
              <div>
                <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-2">
                  Confirm with Password <span class="text-red-500">*</span>
                </label>
                <input
                  type="password"
                  id="delete_password"
                  bind:value={deleteForm.password} 
                  placeholder="Enter your password to confirm deletion"
                  required
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm
                         focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500
                         disabled:bg-gray-100 disabled:cursor-not-allowed sm:text-sm"
                         class:border-red-500={errors?.password} 
                         disabled={processingDelete}
                />
                {#if errors.password} <!-- Server error for password (e.g., incorrect password) -->
                  <p class="mt-1 text-sm text-red-600">{errors.password}</p>
                {/if}
              </div>
              
              <!-- Action Buttons (Cancel and Confirm Delete) -->
              <div class="flex space-x-3">
                <button
                  type="button"
                  onclick={() => showDeleteConfirm = false} 
                  disabled={processingDelete}
                  class="inline-flex justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg
                         text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500
                         disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Cancel
                </button>
                
                <button
                  type="submit"
                  disabled={!deleteFormValid || processingDelete} 
                  class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg
                         text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500
                         disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {#if processingDelete}
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Deleting Account...
                  {:else}
                    Yes, Delete My Account
                  {/if}
                </button>
              </div>
            </form>
          {/if}
        </div>
      </div>
      
      <!-- 
        QUICK ACTIONS - NAVIGATION SHORTCUTS
        ===================================
        
        Provides convenient buttons for common tasks related to blogging and account management.
        
        🎓 LEARN: How to create a quick action panel with internal navigation.
       -->
      <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Write New Post Button -->
          <button
            onclick={() => navigateTo('/posts/create')} 
            class="flex items-center justify-center px-4 py-3 bg-accent-500 text-white rounded-md hover:bg-accent-600 transition-colors duration-200"
          >
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Write New Post
          </button>
          
          <!-- Manage Posts Button -->
          <button
            onclick={() => navigateTo('/manage-posts')}
            class="flex items-center justify-center px-4 py-3 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-200"
          >
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Manage Posts
          </button>
          
          <!-- View Blog Button -->
          <button
              onclick={() => navigateTo('/posts')}
            class="flex items-center justify-center px-4 py-3 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-200"
          >
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            View Blog
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!--
  🎓 EDUCATIONAL SUMMARY - USER PROFILE EDIT COMPONENT
  ====================================================
  
  This `Profile/Edit.svelte` component is a great example of building a secure and user-friendly
  profile management interface in Svelte with Inertia.js.
  
  ✅ KEY LEARNINGS:
  - **Modular Forms**: How to separate different update functionalities into distinct forms.
  - **Dynamic State**: Using `$state` and `$derived` for real-time form data and validation status.
  - **Inertia.js Integration**: Performing `PUT` and `DELETE` requests with clear success/error handling.
  - **Security in UI**: Implementing password re-entry for sensitive actions and clear warnings.
  - **User Feedback**: Utilizing flash messages and loading indicators for a smooth experience.
  - **Component Reusability**: Preparing data in the backend and consuming it as props in the frontend.
  
  This component serves as a robust example for building complex, interactive user interfaces
  while adhering to modern web development best practices.
-->