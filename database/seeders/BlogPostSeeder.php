<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user or create one if none exists
        $user = User::first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
            ]);
        }
        
        // Create sample blog posts
        $posts = [
            [
                'title' => 'Getting Started with Svelte 5',
                'content' => 'Svelte 5 introduces exciting new features that make building reactive user interfaces even more enjoyable. In this comprehensive guide, we\'ll explore the new syntax, performance improvements, and best practices for modern web development.',
                'excerpt' => 'Learn about the new features and improvements in Svelte 5.',
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Laravel and Inertia.js: The Perfect Match',
                'content' => 'Combining Laravel\'s robust backend capabilities with Inertia.js creates a seamless development experience. Discover how to build modern SPAs without the complexity of traditional API-driven architecture.',
                'excerpt' => 'Explore how Laravel and Inertia.js work together to create amazing web applications.',
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Building Responsive UIs with Tailwind CSS',
                'content' => 'Tailwind CSS revolutionizes how we approach styling in modern web development. Learn utility-first principles, responsive design patterns, and how to create beautiful interfaces quickly.',
                'excerpt' => 'Master Tailwind CSS for creating stunning responsive user interfaces.',
                'status' => 'published',
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Advanced Database Relationships in Laravel',
                'content' => 'Deep dive into Eloquent relationships, from basic one-to-many to complex polymorphic relationships. This draft covers advanced topics for seasoned Laravel developers.',
                'excerpt' => 'Explore complex database relationships and Eloquent features.',
                'status' => 'draft',
                'published_at' => null,
            ],
            [
                'title' => 'Modern JavaScript ES2024 Features',
                'content' => 'JavaScript continues to evolve with new features that make development more efficient. This draft explores the latest additions to the language specification.',
                'excerpt' => 'Stay up-to-date with the latest JavaScript features and syntax.',
                'status' => 'draft',
                'published_at' => null,
            ],
        ];
        
        foreach ($posts as $postData) {
            // Generate a slug from the title
            $slug = Str::slug($postData['title']);
            
            // Ensure unique slug
            $originalSlug = $slug;
            $counter = 1;
            while (BlogPost::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            BlogPost::create([
                'user_id' => $user->id,
                'title' => $postData['title'],
                'slug' => $slug,
                'content' => $postData['content'],
                'excerpt' => $postData['excerpt'],
                'status' => $postData['status'],
                'published_at' => $postData['published_at'],
            ]);
        }
    }
    
    /**
     * Calculate estimated reading time for content
     */
    private function calculateReadingTime(string $content): string
    {
        $wordCount = str_word_count(strip_tags($content));
        $readingTime = ceil($wordCount / 200); // Assuming 200 words per minute
        
        return $readingTime . ' min read';
    }
}
