<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * USER MODEL - AUTHENTICATION AND BLOG AUTHORSHIP
 * ===============================================
 * 
 * This model represents users in our educational blog application.
 * It extends Laravel's built-in User class which provides authentication features.
 * 
 * EDUCATIONAL CONCEPTS COVERED:
 * - User authentication (login/logout)
 * - Model relationships (has many blog posts)
 * - Mass assignment protection
 * - Password hashing
 * - Attribute hiding for security
 * 
 * DATABASE TABLE: users
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection|BlogPost[] $blogPosts
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * MASS ASSIGNMENT PROTECTION - SECURITY
     * ====================================
     * 
     * These attributes can be mass-assigned using User::create($data).
     * This is a security feature that prevents malicious users from
     * setting sensitive fields like 'remember_token' or 'email_verified_at'.
     * 
     * EDUCATIONAL NOTE:
     * Only include fields that users should be able to set directly.
     * Sensitive fields like tokens, verification status, etc. should
     * be managed by your application logic, not user input.
     */
    protected $fillable = [
        'name',        // User's display name
        'email',       // Email address (used for login)
        'password',    // Password (will be hashed automatically)
    ];

    /**
     * HIDDEN ATTRIBUTES - SECURITY
     * ===========================
     * 
     * These attributes will be automatically hidden when the model
     * is converted to JSON (e.g., in API responses or when passed to frontend).
     * 
     * EDUCATIONAL NOTE:
     * Never expose passwords or tokens to the frontend! This array
     * ensures sensitive data stays on the backend even if you accidentally
     * send the entire user object to your Svelte components.
     * 
     * EXAMPLE:
     * $user->toArray() // Will NOT include password or remember_token
     * json_encode($user) // Same - hidden fields are excluded
     */
    protected $hidden = [
        'password',        // Never expose password hashes
        'remember_token',  // Never expose authentication tokens
    ];

    /**
     * ATTRIBUTE CASTING - DATA TRANSFORMATION
     * ======================================
     * 
     * This method defines how Laravel should cast database values
     * to PHP types when you access them.
     * 
     * EDUCATIONAL EXAMPLES:
     * - 'email_verified_at' => 'datetime' converts timestamp to Carbon instance
     * - 'password' => 'hashed' automatically hashes passwords when set
     * 
     * USAGE:
     * $user->email_verified_at->diffForHumans() // "2 days ago"
     * $user->password = 'secret123' // Automatically hashed before saving
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',  // Convert to Carbon for date manipulation
            'password' => 'hashed',             // Automatically hash passwords
        ];
    }

    /**
     * RELATIONSHIP: HAS MANY BLOG POSTS
     * =================================
     * 
     * This defines the relationship between users and their blog posts.
     * A user "has many" blog posts (they can write multiple posts).
     * 
     * EDUCATIONAL NOTE:
     * This creates several useful features:
     * 1. $user->blogPosts              // Get all user's posts
     * 2. $user->blogPosts()->published()->get()  // Get user's published posts
     * 3. $user->blogPosts()->count()   // Count user's posts
     * 4. $user->blogPosts()->create([...])  // Create a new post for this user
     * 
     * DATABASE RELATIONSHIP:
     * users.id <- blog_posts.user_id (foreign key constraint)
     * 
     * INVERSE RELATIONSHIP:
     * See BlogPost::user() for the other side of this relationship
     */
    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    /**
     * CONVENIENCE METHOD: GET PUBLISHED POSTS
     * ======================================
     * 
     * A shortcut to get only the user's published blog posts.
     * This demonstrates how to combine relationships with query scopes.
     * 
     * USAGE:
     * $user->publishedPosts()  // Returns a query builder
     * $user->publishedPosts()->get()  // Execute the query
     * $user->publishedPosts()->count()  // Count published posts
     */
    public function publishedPosts(): HasMany
    {
        return $this->blogPosts()->published();
    }

    /**
     * CONVENIENCE METHOD: GET DRAFT POSTS
     * ==================================
     * 
     * Get only the user's draft posts.
     * Useful for author dashboards and content management interfaces.
     */
    public function draftPosts(): HasMany
    {
        return $this->blogPosts()->draft();
    }

    /**
     * BUSINESS LOGIC: CHECK IF USER IS AUTHOR
     * ======================================
     * 
     * Check if this user has written any blog posts.
     * Useful for conditional UI elements and permissions.
     * 
     * USAGE:
     * if ($user->isAuthor()) {
     *     // Show "Manage Posts" button
     * }
     */
    public function isAuthor(): bool
    {
        return $this->blogPosts()->exists();
    }

    /**
     * BUSINESS LOGIC: GET AUTHOR STATS
     * ===============================
     * 
     * Get statistics about this user as an author.
     * Returns an array with useful metrics for dashboards.
     * 
     * USAGE:
     * $stats = $user->getAuthorStats();
     * echo "Published: " . $stats['published_count'];
     */
    public function getAuthorStats(): array
    {
        return [
            'total_posts' => $this->blogPosts()->count(),
            'published_count' => $this->publishedPosts()->count(),
            'draft_count' => $this->draftPosts()->count(),
            'latest_post' => $this->blogPosts()->first(),
        ];
    }
}
