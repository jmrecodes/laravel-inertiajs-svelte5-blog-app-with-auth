<?php

/**
 * WEB ROUTES - EDUCATIONAL BLOG CRUD APPLICATION
 * =============================================
 * 
 * This file defines all the web routes for our educational blog application.
 * Routes are the URLs that users can visit and what code should handle them.
 * 
 * 🎓 BEGINNER LEARNING OBJECTIVES:
 * ================================
 * 1. **RESTful Routing Patterns**: Understanding standard URL conventions for web resources.
 * 2. **Route Groups & Middleware**: Organizing routes and applying access control.
 * 3. **Route Model Binding**: Automatically injecting database records into controller methods.
 * 4. **Named Routes**: Creating easy-to-reference aliases for URLs.
 * 5. **Authentication Protection**: Securing routes based on user login status.
 * 6. **Inertia.js Integration**: How Laravel routes render Svelte components.
 * 
 * 🔍 WHAT YOU'LL LEARN:
 * =====================
 * - How to define basic GET, POST, PUT, DELETE routes.
 * - Grouping related routes for better organization (e.g., authentication, blog posts).
 * - Applying middleware like `auth` and `guest` for access control.
 * - Using `Inertia::render()` to serve Svelte components from Laravel.
 * - How route model binding simplifies fetching data from the database.
 * - Best practices for naming routes for maintainability.
 * 
 * ROUTE MIDDLEWARE EXPLAINED:
 * - `web`: Laravel's default middleware for web applications (handles sessions, CSRF protection, cookies).
 * - `auth`: Ensures only authenticated (logged-in) users can access the route. If not logged in, redirects to the login page.
 * - `guest`: Ensures only *non-authenticated* (guest) users can access the route. If logged in, redirects to the dashboard.
 * 
 * WHY WE USE NAMED ROUTES (e.g., `->name('home')`):
 * - **Flexibility**: If a URL changes, you only update it in one place (the route definition), not everywhere it's used.
 * - **Readability**: `route('home')` is much clearer than hardcoding `'/'`.
 * - **Maintainability**: Easier to refactor and debug large applications.
 * - **Type Safety**: IDEs can often provide auto-completion and validation for named routes.
 * 
 * This file is heavily commented to guide you through each concept
 * and pattern involved in defining web routes for your application.
 */

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController; // Import ProfileController
use App\Http\Controllers\Auth\ForgotPasswordController; // Import ForgotPasswordController

/**
 * HOME PAGE ROUTE
 * ===============
 * 
 * This route handles the root URL (`/`) of our application and displays the welcome page.
 * It's the first page visitors see.
 * 
 * 🎓 LEARNING OBJECTIVES:
 * =====================
 * - **Basic Route Definition**: How to define a simple GET route.
 * - **Inertia.js Rendering**: Using `Inertia::render()` to serve a Svelte component.
 * - **Passing Props**: Sending data from the Laravel backend to the Svelte frontend.
 * 
 * ROUTE: `GET /`
 * SVELTE COMPONENT: `resources/js/Pages/Home.svelte`
 */
Route::get('/', function () {
    return Inertia::render('Home', [
        'welcome' => [
            'title' => 'Welcome to Educational Blog',
            'subtitle' => 'Learn Svelte 5 + Inertia.js + Laravel',
            'description' => 'This is a comprehensive educational application demonstrating modern web development with detailed comments and explanations.',
        ],
        'features' => [
            'Authentication with Laravel Sessions',
            'CRUD Operations with Eloquent ORM', 
            'Reactive UI with Svelte 5',
            'Seamless Navigation with Inertia.js',
            'Modern Styling with Tailwind CSS',
            'Comprehensive Educational Comments',
        ],
    ]);
})->name('home');

/**
 * =======================================================================
 * AUTHENTICATION ROUTES - LOGIN, REGISTER, LOGOUT
 * =======================================================================
 * 
 * This section defines all routes related to user authentication.
 * Routes are logically grouped and protected by appropriate middleware.
 * 
 * 🎓 EDUCATIONAL CONCEPTS:
 * - `guest` middleware: Routes only accessible to non-logged-in users.
 * - `auth` middleware: Routes only accessible to logged-in users.
 * - POST requests for sensitive actions (login, logout) for security.
 */

/**
 * GUEST-ONLY AUTHENTICATION ROUTES
 * ================================
 * 
 * These routes are only accessible to users who are *not* logged in.
 * If an authenticated user tries to access these, they will be redirected
 * to the `/dashboard` route (handled by the `guest` middleware).
 */
Route::middleware('guest')->group(function () {
    /**
     * SHOW LOGIN FORM
     * ==============
     * 
     * Displays the login form to the user.
     * 
     * ROUTE: `GET /login`
     * SVELTE COMPONENT: `resources/js/Pages/Auth/Login.svelte`
     * NAMED ROUTE: `login` (Laravel's authentication system expects this name).
     */
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    /**
     * PROCESS LOGIN FORM SUBMISSION
     * ============================
     * 
     * Handles the login attempt when the user submits the form.
     * 
     * FLOW:
     * 1. User submits email/password to this route.
     * 2. `AuthController::login()` attempts to authenticate the user.
     * 3. On success: User session is created, and user is redirected to `/dashboard`.
     * 4. On failure: Validation errors are returned to the login form.
     * 
     * ROUTE: `POST /login`
     */
    Route::post('/login', [AuthController::class, 'login']);

    /**
     * SHOW REGISTRATION FORM
     * =====================
     * 
     * Displays the user registration form.
     * 
     * ROUTE: `GET /register`
     * SVELTE COMPONENT: `resources/js/Pages/Auth/Register.svelte`
     * NAMED ROUTE: `register`
     */ 
    if (env('APP_ENV') === 'local' || env('APP_ENV') === 'development') {
        Route::get('/register', [AuthController::class, 'showRegister'])
            ->name('register');
    }

    /**
     * PROCESS REGISTRATION FORM SUBMISSION
     * ====================================
     * 
     * Handles the new user registration when the form is submitted.
     * 
     * FLOW:
     * 1. User submits name, email, password to this route.
     * 2. `AuthController::register()` validates data, creates user, and logs them in.
     * 3. On success: User is automatically logged in and redirected to `/dashboard`.
     * 4. On failure: Validation errors are returned to the registration form.
     * 
     * ROUTE: `POST /register`
     */
    Route::post('/register', [AuthController::class, 'register']);
});

/**
 * AUTHENTICATED-ONLY ROUTES
 * =========================
 * 
 * These routes require the user to be logged in (`auth` middleware).
 * If a non-authenticated user tries to access these, they will be redirected
 * to the `/login` page (handled by the `auth` middleware).
 */
Route::middleware('auth')->group(function () {
    /**
     * USER DASHBOARD
     * =============
     * 
     * Displays the user's personal dashboard after successful login.
     * 
     * FEATURES:
     * - Personalized welcome message
     * - Summary of user's blog posts and statistics
     * - Quick links to manage posts or create new ones
     * 
     * ROUTE: `GET /dashboard`
     * SVELTE COMPONENT: `resources/js/Pages/Auth/Dashboard.svelte`
     * NAMED ROUTE: `dashboard`
     */
    Route::get('/dashboard', [AuthController::class, 'dashboard'])
        ->name('dashboard');

    /**
     * LOGOUT USER
     * ============
     * 
     * Handles the user logout process, destroying their session.
     * 
     * SECURITY NOTE:
     * - Uses a `POST` request to prevent CSRF attacks (GET requests can be easily forged).
     * - Clears the user's session and regenerates the CSRF token.
     * 
     * ROUTE: `POST /logout`
     * NAMED ROUTE: `logout`
     */
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
        
    /**
     * PROFILE MANAGEMENT ROUTES
     * ========================
     * 
     * Group of routes for user profile management functionality.
     * All routes within this group require authentication.
     */
    
    /**
     * SHOW PROFILE EDIT FORM
     * =====================
     * 
     * Displays the user's profile information in an editable form.
     * 
     * FEATURES:
     * - Displays current user information (name, email)
     * - Shows account statistics (total posts, published, drafts, member since)
     * - Provides forms for updating name/email, changing password, and account deletion.
     * 
     * ROUTE: `GET /profile`
     * SVELTE COMPONENT: `resources/js/Pages/Profile/Edit.svelte`
     * NAMED ROUTE: `profile.edit`
     */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    /**
     * UPDATE PROFILE INFORMATION
     * =========================
     * 
     * Handles the submission to update the user's name and email.
     * 
     * SECURITY FEATURES:
     * - Email uniqueness validation (excluding current user's email).
     * - Comprehensive input validation.
     * - Laravel's mass assignment protection prevents unauthorized field updates.
     * 
     * FLOW:
     * Validate data → Update user record → Redirect with success message.
     * 
     * ROUTE: `PUT /profile`
     * NAMED ROUTE: `profile.update`
     */
    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    /**
     * CHANGE PASSWORD
     * ==============
     * 
     * Handles requests to update the user's password.
     * 
     * ENHANCED SECURITY:
     * - Requires verification of the current password.
     * - Enforces strong password requirements (min length, letters, numbers).
     * - Requires password confirmation.
     * - Uses a separate route/method from general profile updates for increased security.
     * 
     * ROUTE: `PUT /profile/password`
     * NAMED ROUTE: `profile.password.update`
     */
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password.update');

    /**
     * DELETE ACCOUNT
     * =============
     * 
     * Allows a user to permanently delete their account.
     * 
     * WARNING: This is an **irreversible and destructive** action.
     * 
     * SECURITY CONSIDERATIONS:
     * - Requires password re-confirmation for security.
     * - Deletes all associated data (e.g., blog posts) in this educational app.
     * - Logs out the user automatically.
     * 
     * PRODUCTION NOTE:
     * In real-world applications, often prefer "soft deletes" (`deleted_at` timestamp)
     * for data recovery and audit trails, instead of permanent deletion.
     * 
     * ROUTE: `DELETE /profile`
     * NAMED ROUTE: `profile.destroy`
     */
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/**
 * =======================================================================
 * FORGOT PASSWORD ROUTES - NO AUTHENTICATION REQUIRED
 * =======================================================================
 * 
 * These routes handle the "forgot password" functionality. They are publicly
 * accessible since a user cannot log in to use them.
 * 
 * 🎓 EDUCATIONAL CONCEPTS:
 * - Token-based password resets: A common secure pattern.
 * - Email sending: How applications communicate with users.
 * - Conditional UI via URL parameters: Showing different forms on the same route.
 * - Rate limiting: Preventing abuse of password reset requests.
 */

/**
 * SHOW FORGOT PASSWORD / RESET PASSWORD FORM
 * =========================================
 * 
 * This single route handles two primary displays based on URL query parameters:
 * 1. Without `token` & `email`: Displays the form to request a password reset link (asks for email).
 * 2. With valid `token` & `email`: Displays the form to set a new password.
 * 3. With invalid/expired token: Shows an error message.
 * 
 * EDUCATIONAL NOTE:
 * Using query parameters (`?token=xxx&email=xxx`) allows the same route
 * and a single Svelte component to manage both stages of the password reset flow efficiently.
 * 
 * ROUTE: `GET /forgot-password`
 * SVELTE COMPONENT: `resources/js/Pages/Auth/ForgotPassword.svelte`
 * NAMED ROUTE: `password.request` (Laravel's auth system often uses this name for consistency).
 */
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])
    ->name('password.request');

/**
 * SEND PASSWORD RESET LINK EMAIL
 * ==============================
 * 
 * Processes the request to send a password reset email.
 * 
 * SECURITY FEATURES:
 * 1. **No User Enumeration**: Always returns a generic success message, regardless of email existence.
 * 2. Generates a secure, random token.
 * 3. Stores the token with an expiration timestamp in the database.
 * 4. Sends an email containing the unique reset link.
 * 5. Applies rate limiting based on IP address to prevent abuse.
 * 
 * FLOW:
 * User enters email → Server validates → Token created → Email sent (or simulated in dev) → User redirected with success message.
 * 
 * ROUTE: `POST /forgot-password`
 * NAMED ROUTE: `password.email`
 * MIDDLEWARE: `throttle:2,1` (allows 2 requests per minute per IP for this route to prevent spam).
 */
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
    ->name('password.email')
    ->middleware('throttle:2,1');

/**
 * RESET PASSWORD WITH TOKEN
 * =========================
 * 
 * Processes the new password submission after a user clicks the reset link.
 * 
 * SECURITY VALIDATION:
 * 1. Verifies the provided token exists, is valid, and matches the email.
 * 2. Checks that the token has not expired (typically 1 hour).
 * 3. Ensures the new password meets strength requirements and is confirmed.
 * 4. Invalidates (deletes) the token after successful use to prevent reuse.
 * 
 * FLOW:
 * Validate token & new password → Update user password → Delete token → Redirect to login with success message.
 * 
 * ROUTE: `PUT /forgot-password`
 * NAMED ROUTE: `password.update`
 */
Route::put('/forgot-password', [ForgotPasswordController::class, 'resetPassword'])
    ->name('password.update');

/**
 * =======================================================================
 * BLOG POST ROUTES - ORGANIZED BY ACCESS LEVEL
 * =======================================================================
 * 
 * For educational clarity, we group blog-related routes by who can access them:
 * 1. **PUBLIC ROUTES**: Anyone can access (no authentication required).
 * 2. **AUTHENTICATED ROUTES**: Only logged-in users can access.
 * 
 * This makes it easier to understand the application's permission structure for content.
 */

/**
 * PUBLIC BLOG ROUTES - NO AUTHENTICATION REQUIRED
 * ===============================================
 * 
 * These routes display published blog content to everyone, including guests.
 * They are optimized for SEO and public content consumption.
 */

/**
 * BLOG POST LISTING PAGE
 * =====================
 * 
 * Displays a paginated list of all published blog posts.
 * 
 * FEATURES:
 * - Pagination for efficient loading (10 posts per page).
 * - Search functionality across post titles, content, and excerpts.
 * - Only shows posts with a 'published' status.
 * - Includes author information for each post.
 * - SEO-friendly with proper meta tags (handled in Svelte component).
 * 
 * EXAMPLE URLs:
 * `/posts`                       → First page of posts.
 * `/posts?page=2`                → Second page of posts.
 * `/posts?search=laravel`        → Search for posts containing "laravel".
 * `/posts?search=tutorial&page=2` → Search with pagination.
 * 
 * ROUTE: `GET /posts`
 * CONTROLLER METHOD: `BlogPostController::index()`
 * SVELTE COMPONENT: `resources/js/Pages/BlogPosts/Index.svelte`
 * NAMED ROUTE: `posts.index`
 */
Route::get('/posts', [BlogPostController::class, 'index'])
    ->name('posts.index');

/**
 * AUTHENTICATED BLOG ROUTES - LOGIN REQUIRED
 * ==========================================
 * 
 * These routes are exclusively for authenticated (logged-in) users to create and manage blog posts.
 * The `auth` middleware ensures that only authorized users can access them.
 * If a non-authenticated user attempts to access these, they will be redirected to the login page.
 */
Route::middleware('auth')->group(function () {
    
    /**
     * SHOW NEW POST CREATION FORM
     * ===========================
     * 
     * Displays the form for creating a brand new blog post.
     * 
     * FEATURES:
     * - Rich text editing capabilities.
     * - Fields for SEO optimization (meta title, meta description, featured image).
     * - Post status selection (draft or published).
     * - Automatic slug generation from the post title (client-side).
     * - Real-time character counters and client-side validation.
     * 
     * EDUCATIONAL NOTE ON ROUTE ORDER:
     * This route (`/posts/create`) must be defined *before* the dynamic `{post:slug}` route
     * (`/posts/{post:slug}`). If `/posts/{post:slug}` came first, Laravel might incorrectly
     * interpret "create" as a post slug, leading to unexpected behavior.
     * 
     * ROUTE: `GET /posts/create`
     * CONTROLLER METHOD: `BlogPostController::create()`
     * SVELTE COMPONENT: `resources/js/Pages/BlogPosts/Create.svelte`
     * NAMED ROUTE: `posts.create`
     */
    Route::get('/posts/create', [BlogPostController::class, 'create'])
        ->name('posts.create');

    /**
     * SAVE NEW BLOG POST
     * ==================
     * 
     * Handles the form submission to save a new blog post to the database.
     * 
     * SECURITY & VALIDATION:
     * - Comprehensive server-side validation of all form inputs.
     * - Automatic association of the post with the currently authenticated user (prevents spoofing).
     * - Ensures slug uniqueness.
     * - Protection against Cross-Site Scripting (XSS) via Laravel's built-in sanitization.
     * 
     * FLOW:
     * Validate data → Create new `BlogPost` record → Redirect to the newly created post's view page with a success message.
     * 
     * ROUTE: `POST /posts`
     * CONTROLLER METHOD: `BlogPostController::store()`
     * NAMED ROUTE: `posts.store`
     */
    Route::post('/posts', [BlogPostController::class, 'store'])
        ->name('posts.store');

    /**
     * SHOW EDIT POST FORM
     * ===================
     * 
     * Displays the pre-filled form for editing an existing blog post.
     * 
     * AUTHORIZATION:
     * - Only the original author of the post can access this edit form.
     * - The `BlogPostController::edit()` method includes logic to enforce this ownership.
     * 
     * ROUTE: `GET /posts/{post}/edit`
     * CONTROLLER METHOD: `BlogPostController::edit()`
     * SVELTE COMPONENT: `resources/js/Pages/BlogPosts/Edit.svelte`
     * NAMED ROUTE: `posts.edit`
     */
    Route::get('/posts/{post}/edit', [BlogPostController::class, 'edit'])
        ->name('posts.edit');

    /**
     * UPDATE EXISTING BLOG POST
     * ========================
     * 
     * Handles the form submission to update an existing blog post in the database.
     * 
     * FEATURES:
     * - Preserves post ownership (cannot change author via update).
     * - Validates slug uniqueness, allowing the current post's slug to be ignored.
     * - Automatically updates `published_at` timestamp if a draft is published.
     * - Provides context-specific success messages based on the post's new status.
     * 
     * ROUTE: `PUT /posts/{post}`
     * CONTROLLER METHOD: `BlogPostController::update()`
     * NAMED ROUTE: `posts.update`
     */
    Route::put('/posts/{post}', [BlogPostController::class, 'update'])
        ->name('posts.update');

    /**
     * DELETE BLOG POST
     * ================
     * 
     * Handles the request to permanently delete a blog post from the database.
     * 
     * WARNING: This is an **irreversible and destructive** operation.
     * 
     * SECURITY CONSIDERATIONS:
     * - Only the post's original author can delete their post.
     * - Frontend should implement a confirmation step to prevent accidental deletion.
     * 
     * PRODUCTION NOTE:
     * In real-world applications, consider implementing "soft deletes" (where a `deleted_at` timestamp is set)
     * instead of permanent deletion. This allows for data recovery and maintains an audit trail.
     * 
     * ROUTE: `DELETE /posts/{post}`
     * CONTROLLER METHOD: `BlogPostController::destroy()`
     * NAMED ROUTE: `posts.destroy`
     */
    Route::delete('/posts/{post}', [BlogPostController::class, 'destroy'])
        ->name('posts.destroy');
        
    /**
     * AUTHOR'S POST MANAGEMENT DASHBOARD
     * =================================
     * 
     * Displays a personal dashboard for the authenticated user to manage *all* of their blog posts.
     * This includes both published and draft posts, along with management actions.
     * 
     * CONTRAST WITH `/posts` (Public Blog Listing):
     * - Public `/posts` shows ONLY published posts to everyone.
     * - This `/manage-posts` shows ALL posts (published, drafts, archived) belonging to the logged-in user.
     * 
     * FEATURES:
     * - List of all user's posts with status indicators.
     * - Quick actions like edit, view (if published), and delete.
     * - Basic post statistics (total, published, drafts).
     * 
     * ROUTE: `GET /manage-posts`
     * CONTROLLER METHOD: `BlogPostController::manage()`
     * SVELTE COMPONENT: `resources/js/Pages/BlogPosts/Manage.svelte`
     * NAMED ROUTE: `posts.manage`
     */
    Route::get('/manage-posts', [BlogPostController::class, 'manage'])
        ->name('posts.manage');
});

/**
 * SHOW SINGLE BLOG POST
 * =====================
 * 
 * Displays a single blog post, identified by its unique slug (SEO-friendly URL).
 * This is the public view for reading individual blog posts.
 * 
 * 🎓 LEARNING OBJECTIVES:
 * ======================
 * 1. **Route Model Binding**: Automatic model lookup based on URL parameters.
 * 2. **Slug-based URLs**: Creating SEO-friendly URLs instead of using database IDs.
 * 3. **Authorization Logic**: Controlling access based on post status and ownership.
 * 4. **Meta Tags & SEO**: Dynamically setting page metadata for search engines.
 * 5. **Shared Components**: Reusing UI elements between different page types.
 * 
 * FEATURES:
 * - Clean, readable URLs (e.g., `/posts/my-first-blog-post` instead of `/posts/1`).
 * - Smart access control: Published posts are public, drafts are only visible to authors.
 * - Rich metadata for SEO and social media sharing.
 * - Related post suggestions (can be added later).
 * - Comment system integration (can be added later).
 * 
 * ROUTE MODEL BINDING EXPLANATION:
 * - `{post:slug}` tells Laravel to automatically find a `BlogPost` model
 *   by its `slug` field instead of the default `id`. This creates clean, readable URLs.
 * 
 * URL EXAMPLES:
 * `/posts/my-first-blog-post`  ✅ (SEO-friendly, human-readable)
 * `/posts/123`                 ❌ (would work with default binding, but not SEO-friendly)
 * 
 * AUTHORIZATION LOGIC:
 * - Published posts: Accessible to anyone (public access).
 * - Draft/Archived posts: Only accessible to the original author (for previewing their work).
 * 
 * EDUCATIONAL NOTE ON ROUTE ORDER:
 * This route is placed after the authenticated blog routes to ensure proper route matching order.
 * Laravel processes routes from top to bottom, so specific routes (like /posts/create) 
 * must come before more general dynamic routes (like /posts/{post:slug}).
 * 
 * ROUTE: `GET /posts/{post:slug}`
 * CONTROLLER METHOD: `BlogPostController::show()`
 * SVELTE COMPONENT: `resources/js/Pages/BlogPosts/Show.svelte`
 * NAMED ROUTE: `posts.show`
 */
Route::get('/posts/{post:slug}', [BlogPostController::class, 'show'])
    ->name('posts.show');

/**
 * =======================================================================
 * LEGAL COMPLIANCE ROUTES
 * =======================================================================
 * 
 * These routes handle legal compliance pages that every web application needs.
 * They demonstrate proper legal documentation patterns and user transparency.
 * 
 * 🎓 EDUCATIONAL CONCEPTS:
 * - Legal compliance in web applications
 * - User trust and transparency
 * - GDPR and privacy law requirements
 * - Professional web development practices
 * 
 * WHY THESE PAGES ARE IMPORTANT:
 * - Required by law in many jurisdictions (GDPR, CCPA, etc.)
 * - Build user trust through transparency
 * - Protect both users and developers legally
 * - Demonstrate professional development practices
 * - Required by app stores and many platforms
 * 
 * PUBLIC ACCESS:
 * These pages are accessible to everyone (no authentication required)
 * because users need to understand terms before creating accounts.
 */

/**
 * TERMS OF SERVICE PAGE
 * ====================
 * 
 * Displays the Terms of Service page.
 * 
 * Every web application should have clear terms that define:
 * - User responsibilities and acceptable use.
 * - Service limitations and disclaimers.
 * - Account termination conditions.
 * - Intellectual property rights.
 * 
 * EDUCATIONAL CONTEXT:
 * This page shows students how to implement legal compliance
 * while maintaining good user experience and clear communication.
 * 
 * ROUTE: `GET /terms`
 * CONTROLLER METHOD: `LegalController::terms()`
 * SVELTE COMPONENT: `resources/js/Pages/Legal/Terms.svelte`
 * NAMED ROUTE: `legal.terms`
 */
Route::get('/terms', [LegalController::class, 'terms'])
    ->name('legal.terms');

/**
 * PRIVACY POLICY PAGE
 * ==============
 * 
 * Displays the Privacy Policy page.
 * 
 * Required by privacy laws (GDPR, CCPA, etc.) and essential for user trust.
 * Must explain:
 * - What data is collected and why.
 * - How data is used and protected.
 * - User rights and control options.
 * - Contact information for privacy concerns.
 * 
 * EDUCATIONAL CONTEXT:
 * Demonstrates responsible data handling practices and shows students
 * how to communicate privacy practices clearly to users.
 * 
 * ROUTE: `GET /privacy`
 * CONTROLLER METHOD: `LegalController::privacy()`
 * SVELTE COMPONENT: `resources/js/Pages/Legal/Privacy.svelte`
 * NAMED ROUTE: `legal.privacy`
 */
Route::get('/privacy', [LegalController::class, 'privacy'])
    ->name('legal.privacy');

/**
 * =======================================================================
 * UTILITY AND ERROR ROUTES
 * =======================================================================
 * 
 * This section defines miscellaneous utility routes and custom error handling.
 */

/**
 * ABOUT PAGE
 * ==========
 * 
 * Displays an informational page about this educational application.
 * 
 * This page explains the purpose and features of the application,
 * technologies used, and its educational value. Useful for visitors to understand
 * what they're looking at and learning from.
 * 
 * ROUTE: `GET /about`
 * SVELTE COMPONENT: `resources/js/Pages/About.svelte` (implicit, as it's a simple Inertia render)
 * NAMED ROUTE: `about`
 */
Route::get('/about', function () {
    return Inertia::render('About', [
        'title' => 'About This Educational App',
        'description' => 'This application demonstrates modern web development using Svelte 5, Inertia.js, and Laravel.',
        'technologies' => [
            'Laravel 12' => 'Modern PHP framework for backend',
            'Svelte 5' => 'Reactive frontend framework with runes',
            'Inertia.js' => 'SPA without complexity of traditional APIs',
            'Tailwind CSS' => 'Utility-first CSS framework',
            'SQLite' => 'Simple database for educational purposes',
        ],
        'features' => [
            'Complete authentication system',
            'Full CRUD operations for blog posts',
            'SEO-friendly URLs with slugs',
            'Responsive design with Tailwind',
            'Comprehensive educational comments',
            'Best practices for security',
        ],
    ]);
})->name('about');

/**
 * DEVELOPMENT HELPERS (CONDITIONAL IN LOCAL ENVIRONMENT)
 * =====================================================
 * 
 * These routes provide helpful debugging and development tools.
 * They are strictly conditional and **only available in the local development environment**
 * (`APP_ENV=local` in `.env`). They should *never* be exposed in production.
 */

if (app()->environment('local')) {
    /**
     * ROUTE LIST FOR DEBUGGING
     * ========================
     * 
     * Displays a list of all registered routes in the application.
     * This is an invaluable tool for developers to quickly inspect route names, URIs,
     * methods, actions, and applied middleware.
     * 
     * ROUTE: `GET /routes`
     * SVELTE COMPONENT: `resources/js/Pages/Debug/Routes.svelte` (implicit)
     * NAMED ROUTE: `debug.routes`
     */
    Route::get('/routes', function () {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => $route->getMiddleware(),
            ];
        });

        return Inertia::render('Debug/Routes', [
            'routes' => $routes,
        ]);
    })->name('debug.routes');
}

/**
 * =======================================================================
 * ROUTE MODEL BINDING CUSTOMIZATION
 * =======================================================================
 * 
 * This section customizes how Laravel's Route Model Binding works for specific models.
 * It allows us to use more user-friendly values (like slugs) in URLs instead of database IDs.
 * 
 * 🎓 EDUCATIONAL CONCEPTS:
 * - **Route Model Binding**: Automatically resolving Eloquent models from URL segments.
 * - **Customizing Binding**: Changing the default lookup field (e.g., from `id` to `slug`).
 * - **Error Handling**: Gracefully handling cases where a model is not found.
 */

/**
 * CUSTOM ROUTE MODEL BINDING FOR BLOG POSTS (`{post}` parameter)
 * =============================================================
 * 
 * This tells Laravel how to find a `BlogPost` model when it encounters a `{post}`
 * parameter in a route definition (e.g., `/posts/{post}`).
 * 
 * We customize it to prioritize looking up posts by their `slug` (for SEO-friendly URLs).
 * If a numeric value is provided (like an ID), it will still try to find it by `id`.
 * 
 * BEFORE CUSTOMIZATION: `/posts/123` (finds by ID)
 * AFTER CUSTOMIZATION:  `/posts/my-amazing-blog-post` (finds by slug) OR `/posts/123` (still finds by ID if numeric)
 * 
 * `->firstOrFail()`: If no matching post is found, Laravel will automatically throw a 404 exception,
 *   which our `Route::fallback` handler (below) will catch.
 */
Route::bind('post', function ($value) {
    // If the value looks like a numeric ID (e.g., "123"), try to find the BlogPost by its `id`
    if (is_numeric($value)) {
        return \App\Models\BlogPost::findOrFail($value);
    }
    
    // Otherwise, assume the value is a `slug` (e.g., "my-first-post") and find the BlogPost by its `slug`
    return \App\Models\BlogPost::where('slug', $value)->firstOrFail();
});

/**
 * =======================================================================
 * FALLBACK ROUTE - CUSTOM 404 HANDLING
 * =======================================================================
 * 
 * This route acts as a catch-all. If none of the routes defined above match
 * the incoming URL request, this `fallback` route will be executed.
 * 
 * 🎓 EDUCATIONAL CONCEPTS:
 * - **Graceful Error Handling**: Providing a user-friendly 404 page.
 * - **User Experience**: Guiding users back to valid parts of the application.
 * 
 * ROUTE: `GET /` (Catches any unmatched `GET` request, other methods will still return default Laravel 405/404)
 * SVELTE COMPONENT: `resources/js/Pages/Errors/404.svelte` (implicit render)
 */
Route::fallback(function () {
    return Inertia::render('Errors/404', [
        'title' => 'Page Not Found',
        'message' => 'The page you are looking for does not exist.',
        'suggestions' => [
            ['text' => 'Go to Home Page', 'url' => route('home')],
            ['text' => 'View All Posts', 'url' => route('posts.index')],
            ['text' => 'About This App', 'url' => route('about')],
        ],
    ]);
});

/*
 * 🎓 EDUCATIONAL SUMMARY - WEB ROUTES FILE
 * =======================================
 * 
 * This `web.php` file is crucial for defining the structure and behavior of your application.
 * By understanding these routing concepts, you can build well-organized, secure,
 * and user-friendly web applications.
 * 
 * ✅ KEY LEARNINGS FROM THIS FILE:
 * - **Centralized Routing**: All web URLs are defined in one place.
 * - **Middleware Application**: Protecting routes based on authentication status.
 * - **Clear Separation of Concerns**: Grouping routes logically (auth, blog, legal).
 * - **SEO & UX Friendly URLs**: Using named routes and custom model binding for slugs.
 * - **Robust Error Handling**: Catching unmatched routes with a custom 404 page.
 * - **Inertia.js Integration**: Seamlessly linking Laravel backend to Svelte frontend pages.
 * 
 * This file serves as an excellent reference for anyone learning how to set up
 * routing in a modern Laravel + Inertia.js application.
 */
