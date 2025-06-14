<?php

/**
 * BLOG POSTS DATABASE MIGRATION - DEFINING OUR DATA STRUCTURE
 * ===========================================================
 * 
 * This migration creates the database table that will store our blog posts.
 * Think of migrations as "version control for your database" - they let you
 * define, modify, and share database structure changes.
 * 
 * EDUCATIONAL CONCEPTS COVERED:
 * - Database table design
 * - Column types and constraints
 * - Foreign key relationships
 * - Indexes for performance
 * - Migration rollback (the 'down' method)
 * 
 * WHY USE MIGRATIONS?
 * - Keep database changes in version control
 * - Share database structure with team members
 * - Apply changes consistently across environments
 * - Rollback changes if something goes wrong
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CREATE TABLE - BUILDING OUR BLOG POSTS STRUCTURE
     * ===============================================
     * 
     * The 'up' method defines what happens when this migration runs.
     * It creates the 'blog_posts' table with all the columns we need
     * for our educational CRUD application.
     * 
     * EDUCATIONAL NOTE:
     * Each column type (string, text, timestamp, etc.) maps to a specific
     * database column type (VARCHAR, TEXT, TIMESTAMP, etc.). Laravel
     * abstracts this so your migrations work across different databases.
     */
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            /**
             * PRIMARY KEY - UNIQUE IDENTIFIER
             * ==============================
             * 
             * Every table needs a primary key - a unique identifier for each row.
             * Laravel's id() method creates an auto-incrementing integer primary key.
             * 
             * Database: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
             * PHP: Will be accessible as $post->id
             */
            $table->id();

            /**
             * FOREIGN KEY - RELATIONSHIP TO USERS
             * ===================================
             * 
             * This links each blog post to the user who wrote it.
             * foreignId() creates an integer column that references another table.
             * constrained() sets up the foreign key constraint automatically.
             * 
             * Database: BIGINT UNSIGNED with FOREIGN KEY constraint
             * Relationship: blog_posts.user_id -> users.id
             * PHP: $post->user_id and $post->user (via Eloquent relationship)
             */
            $table->foreignId('user_id')
                  ->constrained()  // References users.id automatically
                  ->onDelete('cascade');  // Delete posts when user is deleted

            /**
             * TITLE - THE BLOG POST HEADLINE
             * ==============================
             * 
             * The main title/headline of the blog post.
             * string() creates a VARCHAR column with 255 character limit.
             * 
             * Database: VARCHAR(255) NOT NULL
             * Validation: Required, max 255 characters
             * UI: Will be displayed prominently, used in page titles
             */
            $table->string('title');

            /**
             * SLUG - URL-FRIENDLY VERSION OF TITLE
             * ====================================
             * 
             * A "slug" is a URL-friendly version of the title.
             * Example: "My First Blog Post" becomes "my-first-blog-post"
             * 
             * Database: VARCHAR(255) UNIQUE NOT NULL
             * Purpose: Used in URLs like /posts/my-first-blog-post
             * SEO: Better for search engines than /posts/123
             */
            $table->string('slug')->unique();

            /**
             * CONTENT - THE MAIN BLOG POST TEXT
             * =================================
             * 
             * The main body content of the blog post.
             * text() creates a TEXT column for large amounts of text.
             * 
             * Database: TEXT (can store ~65,000 characters)
             * Editor: Perfect for rich text content, markdown, etc.
             * Display: Will be formatted and displayed as the main content
             */
            $table->text('content');

            /**
             * EXCERPT - SHORT SUMMARY (OPTIONAL)
             * ==================================
             * 
             * A brief summary or excerpt of the post content.
             * nullable() makes this field optional.
             * 
             * Database: TEXT NULL (can be empty)
             * Purpose: Used in post lists, search results, social sharing
             * UX: Gives readers a preview before clicking to read full post
             */
            $table->text('excerpt')->nullable();

            /**
             * STATUS - PUBLICATION STATE
             * =========================
             * 
             * Controls whether the post is published, draft, etc.
             * enum() restricts values to specific options.
             * 
             * Database: ENUM('draft', 'published', 'archived')
             * Default: 'draft' (new posts start as drafts)
             * Workflow: draft -> published -> archived (optional)
             */
            $table->enum('status', ['draft', 'published', 'archived'])
                  ->default('draft');

            /**
             * FEATURED IMAGE - OPTIONAL BLOG POST IMAGE
             * ========================================
             * 
             * URL or path to a featured image for the blog post.
             * nullable() because not all posts need images.
             * 
             * Database: VARCHAR(255) NULL
             * Storage: Could be local file path or external URL
             * Display: Used as header image, social media preview, etc.
             */
            $table->string('featured_image')->nullable();

            /**
             * METADATA FIELDS - FOR SEO AND SOCIAL MEDIA
             * ==========================================
             * 
             * Additional fields that help with search engine optimization
             * and social media sharing.
             */
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            /**
             * PUBLICATION TIMESTAMPS
             * =====================
             * 
             * Track when the post was actually published (vs. created).
             * Different from created_at because posts can be drafts first.
             * 
             * Database: TIMESTAMP NULL
             * Logic: Set when status changes from 'draft' to 'published'
             * Display: "Published on March 15, 2024"
             */
            $table->timestamp('published_at')->nullable();

            /**
             * AUTOMATIC TIMESTAMPS - AUDIT TRAIL
             * ==================================
             * 
             * Laravel automatically manages these timestamps:
             * - created_at: When the record was first created
             * - updated_at: When the record was last modified
             * 
             * Database: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
             * Purpose: Audit trail, sorting, "last updated" displays
             * Automatic: Laravel handles these - you don't need to set them manually
             */
            $table->timestamps();

            /**
             * DATABASE INDEXES - PERFORMANCE OPTIMIZATION
             * ==========================================
             * 
             * Indexes speed up database queries by creating "shortcuts" to data.
             * Think of them like an index in a book - helps find information faster.
             */
            
            // Index for finding posts by status (published, draft, etc.)
            $table->index('status');
            
            // Index for finding posts by publication date
            $table->index('published_at');
            
            // Composite index for finding user's posts by status
            $table->index(['user_id', 'status']);
            
            // Index for slug-based lookups (for URLs)
            // Note: unique() already creates an index, but this is explicit
            $table->index('slug');
        });
    }

    /**
     * ROLLBACK - UNDOING THE MIGRATION
     * ================================
     * 
     * The 'down' method defines how to undo this migration.
     * If something goes wrong, you can rollback using:
     * php artisan migrate:rollback
     * 
     * EDUCATIONAL NOTE:
     * Always make sure your 'down' method properly reverses
     * everything your 'up' method does. This ensures you can
     * safely rollback changes if needed.
     */
    public function down(): void
    {
        /**
         * DROP TABLE - REMOVING THE ENTIRE TABLE
         * ======================================
         * 
         * dropIfExists() safely removes the table if it exists.
         * The "IfExists" part prevents errors if the table was already deleted.
         * 
         * WARNING: This will delete ALL data in the table!
         * In production, you might want to backup data first.
         */
        Schema::dropIfExists('blog_posts');
    }
};
