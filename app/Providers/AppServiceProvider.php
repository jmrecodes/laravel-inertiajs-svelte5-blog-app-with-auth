<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\BlogPost;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * ALTERNATIVE: CUSTOM ROUTE MODEL BINDING WITH ERROR HANDLING
         * ==========================================================
         * 
         * This is an alternative approach to handle route model binding
         * with custom error handling. You can use this instead of the
         * Route::bind() in web.php if you want more control.
         * 
         * Uncomment the code below to use this approach:
         */
        
        // Route::bind('post', function ($value) {
        //     try {
        //         // Try to find by slug first (most common case)
        //         $post = BlogPost::where('slug', $value)->first();
        //         
        //         // If not found by slug and value is numeric, try by ID
        //         if (!$post && is_numeric($value)) {
        //             $post = BlogPost::find($value);
        //         }
        //         
        //         // If still not found, you could log this for monitoring
        //         if (!$post) {
        //             \Log::warning('Blog post not found', [
        //                 'searched_value' => $value,
        //                 'ip' => request()->ip(),
        //                 'user_agent' => request()->userAgent()
        //             ]);
        //             
        //             // Throw ModelNotFoundException which will be caught by exception handler
        //             throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        //         }
        //         
        //         return $post;
        //     } catch (\Exception $e) {
        //         // Re-throw as ModelNotFoundException for consistent handling
        //         throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        //     }
        // });
    }
}
