<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * BLOG POST MODEL - THE HEART OF OUR CRUD APPLICATION
 * ===================================================
 * 
 * This Eloquent model represents a blog post in our application. Think of it as
 * a bridge between your PHP code and the database table. It handles:
 * 
 * - Database interactions (create, read, update, delete)
 * - Relationships with other models (User)
 * - Data transformation (accessors and mutators)
 * - Query scopes for common database queries
 * - Business logic related to blog posts
 * 
 * EDUCATIONAL CONCEPTS COVERED:
 * - Eloquent ORM basics
 * - Model relationships (belongs to)
 * - Mass assignment protection
 * - Accessors and mutators
 * - Query scopes
 * - Model events and observers
 * 
 * DATABASE TABLE: blog_posts
 * 
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string|null $excerpt
 * @property string $status
 * @property string|null $featured_image
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property Carbon|null $published_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property-read User $user
 */
class BlogPost extends Model
{
    /**
     * FILLABLE ATTRIBUTES - MASS ASSIGNMENT PROTECTION
     * ===============================================
     * 
     * The $fillable array defines which attributes can be mass-assigned.
     * This is a security feature that prevents malicious users from
     * setting fields they shouldn't be able to modify.
     * 
     * EDUCATIONAL NOTE:
     * Mass assignment is when you create/update a model using an array:
     * BlogPost::create($request->all()) // This uses mass assignment
     * 
     * Only fields listed in $fillable can be set this way.
     * Fields not in $fillable must be set individually:
     * $post->id = 1; // This would need to be done manually
     * 
     * SECURITY EXAMPLE:
     * Without this protection, a malicious user could submit:
     * { "title": "My Post", "user_id": 999 }
     * And steal ownership of the post!
     */
    protected $fillable = [
        'title',           // Blog post title
        'slug',            // URL-friendly version of title
        'content',         // Main blog post content
        'excerpt',         // Short summary (optional)
        'status',          // draft, published, archived
        'featured_image',  // Image URL/path (optional)
        'meta_title',      // SEO title (optional)
        'meta_description', // SEO description (optional)
        'published_at',    // When the post was published
        // Note: user_id is NOT fillable for security
        // Note: id, created_at, updated_at are managed by Laravel
    ];

    /**
     * APPENDABLE ATTRIBUTES - ADDITIONAL ATTRIBUTES TO RETURN
     * ======================================================
     * 
     * The $appends array defines additional attributes that should be returned
     * with the model. This is useful for computed attributes that don't exist
     * in the database.
     */
    protected $appends = ['reading_time'];

    /**
     * ATTRIBUTE CASTING - AUTOMATIC DATA TRANSFORMATION
     * ================================================
     * 
     * The $casts array tells Eloquent how to automatically convert
     * database values to PHP types and vice versa.
     * 
     * EDUCATIONAL EXAMPLES:
     * - 'published_at' => 'datetime' converts database timestamp to Carbon instance
     * - 'is_featured' => 'boolean' converts 1/0 to true/false
     * - 'tags' => 'array' converts JSON string to PHP array
     * 
     * This happens automatically when you access the attributes:
     * $post->published_at->format('Y-m-d') // Carbon methods available
     * $post->is_featured === true          // Boolean, not 1
     */
    
    protected $casts = [
        'published_at' => 'datetime',  // Convert to Carbon instance for date manipulation
        'created_at' => 'datetime',    // Explicit (usually automatic)
        'updated_at' => 'datetime',    // Explicit (usually automatic)
    ];

    /**
     * RELATIONSHIP: BELONGS TO USER
     * ============================
     * 
     * This defines the relationship between a blog post and its author.
     * A blog post "belongs to" one user (the author).
     * 
     * EDUCATIONAL NOTE:
     * This creates several useful features:
     * 1. $post->user            // Get the author
     * 2. $post->user->name      // Get the author's name
     * 3. $post->user_id         // Get the author's ID
     * 4. User::with('posts')    // Eager load posts with users (prevents N+1 queries)
     * 
     * DATABASE RELATIONSHIP:
     * blog_posts.user_id -> users.id (foreign key constraint)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * QUERY SCOPE: PUBLISHED POSTS ONLY
     * =================================
     * 
     * Scopes are reusable query constraints that make your code cleaner.
     * This scope filters to only show published posts.
     * 
     * USAGE EXAMPLES:
     * BlogPost::published()->get()                    // All published posts
     * BlogPost::published()->latest()->take(5)->get() // 5 latest published posts
     * $user->posts()->published()->count()            // Count of user's published posts
     * 
     * WHY USE SCOPES?
     * - Reusable query logic
     * - Cleaner, more readable code
     * - Consistent filtering across your app
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * QUERY SCOPE: DRAFT POSTS ONLY
     * =============================
     * 
     * Filter to only show draft posts.
     * Useful for author dashboards and content management.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * QUERY SCOPE: RECENT POSTS
     * =========================
     * 
     * Get posts ordered by most recent first.
     * 
     * USAGE: BlogPost::recent()->take(10)->get()
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * QUERY SCOPE: BY STATUS
     * =====================
     * 
     * Filter posts by a specific status.
     * 
     * USAGE: BlogPost::byStatus('published')->get()
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * ACCESSOR: IS PUBLISHED
     * =====================
     * 
     * Accessors transform attribute values when you access them.
     * This creates a virtual "is_published" attribute.
     * 
     * USAGE: $post->is_published  // Returns true/false
     * 
     * EDUCATIONAL NOTE:
     * This doesn't exist in the database - it's computed on-the-fly.
     * Useful for conditional logic in your views and business logic.
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published' 
               && $this->published_at !== null 
               && $this->published_at->isPast();
    }

    /**
     * ACCESSOR: READING TIME ESTIMATE
     * ==============================
     * 
     * Calculate estimated reading time based on content length.
     * Assumes average reading speed of 200 words per minute.
     * 
     * USAGE: $post->reading_time  // Returns "5 min read"
     */
    public function getReadingTimeAttribute(): string
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200); // Average reading speed
        
        return $minutes === 1 ? '1 min read' : $minutes . ' min read';
    }

    /**
     * ACCESSOR: FORMATTED PUBLISHED DATE
     * =================================
     * 
     * Get a human-friendly published date.
     * 
     * USAGE: $post->published_date  // Returns "March 15, 2024"
     */
    public function getPublishedDateAttribute(): string
    {
        if (!$this->published_at) {
            return 'Not published';
        }
        
        return $this->published_at->format('F j, Y');
    }

    /**
     * MUTATOR: AUTO-GENERATE SLUG FROM TITLE
     * ======================================
     * 
     * Mutators transform attribute values when you set them.
     * This automatically creates a URL-friendly slug from the title.
     * 
     * EDUCATIONAL NOTE:
     * When you set: $post->title = "My Amazing Blog Post"
     * Laravel also sets: $post->slug = "my-amazing-blog-post"
     * 
     * This ensures URLs are always SEO-friendly and consistent.
     */
    public function setTitleAttribute(string $value): void
    {
        $this->attributes['title'] = $value;
        
        // Only auto-generate slug if it doesn't exist
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    /**
     * MUTATOR: ENSURE UNIQUE SLUG
     * ===========================
     * 
     * Automatically ensure the slug is unique by appending numbers if needed.
     * 
     * EXAMPLE:
     * "my-post" -> "my-post" (if available)
     * "my-post" -> "my-post-2" (if "my-post" exists)
     * "my-post" -> "my-post-3" (if "my-post-2" exists)
     */
    public function setSlugAttribute(string $value): void
    {
        $slug = Str::slug($value);
        $originalSlug = $slug;
        $counter = 1;
        
        // Keep checking until we find a unique slug
        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $counter++;
            $slug = $originalSlug . '-' . $counter;
        }
        
        $this->attributes['slug'] = $slug;
    }

    /**
     * MUTATOR: AUTO-SET PUBLISHED DATE
     * ================================
     * 
     * Automatically set published_at when status changes to 'published'.
     */
    public function setStatusAttribute(string $value): void
    {
        $this->attributes['status'] = $value;
        
        // If changing to published and no published date set, set it now
        if ($value === 'published' && empty($this->attributes['published_at'])) {
            $this->attributes['published_at'] = now();
        }
    }

    /**
     * CUSTOM BUSINESS LOGIC METHODS
     * ============================
     * 
     * These methods encapsulate business logic related to blog posts.
     * They make your controllers cleaner and logic more reusable.
     */

    /**
     * Publish the blog post
     */
    public function publish(): bool
    {
        $this->status = 'published';
        $this->published_at = now();
        
        return $this->save();
    }

    /**
     * Unpublish the blog post (back to draft)
     */
    public function unpublish(): bool
    {
        $this->status = 'draft';
        $this->published_at = null;
        
        return $this->save();
    }

    /**
     * Archive the blog post
     */
    public function archive(): bool
    {
        $this->status = 'archived';
        
        return $this->save();
    }

    /**
     * Check if the current user can edit this post
     */
    public function canEdit(User $user): bool
    {
        // Only the author can edit their own posts
        // In a more complex app, you might also check for admin roles
        return $this->user_id === $user->id;
    }

    /**
     * Get the URL for this blog post
     */
    public function getUrlAttribute(): string
    {
        return route('posts.show', $this->slug);
    }

    /**
     * DEFAULT QUERY ORDERING
     * ======================
     * 
     * This method defines the default order for queries.
     * When you do BlogPost::all(), it will automatically order by newest first.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function (Builder $builder) {
            $builder->orderBy('created_at', 'desc');
        });
    }
}
