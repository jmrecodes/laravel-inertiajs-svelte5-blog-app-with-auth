<?php

/**
 * LARAVEL 12 APPLICATION BOOTSTRAP - THE FOUNDATION
 * =================================================
 * 
 * This file is the foundation of your Laravel application. It configures:
 * - Routing (which URLs map to which code)
 * - Middleware (security and request processing)
 * - Exception handling (error management)
 * 
 * EDUCATIONAL NOTE:
 * Laravel 12 introduced a new, cleaner way to configure applications.
 * Instead of separate Kernel classes, everything is configured here
 * in a more straightforward, functional style.
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    /**
     * ROUTING CONFIGURATION - URL TO CODE MAPPING
     * ===========================================
     * 
     * This tells Laravel where to find your route definitions:
     * - 'web' routes: For web pages (what users see in browsers)
     * - 'commands' routes: For CLI commands (terminal tasks)
     * - 'health' route: Built-in health check endpoint
     */
    ->withRouting(
        // Web routes - our Inertia pages will be defined here
        web: __DIR__.'/../routes/web.php',
        
        // Console commands - for background tasks, database seeding, etc.
        commands: __DIR__.'/../routes/console.php',
        
        // Health check endpoint - useful for monitoring
        health: '/up',
    )
    
    /**
     * MIDDLEWARE CONFIGURATION - REQUEST PROCESSING PIPELINE
     * =====================================================
     * 
     * Middleware processes every request before it reaches your controllers.
     * Think of it as a security checkpoint and data processor.
     * 
     * For our educational blog app, we need:
     * - Authentication middleware (who is logged in?)
     * - CSRF protection (prevent fake requests)
     * - Inertia middleware (bridge to Svelte frontend)
     */
    ->withMiddleware(function (Middleware $middleware) {
        /**
         * WEB MIDDLEWARE GROUP - FOR OUR INERTIA PAGES
         * ===========================================
         * 
         * The 'web' middleware group is automatically applied to routes
         * defined in routes/web.php. Here we add our Inertia middleware
         * to this group so it runs on every page request.
         */
        $middleware->web(append: [
            /**
             * INERTIA MIDDLEWARE - THE BRIDGE TO SVELTE
             * ========================================
             * 
             * This middleware handles the communication between Laravel
             * and our Svelte frontend. It:
             * 
             * 1. Shares authentication data with every page
             * 2. Handles partial page reloads (AJAX-like navigation)
             * 3. Manages flash messages and form errors
             * 4. Provides CSRF tokens for form security
             * 
             * EDUCATIONAL NOTE:
             * This is what makes Inertia.js work! Without this middleware,
             * your Svelte components wouldn't receive any data from Laravel.
             */
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        /**
         * AUTHENTICATION MIDDLEWARE ALIASES
         * ================================
         * 
         * Laravel 12 provides built-in aliases for common middleware including:
         * - 'auth' (Authenticate)
         * - 'guest' (RedirectIfAuthenticated) 
         * - 'verified' (EnsureEmailIsVerified)
         * - And many more...
         * 
         * Since these are provided by default, we only need to define custom aliases here.
         * See: https://laravel.com/docs/12.x/middleware#middleware-aliases
         */
        
        // Currently no custom middleware aliases needed for our educational blog app
        // All authentication middleware use Laravel's built-in aliases
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /**
         * GLOBAL EXCEPTION HANDLING FOR MODEL NOT FOUND
         * ============================================
         * 
         * This handles all ModelNotFoundException errors globally,
         * which occur when route model binding fails to find a model.
         * 
         * For example, when someone visits /posts/invalid-slug-here
         * and no blog post with that slug exists.
         */
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            // 404 page for blog post not found
            return Inertia::render('Errors/PostNotFound', [
                'title' => 'Blog Post Not Found',
                'message' => 'The blog post you are looking for does not exist or may have been removed.',
                'suggestions' => \App\Models\BlogPost::published()
                    ->latest()
                    ->take(3)
                    ->get(['id', 'title', 'slug', 'excerpt']),
                'searchedSlug' => request()->route('post') // Get the invalid slug that was searched
            ])->toResponse($request)->setStatusCode(404);
        });
    })->create();
