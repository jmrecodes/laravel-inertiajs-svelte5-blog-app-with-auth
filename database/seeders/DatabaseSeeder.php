<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DATABASE SEEDER - MAIN SEEDER ORCHESTRATOR
 * ==========================================
 * 
 * This is the main seeder that coordinates all other seeders.
 * It's like a conductor for your database seeding orchestra.
 * 
 * EDUCATIONAL NOTE:
 * The DatabaseSeeder is the entry point for all seeding operations.
 * It calls other specialized seeders in the correct order.
 * 
 * USAGE:
 * - php artisan db:seed (runs this seeder)
 * - php artisan migrate:fresh --seed (reset DB and seed)
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * This method calls other seeders in the proper order.
     * Order matters when there are foreign key relationships!
     */
    public function run(): void
    {
        /**
         * SEEDING ORDER EXPLANATION
         * ========================
         * 
         * 1. Users first (independent table)
         * 2. Blog posts second (depends on users via foreign key)
         * 3. Comments third (depends on both users and posts)
         * 
         * Note: We'll add BlogPostSeeder when we create the blog CRUD features.
         */
        
        $this->call([
            /**
             * USER SEEDER - CREATE DEMO USERS
             * ===============================
             * 
             * Creates demo users for testing and development.
             * These users can be logged into using demo credentials.
             */
            UserSeeder::class,
            BlogPostSeeder::class,
        ]);
        
        /**
         * EDUCATIONAL PROGRESS UPDATE
         * ==========================
         * 
         * Let the developer know what was seeded.
         */
        $this->command->info('🌱 Database seeding completed successfully!');
        $this->command->info('🎓 Educational Blog app is ready for development.');
    }
}
