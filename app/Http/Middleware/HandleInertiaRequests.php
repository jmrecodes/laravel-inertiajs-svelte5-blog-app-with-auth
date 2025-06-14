<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * INERTIA REQUESTS MIDDLEWARE - THE BRIDGE BETWEEN LARAVEL AND SVELTE
 * ====================================================================
 * 
 * This middleware is the heart of the Inertia.js magic! It acts as a bridge
 * between your Laravel backend and Svelte frontend. Think of it as a translator
 * that speaks both languages.
 * 
 * WHAT THIS MIDDLEWARE DOES:
 * 1. Shares data between backend and frontend automatically
 * 2. Handles partial page reloads (AJAX-like requests)
 * 3. Manages asset versioning for cache busting
 * 4. Provides the root template for your SPA
 * 
 * EDUCATIONAL NOTE:
 * Every request to your app goes through this middleware when using Inertia.
 * It decides what data your Svelte components will receive as props.
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * ROOT TEMPLATE - THE HTML SHELL FOR YOUR SPA
     * ===========================================
     * 
     * This is the name of the Blade template that wraps your entire Svelte app.
     * It's only loaded once (on the first page visit), then Inertia handles
     * all subsequent navigation without full page reloads.
     * 
     * The template file should be located at: resources/views/app.blade.php
     * 
     * @var string
     */
    protected $rootView = 'app';

    /**
     * ASSET VERSIONING - CACHE BUSTING FOR PRODUCTION
     * ===============================================
     * 
     * This method determines when your frontend assets (CSS, JS) have changed.
     * When assets change, Inertia forces a full page reload to get the new files.
     * This prevents users from getting cached old versions of your app.
     * 
     * EDUCATIONAL NOTE:
     * - In development: Usually not important (files change frequently)
     * - In production: Critical for deploying updates
     * 
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        // Use Laravel's default versioning (based on mix-manifest.json or similar)
        // This automatically detects when your compiled assets have changed
        return parent::version($request);
    }

    /**
     * SHARED DATA - GLOBAL PROPS FOR ALL SVELTE COMPONENTS
     * ===================================================
     * 
     * This is where the magic happens! Any data you return here will be
     * automatically available as props in ALL your Svelte page components.
     * 
     * COMMON USE CASES:
     * - Current user information (for authentication)
     * - Flash messages (success/error notifications)
     * - Global settings (app name, feature flags, etc.)
     * - CSRF tokens (for form security)
     * 
     * EDUCATIONAL NOTE:
     * Think of this as "global state" that every page needs to know about.
     * In traditional SPAs, you'd manage this with a state store. With Inertia,
     * Laravel manages it for you and automatically syncs it to the frontend.
     * 
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        return [
            // Include parent shared data (contains Inertia defaults)
            ...parent::share($request),
            
            /**
             * AUTHENTICATION DATA - WHO IS CURRENTLY LOGGED IN?
             * ================================================
             * 
             * This makes the current user's information available to all
             * Svelte components. Essential for:
             * - Showing/hiding UI elements based on login status
             * - Displaying user name in navigation
             * - Conditional rendering of authenticated content
             * 
             * The data structure:
             * - If logged in: { id: 1, name: "John", email: "john@example.com" }
             * - If not logged in: null
             */
            'auth' => [
                // Get the currently authenticated user (or null if not logged in)
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    // Add more user fields as needed for your app
                    'created_at' => $request->user()->created_at,
                ] : null,
            ],

            /**
             * FLASH MESSAGES - TEMPORARY FEEDBACK FOR USERS
             * =============================================
             * 
             * Flash messages are temporary messages stored in the session
             * that appear once and then disappear. Perfect for:
             * - "Post created successfully!" 
             * - "Error: Please fill in all fields"
             * - "Welcome back, John!"
             * 
             * USAGE IN LARAVEL CONTROLLERS:
             * return redirect()->back()->with('success', 'Post created!');
             * 
             * USAGE IN SVELTE:
             * let { flash } = $props(); // Receive as prop
             * {#if flash.success}<div class="alert">{flash.success}</div>{/if}
             */
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'warning' => fn () => $request->session()->get('warning'),
            ],

            /**
             * CSRF TOKEN - SECURITY FOR FORMS
             * ===============================
             * 
             * Cross-Site Request Forgery (CSRF) protection prevents malicious
             * websites from making requests on behalf of your users.
             * 
             * Every form submission needs to include this token to prove
             * the request came from your legitimate frontend.
             * 
             * USAGE IN SVELTE FORMS:
             * <input type="hidden" name="_token" value={$page.props.csrf_token} />
             */
            'csrf_token' => csrf_token(),

            /**
             * APPLICATION METADATA - GLOBAL APP INFORMATION
             * ============================================
             * 
             * Useful information that multiple components might need.
             */
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'environment' => config('app.env'),
                
                // Feature flags for conditional functionality
                'features' => [
                    'registration_enabled' => true, // Allow new user registration
                    'password_reset_enabled' => true, // Allow password resets
                ],
            ],

            /**
             * ERRORS - FORM VALIDATION ERRORS
             * ===============================
             * 
             * When form validation fails, Laravel stores the errors in the session.
             * This makes them available to your Svelte components for display.
             * 
             * USAGE IN SVELTE:
             * let { errors } = $props();
             * {#if errors.email}<span class="error">{errors.email}</span>{/if}
             */
            'errors' => fn () => $request->session()->get('errors') 
                ? $request->session()->get('errors')->getBag('default')->getMessages() 
                : (object) [],
        ];
    }
}
