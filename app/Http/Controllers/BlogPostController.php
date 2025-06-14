<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

/**
 * BLOG POST CONTROLLER - COMPLETE CRUD OPERATIONS FOR EDUCATIONAL APP
 * ====================================================================
 * 
 * This controller manages all blog post related actions in our application.
 * It's a perfect example of a **CRUD (Create, Read, Update, Delete)** controller
 * in a Laravel application using Inertia.js and Svelte.
 * 
 * 🎓 BEGINNER LEARNING OBJECTIVES:
 * ================================
 * 1. **RESTful API Design**: Understand how controllers map to common web actions.
 * 2. **Database Interactions (Eloquent)**: Learn how to save, retrieve, and modify data.
 * 3. **Form Handling & Validation**: Securely process user input.
 * 4. **User Authentication & Authorization**: Control who can do what.
 * 5. **Single Page Application (SPA) Integration**: How Laravel and Svelte work together via Inertia.js.
 * 6. **Pagination & Searching**: Efficiently display large datasets.
 * 7. **Error Handling & User Feedback**: Provide a good user experience.
 * 
 * 🔍 WHAT YOU'LL LEARN:
 * =====================
 * - How to define controller methods for each CRUD operation.
 * - Using Laravel's powerful Eloquent ORM for database queries.
 * - Implementing data validation rules to protect your application.
 * - Checking user permissions with Laravel's Gate facade.
 * - Rendering Svelte components and passing data (props) to them.
 * - Handling redirects and flash messages after successful actions.
 * 
 * ROUTES MANAGED BY THIS CONTROLLER:
 * - `GET /posts`: List all blog posts (`index` method)
 * - `GET /posts/create`: Show form to create new post (`create` method)
 * - `POST /posts`: Save a new post (`store` method)
 * - `GET /posts/{slug}`: Show a single post (`show` method)
 * - `GET /posts/{post}/edit`: Show form to edit existing post (`edit` method)
 * - `PUT /posts/{post}`: Update an existing post (`update` method)
 * - `DELETE /posts/{post}`: Delete a post (`destroy` method)
 * - `GET /manage-posts`: Show author's post management dashboard (`manage` method)
 *
 * AUTHORIZATION STRATEGY (WHO CAN DO WHAT):
 * - **Public Access**: Anyone can view published blog posts (`index`, `show`).
 * - **Authenticated Users**: Only logged-in users can create new posts.
 * - **Post Authors Only**: Only the original author of a post can edit or delete it.
 * 
 * This controller is heavily commented to guide you through each step
 * and concept involved in building robust CRUD functionality.
 */
class BlogPostController extends Controller
{
    /**
     * DISPLAY LISTING OF BLOG POSTS (PUBLIC VIEW)
     * ==========================================
     * 
     * This method fetches and displays all **published** blog posts for public viewing.
     * It's the main page where visitors can browse content.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Querying Data**: Fetching data from the database using Eloquent.
     * - **Eager Loading**: Loading related data (`user` for author info) to prevent N+1 issues.
     * - **Pagination**: Efficiently displaying large numbers of posts without loading all at once.
     * - **Search & Filtering**: Allowing users to find specific content.
     * - **Inertia.js Rendering**: Sending data to a Svelte component for display.
     * - **SEO**: Setting up meta tags for search engine optimization.
     * 
     * ACCESSIBILITY: Publicly accessible (no authentication required).
     * ROUTE: `GET /posts`
     */
    public function index(Request $request): Response
    {
        /**
         * 🔎 SEARCH AND FILTERING LOGIC
         * ============================
         * 
         * This section demonstrates how to build dynamic database queries
         * based on user input (e.g., search terms).
         * 
         * `BlogPost::with('user')`: Starts a new query for `BlogPost` models
         *   and also fetches the associated `user` (author) for each post.
         *   This is called **eager loading** and prevents multiple database queries.
         * `->published()`: This is a **local scope** defined in the `BlogPost` model.
         *   It automatically adds a `WHERE status = 'published'` clause to the query,
         *   ensuring only public posts are shown.
         */
        $query = BlogPost::with('user') // Always load the author
                         ->published();  // Only show posts with 'published' status

        // Check if a search term is provided in the request (e.g., /posts?search=keyword)
        if ($search = $request->get('search')) {
            /*
             * SEARCH IMPLEMENTATION
             * ====================
             * 
             * We use `where(function ($q) { ... })` to group our `OR` conditions.
             * This ensures the search applies to multiple fields (`title`, `content`, `excerpt`)
             * without breaking other parts of the query.
             * 
             * `like '%{$search}%'`: This is a SQL LIKE operator. The `%` acts as a wildcard,
             *   matching any sequence of zero or more characters. So, `%keyword%` means
             *   "contains keyword".
             */
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        /**
         * 📄 PAGINATION AND PERFORMANCE OPTIMIZATION
         * ==========================================
         * 
         * Pagination is vital for applications with many records.
         * Instead of loading all posts (which can be slow and use lots of memory),
         * we load them in smaller chunks (pages).
         * 
         * `->latest('published_at')`: Orders the posts by their `published_at` date in descending order.
         *   This shows the newest posts first.
         * `->paginate(10)`: Tells Laravel to retrieve 10 posts per page.
         * `->withQueryString()`: Ensures any existing query parameters (like `?search=xyz` or `?page=2`)
         *   are preserved when navigating between pagination links.
         */
        $posts = $query->latest('published_at')
                      ->paginate(10) // Display 10 posts per page
                      ->withQueryString(); // Maintain search/filter parameters across pages

        /*
         * 🚀 RENDERING SVELTE COMPONENT VIA INERTIA.JS
         * ===========================================
         * 
         * `Inertia::render('BlogPosts/Index', [...])`:
         * This sends the data (`posts`, `search`, `meta`) to the
         * `resources/js/Pages/BlogPosts/Index.svelte` component.
         * The Svelte component will receive this data as `$props`.
         */
        return Inertia::render('BlogPosts/Index', [
            'posts' => $posts, // Paginated posts data
            'search' => $request->get('search', ''), // Current search term
            'meta' => [
                'title' => 'Educational Blog - Learn Modern Web Development',
                'description' => 'Discover hands-on tutorials and insights about modern web development with Laravel, Svelte 5, and Inertia.js.',
                'total_posts' => $posts->total(), // Total number of posts (for display)
            ],
        ]);
    }

    /**
     * SHOW CREATE NEW POST FORM
     * =========================
     * 
     * This method displays the form that allows an authenticated user to create a new blog post.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Authorization**: Ensuring only permitted users can access this page.
     * - **Inertia.js Rendering**: Preparing and sending data to a Svelte form component.
     * - **Form Data Preparation**: Providing necessary options (like post statuses).
     * 
     * AUTHENTICATION: This route is protected by Laravel's `auth` middleware.
     * ROUTE: `GET /posts/create`
     */
    public function create(): Response
    {
        /**
         * 🔒 AUTHORIZATION CHECK (Defense in Depth)
         * =========================================
         * 
         * While the route itself is protected by `auth` middleware,
         * adding `Auth::check()` here is a good practice for clarity
         * and an additional layer of security. It ensures the user is logged in.
         */
        if (!Auth::check()) {
            // If not logged in, abort with a 403 Forbidden error.
            // This should ideally not be reached if middleware is set up correctly.
            abort(403, 'You must be logged in to create posts.');
        }

        /*
         * 🚀 RENDERING SVELTE FORM COMPONENT
         * ====================================
         * 
         * We render the `BlogPosts/Create.svelte` component.
         * We pass some initial data and configuration that the form needs.
         * 
         * `statuses`: Defines the available publishing options (draft, published).
         * `maxTitleLength`, `maxExcerptLength`: Provide frontend hints for input limits.
         */
        return Inertia::render('BlogPosts/Create', [
            'statuses' => [
                'draft' => 'Draft (Private)',
                'published' => 'Published (Public)',
            ],
            'maxTitleLength' => 255,
            'maxExcerptLength' => 500,
        ]);
    }

    /**
     * STORE NEW BLOG POST (FORM SUBMISSION HANDLER)
     * =============================================
     * 
     * This method processes the form submission from the `create` page,
     * validates the data, and saves a new blog post to the database.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Request Validation**: Protecting your database from invalid or malicious data.
     * - **Eloquent Create**: Inserting new records into the database.
     * - **Slug Generation**: Automatically creating SEO-friendly URLs.
     * - **Redirects & Flash Messages**: Providing immediate feedback to the user.
     * - **Mass Assignment Protection**: A crucial security concept in Laravel.
     * 
     * AUTHENTICATION: This route is protected by Laravel's `auth` middleware.
     * ROUTE: `POST /posts`
     */
    public function store(Request $request): RedirectResponse
    {
        /**
         * ✅ COMPREHENSIVE FORM VALIDATION
         * ===============================
         * 
         * **ALWAYS VALIDATE USER INPUT!** This is your first line of defense
         * against security vulnerabilities (like SQL injection, XSS) and ensures
         * data integrity in your database.
         * 
         * `Request->validate([...])`: Laravel's built-in validation. If validation fails,
         *   it automatically redirects back to the form with errors and old input.
         * 
         * KEY VALIDATION RULES:
         * - `required`: Field must not be empty.
         * - `string`: Input must be a string.
         * - `max:255`: Maximum allowed characters.
         * - `nullable`: Field can be empty or null.
         * - `in:draft,published`: Value must be one of the specified options (for `status`).
         * - `sometimes`: Only validate if the field is present in the request.
         * - `regex`: Ensures the slug is URL-friendly.
         * - `Rule::unique`: Ensures the slug is unique in the `blog_posts` table.
         */
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:draft,published'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            
            'slug' => [
                'sometimes', // Only validate if slug is provided in the request
                'nullable',  // Allow slug to be empty (auto-generated if empty)
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', // Ensures it's URL-friendly (kebab-case)
                Rule::unique('blog_posts', 'slug'),   // Must be unique in the blog_posts table
            ],
        ]);

        /**
         * 💾 CREATE BLOG POST WITH PROPER AUTHORSHIP
         * ==========================================
         * 
         * This section demonstrates how to securely create a new database record
         * and associate it with the currently authenticated user.
         * 
         * `new BlogPost($validated)`: Creates a new `BlogPost` model instance
         *   and fills it with the validated data. **This is safe because Laravel's
         *   fillable/guarded properties in the `BlogPost` model prevent mass assignment.**
         * `Auth::id()`: Safely gets the ID of the currently logged-in user.
         * `post->user_id = Auth::id()`: Explicitly sets the `user_id` to prevent
         *   a malicious user from trying to set themselves as a different author.
         * 
         * `post->slug = $validated['title']`: If no slug was provided, the `setSlugAttribute`
         *   mutator in the `BlogPost` model will automatically convert the title
         *   into a URL-friendly slug (e.g., "My Awesome Post" -> "my-awesome-post").
         */
        $post = new BlogPost($validated);
        $post->user_id = Auth::id(); // Assign the current user as the author
        
        // If slug was not provided by the user, generate it from the title
        if (empty($validated['slug'])) {
            $post->slug = $validated['title']; 
        }
        
        $post->save(); // Save the new blog post to the database

        /**
         * 🎉 SUCCESS RESPONSE & USER FEEDBACK
         * ===================================
         * 
         * After a successful operation, it's good practice to:
         * 1. Redirect the user to a relevant page (e.g., the newly created post).
         * 2. Provide a clear, actionable success message (a "flash" message).
         * 
         * `redirect()->route('posts.show', $post->slug)`: Generates a URL for the `posts.show`
         *   named route using the new post's slug, and redirects the browser.
         * `->with('success', $message)`: Stores a one-time success message in the session.
         *   This message will be available in the Svelte component (via `$page.props.flash.success`).
         */
        $message = $post->status === 'published' 
                    ? "Post '{$post->title}' has been **published** successfully!"
                    : "Post '{$post->title}' has been saved as a **draft**.";

        return redirect()
            ->route('posts.show', $post->slug)
            ->with('success', $message);
    }

    /**
     * DISPLAY SINGLE BLOG POST (PUBLIC OR AUTHOR PREVIEW)
     * ===================================================
     * 
     * This method displays a single blog post to readers.
     * It uses Laravel's **Route Model Binding** with the `slug` for clean, SEO-friendly URLs.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Route Model Binding**: Automatically fetching a model instance from the URL.
     * - **Authorization Logic**: Controlling access to unpublished content.
     * - **Eager Loading**: Fetching related data (`user`) efficiently.
     * - **Computed Properties**: Calculating values like reading time.
     * - **SEO Meta Data**: Dynamically setting page titles and descriptions for search engines.
     * 
     * ROUTE: `GET /posts/{post:slug}`
     * EXAMPLE URL: `/posts/my-amazing-blog-post` (uses the post's unique slug)
     */
    public function show(BlogPost $post): Response
    {
        /**
         * 🔒 AUTHORIZATION FOR DRAFT/UNPUBLISHED POSTS
         * ===========================================
         * 
         * By default, only published posts are publicly viewable. 
         * However, the original author should be able to preview their drafts.
         * 
         * `!$post->is_published`: Check if the post is NOT published.
         * `!Auth::check()`: Check if no user is logged in.
         * `!$post->canEdit(Auth::user())`: Check if the logged-in user is NOT the author.
         * 
         * If the post is not published AND the user is not logged in OR is not the author,
         * then we abort with a 404 error (hiding the existence of the draft).
         */
        if (!$post->is_published && (!Auth::check() || !$post->canEdit(Auth::user()))) {
            abort(404, 'Post not found or unauthorized to view.');
        }

        /**
         * 📊 EAGER LOAD RELATIONSHIPS FOR DISPLAY
         * =====================================
         * 
         * `->load('user')`: Fetches the associated `User` model (author) for the post.
         * This is more efficient than fetching it separately later (prevents N+1 queries).
         */
        $post->load('user'); // Load the author information

        /**
         * 💡 SUGGESTED RELATED POSTS
         * =========================
         * 
         * To enhance user engagement, we find up to 3 other published posts
         * by the same author to suggest to the reader.
         * 
         * `->published()`: Only suggest other published posts.
         * `->where('user_id', $post->user_id)`: Filter by the same author.
         * `->where('id', '!=', $post->id)`: Exclude the current post itself.
         * `->take(3)`: Limit to 3 suggestions.
         */
        $relatedPosts = BlogPost::published()
                               ->where('user_id', $post->user_id)
                               ->where('id', '!=', $post->id)
                               ->take(3)
                               ->get();

        /*
         * 🚀 RENDERING SVELTE COMPONENT
         * =============================
         * 
         * We send the single blog post data, related posts, and authorization status
         * to the `BlogPosts/Show.svelte` component.
         * The `meta` array contains data crucial for SEO (`<svelte:head>`).
         */
        return Inertia::render('BlogPosts/Show', [
            'post' => $post, // The main blog post object
            'relatedPosts' => $relatedPosts, // List of related posts
            'canEdit' => Auth::check() && $post->canEdit(Auth::user()), // Check if current user can edit this post
            'meta' => [
                'title' => $post->meta_title ?: $post->title,
                'description' => $post->meta_description ?: $post->excerpt,
                'author' => $post->user->name,
                'published_date' => $post->published_date,
                'reading_time' => $post->reading_time,
            ],
        ]);
    }

    /**
     * SHOW EDIT POST FORM
     * ===================
     * 
     * This method displays the pre-filled form for editing an existing blog post.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Route Model Binding**: Automatically loads the `BlogPost` to be edited.
     * - **Authorization**: Ensuring only the post's author can access the edit form.
     * - **Data Population**: Sending the existing post data to the frontend for editing.
     * 
     * AUTHENTICATION: This route is protected by Laravel's `auth` middleware.
     * ROUTE: `GET /posts/{post}/edit`
     */
    public function edit(BlogPost $post): Response
    {
        /**
         * 🔒 AUTHORIZATION CHECK
         * ====================
         * 
         * Before showing the edit form, we must verify that:
         * 1. A user is logged in (`Auth::check()`).
         * 2. The logged-in user is the actual author of the post (`$post->canEdit(Auth::user())`).
         * If either condition fails, a 403 Forbidden error is returned.
         */
        if (!Auth::check() || !$post->canEdit(Auth::user())) {
            abort(403, 'You are not authorized to edit this post.');
        }

        /*
         * 🚀 RENDERING SVELTE EDIT FORM COMPONENT
         * =======================================
         * 
         * We send the existing `post` object and available `statuses` to
         * the `BlogPosts/Edit.svelte` component. The Svelte component
         * will use this `post` object to pre-fill the form fields.
         */
        return Inertia::render('BlogPosts/Edit', [
            'post' => $post, // The blog post to be edited
            'statuses' => [
                'draft' => 'Draft (Private)',
                'published' => 'Published (Public)',
                'archived' => 'Archived (Hidden)', // New status for organization
            ],
            'maxTitleLength' => 255,
            'maxExcerptLength' => 500,
        ]);
    }

    /**
     * UPDATE EXISTING BLOG POST (FORM SUBMISSION HANDLER)
     * ===================================================
     * 
     * This method processes the form submission from the `edit` page,
     * validates the updated data, and saves changes to the existing blog post.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Validation with Exceptions**: How to handle unique rules for existing records.
     * - **Eloquent Update**: Modifying existing database records.
     * - **Conditional Logic**: Changing behavior based on post status updates.
     * - **User Feedback**: Providing dynamic success messages.
     * 
     * AUTHENTICATION: This route is protected by Laravel's `auth` middleware.
     * ROUTE: `PUT /posts/{post}`
     */
    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        /**
         * 🔒 AUTHORIZATION CHECK (Redundant Security)
         * ===========================================
         * 
         * It's crucial to re-check authorization here, even if the `edit` method
         * already performed a check. This prevents direct API calls by unauthorized users.
         */
        if (!Auth::check() || !$post->canEdit(Auth::user())) {
            abort(403, 'You are not authorized to edit this post.');
        }

        /**
         * ✅ VALIDATION FOR UPDATES
         * ========================
         * 
         * Similar to the `store` method's validation, but with one key difference:
         * When validating `slug` uniqueness, we must `->ignore($post->id)`.
         * This tells Laravel to ignore the current post's ID when checking for uniqueness,
         * allowing the post to keep its existing slug without causing a validation error.
         */
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:draft,published,archived'], // Added 'archived'
            'featured_image' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            
            'slug' => [
                'sometimes',
                'nullable', 
                'string', 
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('blog_posts', 'slug')->ignore($post->id), // Ignore current post's ID
            ],
        ]);

        /**
         * 🔄 UPDATE THE BLOG POST RECORD
         * ============================
         * 
         * `post->update($validated)`: This efficiently updates the `BlogPost` model
         * with all the validated data. Laravel automatically handles:
         * - Updating only the changed fields.
         * - Setting the `updated_at` timestamp.
         * - Preventing mass assignment of non-fillable attributes.
         */
        $post->update($validated);

        /**
         * 🎉 USER FEEDBACK & REDIRECT
         * ===========================
         * 
         * Provide context-specific success messages based on the new post status.
         * This enhances the user experience by giving clear feedback.
         * 
         * `match($post->status)`: A concise way to handle multiple conditional messages.
         * `redirect()->route('posts.show', $post->slug)`: Redirects to the updated post's page.
         */
        $message = match($post->status) {
            'published' => "Post '{$post->title}' has been **updated and published**!",
            'draft' => "Post '{$post->title}' has been **updated and saved as draft**.",
            'archived' => "Post '{$post->title}' has been **updated and archived**.",
            default => "Post '{$post->title}' has been **updated** successfully!"
        };

        return redirect()
            ->route('posts.show', $post->slug)
            ->with('success', $message);
    }

    /**
     * DELETE BLOG POST
     * ===============
     * 
     * This method permanently deletes a blog post from the database.
     * **This is an irreversible and destructive operation.**
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Authorization for Deletion**: Strict checks for destructive actions.
     * - **Eloquent Deletion**: Removing records from the database.
     * - **Redirects**: Guiding the user after deletion.
     * 
     * AUTHENTICATION: This route is protected by Laravel's `auth` middleware.
     * ROUTE: `DELETE /posts/{post}`
     * 
     * IMPORTANT PRODUCTION NOTE:
     * In real-world applications, consider implementing "soft deletes" (`deleted_at` timestamp)
     * instead of permanent deletion. This allows for data recovery and audit trails.
     */
    public function destroy(BlogPost $post): RedirectResponse
    {
        /**
         * 🔒 STRICT AUTHORIZATION FOR DELETION
         * ===================================
         * 
         * This is the final and most critical authorization check for deletion.
         * Only the post's author should be able to delete their own posts.
         * If unauthorized, a 403 Forbidden error is returned.
         */
        if (!Auth::check() || !$post->canEdit(Auth::user())) {
            abort(403, 'You are not authorized to delete this post.');
        }

        /**
         * ⚠️ CAPTURE POST TITLE BEFORE DELETION
         * ====================================
         * 
         * We need to store the post's title *before* deleting it,
         * as the post object will no longer exist after `->delete()` is called.
         * This allows us to provide a meaningful success message to the user.
         */
        $postTitle = $post->title;

        /**
         * 🗑️ PERFORM THE DELETION
         * ======================
         * 
         * `post->delete()`: Eloquent method to remove the record from the `blog_posts` table.
         * If you have `onDelete('cascade')` set up in your migrations,
         * any related records (e.g., comments) might also be deleted automatically.
         */
        $post->delete();

        /**
         * ✅ REDIRECT WITH SUCCESS MESSAGE
         * ===============================
         * 
         * After successful deletion, redirect the user to a relevant page (e.g., their dashboard)
         * and display a confirmation message using a flash session variable.
         */
        return redirect()
            ->route('dashboard') // Redirect to the user's dashboard or posts list
            ->with('success', "Post '{$postTitle}' has been **deleted** successfully.");
    }

    /**
     * AUTHOR'S POST MANAGEMENT DASHBOARD
     * =================================
     * 
     * This method displays a dashboard for the authenticated user to manage their own blog posts.
     * Unlike the public `index` method, this shows **all** of the user's posts (published, drafts, archived).
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **User-Specific Data**: Fetching data related to the logged-in user.
     * - **Dashboard Logic**: Aggregating statistics and providing quick actions.
     * - **Inertia.js Rendering**: Sending structured data to a Svelte component for a dashboard UI.
     * 
     * AUTHENTICATION: This route is protected by Laravel's `auth` middleware.
     * ROUTE: `GET /manage-posts` (custom route for user's blog management)
     */
    public function manage(): Response
    {
        /**
         * 🔒 REQUIRE AUTHENTICATION
         * ========================
         * 
         * Although the route is middleware-protected, this explicit check
         * provides immediate clarity and an extra layer of defense.
         */
        if (!Auth::check()) {
            abort(403, 'You must be logged in to manage posts.');
        }

        /**
         * 📊 FETCH USER'S POSTS & STATISTICS
         * ===================================
         * 
         * `Auth::user()`: Retrieves the currently authenticated user's model instance.
         * `->blogPosts()`: Accesses the relationship defined in the `User` model
         *   to get all posts belonging to this user.
         * `->latest()`: Orders the user's posts by creation date, newest first.
         * `->paginate(15)`: Paginate the results to show 15 posts per page.
         *
         * `stats` array: Calculates and provides a summary of the user's posts
         *   (total, published, drafts) for display on the dashboard.
         */
        $user = Auth::user();
        
        $posts = $user->blogPosts()
                     ->latest()
                     ->paginate(15); // Paginate user's posts

        $stats = [
            'totalPosts' => $user->blogPosts()->count(),
            'publishedPosts' => $user->publishedPosts()->count(),
            'draftPosts' => $user->draftPosts()->count(),
            'totalViews' => 0, // TODO: Future enhancement: Implement view tracking for posts
        ];

        /*
         * 🚀 RENDERING SVELTE MANAGEMENT DASHBOARD COMPONENT
         * ================================================
         * 
         * We send the user's posts, aggregated statistics, and the user object itself
         * to the `BlogPosts/Manage.svelte` component for rendering.
         */
        return Inertia::render('BlogPosts/Manage', [
            'posts' => $posts, // Paginated list of user's posts
            'stats' => $stats, // User-specific post statistics
            'user' => $user,   // Current authenticated user object
        ]);
    }
}
