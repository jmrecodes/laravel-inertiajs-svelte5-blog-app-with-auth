<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

/**
 * AUTHENTICATION CONTROLLER - USER LOGIN, LOGOUT, AND REGISTRATION
 * ================================================================
 * 
 * This controller handles all authentication-related functionality for our
 * educational blog application. It demonstrates how authentication works
 * in a Svelte + Inertia.js + Laravel setup.
 * 
 * EDUCATIONAL CONCEPTS COVERED:
 * - User registration and validation
 * - Login with email/password
 * - Session-based authentication
 * - Logout and session cleanup
 * - Inertia.js page rendering
 * - Form validation and error handling
 * - Security best practices
 * 
 * AUTHENTICATION FLOW:
 * 1. User submits login/register form (Svelte frontend)
 * 2. Form data sent to Laravel via Inertia (no traditional API calls!)
 * 3. Laravel validates data and creates/authenticates user
 * 4. Laravel creates session and redirects
 * 5. Inertia updates frontend with new auth state
 * 6. Svelte components reactively update based on auth status
 * 
 * WHY THIS APPROACH?
 * - Single authentication system (no JWT complexity)
 * - Automatic CSRF protection
 * - Server-side session security
 * - Seamless SPA experience with traditional web security
 */
class AuthController extends Controller
{
    /**
     * SHOW LOGIN PAGE
     * ==============
     * 
     * Render the login form using Inertia.js.
     * 
     * EDUCATIONAL NOTE:
     * Inertia::render() does NOT return HTML like traditional Laravel views.
     * Instead, it returns JSON that tells your Svelte frontend which
     * component to load and what props to pass to it.
     * 
     * INERTIA MAGIC:
     * - First visit: Returns full HTML page with Svelte app
     * - Subsequent visits: Returns JSON for seamless navigation
     * - No page refresh needed!
     * 
     * GUEST MIDDLEWARE:
     * This route should use 'guest' middleware to redirect already-logged-in
     * users away from the login page. See routes/web.php for implementation.
     */
    public function showLogin(Request $request): Response
    {
        // Check if user has already attempted a reset in the last 12 hours
        if (Cache::get('password_reset_attempt_' . $request->ip())) {
            $canResetPassword = false;
        } else {
            $canResetPassword = true;
        }
        
        return Inertia::render('Auth/Login', [
            /**
             * PROPS PASSED TO SVELTE COMPONENT
             * ===============================
             * 
             * These props will be available in your Svelte Login component:
             * let { canResetPassword } = $props();
             * 
             * You can pass any data your frontend needs here.
             */
            'canResetPassword' => $canResetPassword, // Feature flag for password reset
            'status' => session('status'), // Flash message from password reset
        ]);
    }

    /**
     * SHOW REGISTRATION PAGE
     * =====================
     * 
     * Render the registration form using Inertia.js.
     * Similar to login but for new user registration.
     */
    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register', [
            // Could pass terms of service URL, privacy policy, etc.
            'termsUrl' => '/terms',
            'privacyUrl' => '/privacy',
        ]);
    }

    /**
     * HANDLE LOGIN ATTEMPT
     * ===================
     * 
     * Process the login form submission.
     * This demonstrates Laravel's authentication system with Inertia.js.
     * 
     * EDUCATIONAL FLOW:
     * 1. Validate form inputs (email format, required fields)
     * 2. Attempt authentication with Laravel's Auth system
     * 3. If successful: create session, redirect to intended page
     * 4. If failed: return with validation errors
     * 
     * SECURITY FEATURES:
     * - Password verification using Laravel's built-in hashing
     * - Rate limiting to prevent brute force attacks
     * - CSRF protection (handled by middleware)
     * - Session hijacking protection
     */
    public function login(Request $request): RedirectResponse
    {
        /**
         * INPUT VALIDATION - FIRST LINE OF DEFENSE
         * =======================================
         * 
         * Always validate user input before processing!
         * Laravel's validation provides:
         * - Type checking (email format, required fields)
         * - Security sanitization
         * - Automatic error messages
         * - Failed validation automatically returns errors to frontend
         * 
         * VALIDATION RULES EXPLAINED:
         * - 'required': Field must be present and not empty
         * - 'email': Must be valid email format
         * - 'string': Must be string type
         * - 'min:8': Minimum 8 characters
         */
        $credentials = $request->validate([
            'email' => ['required', 'email', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        /**
         * AUTHENTICATION ATTEMPT - THE CORE LOGIC
         * ======================================
         * 
         * Auth::attempt() does several things:
         * 1. Finds user by email
         * 2. Verifies password using Hash::check()
         * 3. If successful: logs user in (creates session)
         * 4. Returns true/false based on success
         * 
         * EDUCATIONAL NOTE:
         * Laravel automatically hashes passwords and compares them securely.
         * You never work with plain text passwords in your application!
         * 
         * REMEMBER ME:
         * The second parameter enables "remember me" functionality.
         * This creates a long-lived cookie for persistent login.
         */
        $remember = $request->boolean('remember', false);
        
        if (Auth::attempt($credentials, $remember)) {
            /**
             * SUCCESSFUL LOGIN - SESSION MANAGEMENT
             * ===================================
             * 
             * When login succeeds, Laravel:
             * 1. Creates a session for the user
             * 2. Stores user ID in session
             * 3. Regenerates session ID (security against session fixation)
             * 4. Sets authentication cookies
             * 
             * regenerate() prevents session fixation attacks by creating
             * a new session ID after successful login.
             */
            $request->session()->regenerate();

            /**
             * REDIRECT WITH SUCCESS MESSAGE
             * ===========================
             * 
             * intended() redirects to the page the user was trying to access
             * before being redirected to login. If no intended page exists,
             * it falls back to the dashboard.
             * 
             * with() adds a flash message that will be available on the next page.
             * This message will automatically be passed to your Svelte components
             * via the Inertia middleware we configured earlier.
             */
            return redirect()->intended('/dashboard')->with('success', 
                'Welcome back, ' . Auth::user()->name . '!'
            );
        }

        /**
         * FAILED LOGIN - SECURITY AND UX
         * =============================
         * 
         * When login fails, we:
         * 1. Throw a validation exception with a generic error message
         * 2. Don't specify whether email or password was wrong (security)
         * 3. Return to login page with error displayed
         * 
         * SECURITY NOTE:
         * We use a generic message "These credentials do not match our records"
         * instead of "Wrong password" or "Email not found" to prevent
         * attackers from determining which email addresses are registered.
         */
        throw ValidationException::withMessages([
            'email' => ['These credentials do not match our records.'],
        ]);
    }

    /**
     * HANDLE USER REGISTRATION
     * =======================
     * 
     * Process new user registration.
     * This demonstrates user creation with validation and automatic login.
     * 
     * EDUCATIONAL FLOW:
     * 1. Validate registration form inputs
     * 2. Create new user record in database
     * 3. Automatically log the user in
     * 4. Redirect to dashboard with welcome message
     */
    public function register(Request $request): RedirectResponse
    {
        /**
         * REGISTRATION VALIDATION - COMPREHENSIVE CHECKS
         * =============================================
         * 
         * Registration requires more thorough validation than login:
         * - Name: Required, string, reasonable length
         * - Email: Required, valid format, unique in database
         * - Password: Required, minimum length, confirmed
         * 
         * VALIDATION RULES EXPLAINED:
         * - 'unique:users': Ensures email doesn't already exist
         * - 'confirmed': Checks that password and password_confirmation match
         * - 'max:255': Prevents extremely long names that could break UI
         */
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /**
         * USER CREATION - SECURE ACCOUNT SETUP
         * ===================================
         * 
         * User::create() safely creates a new user account:
         * 1. Mass assignment protection ensures only fillable fields are set
         * 2. Password is automatically hashed (see User model $casts)
         * 3. Timestamps (created_at, updated_at) are set automatically
         * 4. User ID is auto-generated by database
         * 
         * SECURITY FEATURES:
         * - Passwords are hashed using bcrypt (secure, slow, salted)
         * - Only validated, fillable fields can be set
         * - Database constraints prevent duplicate emails
         */
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Auto-hashed by model
        ]);

        /**
         * AUTO-LOGIN AFTER REGISTRATION
         * ============================
         * 
         * After successful registration, we automatically log the user in.
         * This provides a smooth user experience - no need to login again.
         * 
         * Auth::login() manually logs in a user without password verification.
         * This is safe here because we just created the account.
         */
        Auth::login($user);

        /**
         * SESSION SECURITY
         * ===============
         * 
         * Regenerate session ID after login for security.
         * This prevents session fixation attacks.
         */
        $request->session()->regenerate();

        /**
         * WELCOME THE NEW USER
         * ===================
         * 
         * Redirect to dashboard with personalized welcome message.
         * This creates a positive first impression and confirms successful registration.
         */
        return redirect('/dashboard')->with('success', 
            'Welcome to our blog, ' . $user->name . '! Your account has been created successfully.'
        );
    }

    /**
     * HANDLE LOGOUT
     * ============
     * 
     * Log the user out and clean up their session.
     * This demonstrates proper session cleanup and security.
     * 
     * EDUCATIONAL NOTE:
     * Logout is more than just "forgetting" the user - it requires
     * proper cleanup to prevent security issues and ensure a clean state.
     */
    public function logout(Request $request): RedirectResponse
    {
        /**
         * LOGOUT PROCESS - SECURITY CLEANUP
         * ================================
         * 
         * Proper logout requires several steps:
         * 1. Remove authentication from session
         * 2. Invalidate the entire session
         * 3. Regenerate CSRF token
         * 4. Clear authentication cookies
         * 
         * This prevents:
         * - Session hijacking
         * - Unauthorized access from shared computers
         * - CSRF attacks using old tokens
         */
        
        // Step 1: Remove user authentication
        Auth::logout();

        // Step 2: Invalidate the session (clears all session data)
        $request->session()->invalidate();

        // Step 3: Regenerate CSRF token for security
        $request->session()->regenerateToken();

        /**
         * REDIRECT AFTER LOGOUT
         * ====================
         * 
         * Redirect to home page with confirmation message.
         * This provides user feedback and returns them to a safe, public page.
         */
        return redirect('/')->with('success', 'You have been logged out successfully.');
    }

    /**
     * SHOW DASHBOARD (AUTHENTICATED USERS ONLY)
     * ========================================
     * 
     * Display the user dashboard after successful login.
     * This page requires authentication (protected by 'auth' middleware).
     * 
     * EDUCATIONAL NOTE:
     * This demonstrates how to create protected pages that only
     * authenticated users can access. The 'auth' middleware
     * automatically redirects unauthenticated users to login.
     */
    public function dashboard(): Response
    {
        /**
         * GETTING AUTHENTICATED USER DATA
         * ==============================
         * 
         * Auth::user() returns the currently authenticated user.
         * This is available because the user passed through auth middleware.
         * 
         * We're also demonstrating relationship loading - getting the user's
         * blog posts to show on their dashboard.
         */
        $user = Auth::user();
        
        /**
         * EAGER LOADING RELATIONSHIPS
         * ==========================
         * 
         * load() fetches related data efficiently.
         * This prevents N+1 query problems and provides better performance.
         * 
         * We're loading:
         * - User's blog posts
         * - Ordered by newest first
         * - Limited to recent posts for dashboard display
         */
        $user->load(['blogPosts' => function ($query) {
            $query->latest()->take(5); // Show 5 most recent posts
        }]);

        return Inertia::render('Auth/Dashboard', [
            /**
             * DASHBOARD DATA
             * =============
             * 
             * Pass user statistics and recent posts to the Svelte dashboard.
             * This data will be available as props in your Dashboard component.
             */
            'user' => $user,
            'stats' => [
                'totalPosts' => $user->blogPosts()->count(),
                'publishedPosts' => $user->publishedPosts()->count(),
                'draftPosts' => $user->draftPosts()->count(),
                'totalViews' => 0, // TODO: Implement view tracking
            ],
            'recentPosts' => $user->blogPosts,
        ]);
    }
}
