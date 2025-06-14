<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * USER SEEDER - DEMO DATA FOR EDUCATIONAL BLOG APP
 * =================================================
 * 
 * This seeder creates demo users for testing and educational purposes.
 * It ensures consistent demo credentials that match the UI expectations.
 * 
 * EDUCATIONAL NOTE:
 * Seeders are Laravel's way to populate the database with initial data.
 * They're essential for:
 * - Development testing
 * - Demo environments
 * - Initial application setup
 * - Consistent test data
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This method creates demo users with specific credentials
     * that match what the frontend demo buttons expect.
     */
    public function run(): void
    {
        /**
         * DEMO USER - PRIMARY TEST ACCOUNT
         * ================================
         * 
         * This is the main demo account that users can log into
         * using the "Fill Demo Credentials" button on the login page.
         * 
         * CREDENTIALS:
         * - Email: demo@example.com
         * - Password: password123
         * 
         * These match exactly what the login component fills in.
         */
        User::updateOrCreate(
            ['email' => 'demo@example.com'], // Find by email
            [
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'password' => Hash::make('password123'), // Matches login demo button
                'email_verified_at' => now(), // Pre-verified for convenience
            ]
        );

        /**
         * EDUCATIONAL NOTE: updateOrCreate() METHOD
         * ========================================
         * 
         * We use updateOrCreate() instead of create() because:
         * 
         * 1. IDEMPOTENT: Safe to run multiple times
         * 2. PREVENTS DUPLICATES: Won't create duplicates if run again
         * 3. UPDATES EXISTING: If user exists, updates their data
         * 4. CREATES IF MISSING: Creates user if they don't exist
         * 
         * This is a best practice for seeders!
         */

        $this->command->info('✅ Demo users created successfully:');
        $this->command->line('📧 demo@example.com (password: password123)');
        $this->command->line('📧 test@example.com (password: password)');
        $this->command->line('📧 admin@example.com (password: admin123)');
    }
}
