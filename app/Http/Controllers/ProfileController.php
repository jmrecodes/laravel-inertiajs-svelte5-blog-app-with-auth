<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

/**
 * PROFILE CONTROLLER - USER ACCOUNT MANAGEMENT FOR EDUCATIONAL APP
 * ================================================================
 * 
 * This controller handles all user profile management functionality for our
 * educational blog application. It demonstrates how to securely allow users to:
 * - View and update their personal information (name, email).
 * - Change their password with strong security measures.
 * - Delete their account (with important caveats for educational purposes).
 * 
 * 🎓 BEGINNER LEARNING OBJECTIVES:
 * ================================
 * 1. **User Authentication & Authorization**: Ensuring users can only manage their own profiles.
 * 2. **Form Validation**: Implementing robust validation for personal data and passwords.
 * 3. **Password Hashing & Verification**: Securely handling user passwords.
 * 4. **Database Updates (Eloquent)**: Modifying existing user records.
 * 5. **Security Best Practices**: Protecting sensitive user operations.
 * 6. **Inertia.js Integration**: Rendering Svelte profile components and handling form submissions.
 * 
 * 🔍 WHAT YOU'LL LEARN:
 * =====================
 * - How to fetch the authenticated user's data.
 * - Using Laravel's validation rules, including custom rules like `Rule::unique`.
 * - Applying `Hash::check` for password verification.
 * - Redirecting with flash messages for user feedback.
 * - Important considerations for destructive actions like account deletion.
 * 
 * ROUTES MANAGED BY THIS CONTROLLER:
 * - `GET /profile`: Show user profile edit form (`edit` method)
 * - `PUT /profile`: Update profile information (name, email) (`update` method)
 * - `PUT /profile/password`: Change user password (`updatePassword` method)
 * - `DELETE /profile`: Delete user account (`destroy` method)
 * 
 * SECURITY CONSIDERATIONS (CRITICAL FOR ANY APP):
 * - **Self-Service Only**: Users can only access/edit their own profile, enforced by middleware and explicit checks.
 * - **Password Re-verification**: Sensitive changes (like password change or account deletion) require re-entering the current password.
 * - **Email Uniqueness**: Ensures each user has a unique email address.
 * - **Strong Passwords**: Enforces minimum length, letters, and numbers for new passwords.
 * - **Mass Assignment Protection**: Laravel's models prevent unauthorized mass updates.
 * 
 * This controller is heavily commented to guide you through each concept
 * involved in building secure user profile management features.
 */
class ProfileController extends Controller
{
    /**
     * SHOW PROFILE EDIT FORM
     * =====================
     * 
     * This method displays the user's personal profile information in an editable form.
     * It also fetches and displays relevant statistics about the user's activity.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Fetching Authenticated User**: How to get the data for the currently logged-in user.
     * - **Data Aggregation**: Calculating and presenting user-specific statistics (e.g., total posts).
     * - **Inertia.js Rendering**: Sending structured user data to a Svelte component.
     * 
     * AUTHENTICATION: This route is protected by Laravel's `auth` middleware.
     * ROUTE: `GET /profile`
     */
    public function edit(): Response
    {
        /**
         * 👤 GET AUTHENTICATED USER INSTANCE
         * =================================
         * 
         * `Auth::user()`: This helper securely retrieves the currently logged-in `User` model instance.
         * This is safe because the route accessing this method is protected by Laravel's `auth` middleware.
         */
        $user = Auth::user();

        /**
         * 📊 LOAD USER STATISTICS FOR DASHBOARD
         * ====================================
         * 
         * We calculate various statistics related to the user's blog posts.
         * This demonstrates aggregating related data for display in a dashboard.
         * 
         * `user->blogPosts()->count()`: Counts all blog posts created by this user.
         * `user->publishedPosts()->count()`: Counts only the published posts.
         * `user->draftPosts()->count()`: Counts only the draft posts.
         * `user->created_at->format('F Y')`: Formats the user's creation date nicely.
         */
        $stats = [
            'totalPosts' => $user->blogPosts()->count(),
            'publishedPosts' => $user->publishedPosts()->count(),
            'draftPosts' => $user->draftPosts()->count(),
            'memberSince' => $user->created_at->format('F Y'),
        ];

        /*
         * 🚀 RENDERING SVELTE PROFILE EDIT COMPONENT
         * =========================================
         * 
         * `Inertia::render('Profile/Edit', [...])`: Sends the authenticated user's data
         * and their calculated statistics to the `resources/js/Pages/Profile/Edit.svelte` component.
         * The Svelte component will receive this data as `$props`.
         */
        return Inertia::render('Profile/Edit', [
            'user' => $user, // The authenticated user object
            'stats' => $stats, // User-specific statistics
            'meta' => [
                'title' => 'Profile Settings',
                'description' => 'Update your account information and preferences',
            ],
        ]);
    }

    /**
     * UPDATE PROFILE INFORMATION (NAME & EMAIL)
     * =========================================
     * 
     * This method handles the submission of the profile information form,
     * updating the user's name and email address in the database.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Request Validation**: Ensuring submitted data is valid and safe.
     * - **Unique Email Rule**: Allowing an email to be unique except for the current user's.
     * - **Eloquent Update**: Modifying an existing database record.
     * - **Flash Messages**: Providing clear feedback to the user after a successful update.
     * 
     * AUTHENTICATION: This route is protected by Laravel's `auth` middleware.
     * ROUTE: `PUT /profile`
     */
    public function update(Request $request): RedirectResponse
    {
        /**
         * 👤 GET AUTHENTICATED USER
         * =====================
         * 
         * Fetch the currently logged-in user instance.
         */
        $user = Auth::user();

        /**
         * ✅ PROFILE UPDATE VALIDATION
         * ===========================
         * 
         * This validates the `name` and `email` fields.
         * 
         * `Rule::unique('users')->ignore($user->id)`: This is a crucial validation rule.
         *   It ensures that the new email address is unique in the `users` table,
         *   BUT it allows the user to keep their *current* email if they don't change it,
         *   or if they change it to their own existing email (which shouldn't happen, but covers edge cases).
         */
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255',
                Rule::unique('users')->ignore($user->id), // Ensure email is unique, ignoring current user's email
            ],
        ]);

        /**
         * 🔄 UPDATE USER INFORMATION IN DATABASE
         * =====================================
         * 
         * `user->update($validated)`: This Eloquent method updates the user's record
         * with the validated data. Laravel automatically handles setting the `updated_at` timestamp.
         * It also respects the `$fillable` or `$guarded` properties in your `User` model
         * to prevent mass assignment vulnerabilities.
         */
        $user->update($validated);

        /**
         * 🎉 SUCCESS RESPONSE & USER FEEDBACK
         * ===================================
         * 
         * `redirect()->route('profile.edit')`: Redirects the user back to the profile editing page.
         * `->with('success', ...)`: Stores a one-time "flash" message in the session.
         *   This message will be displayed to the user on the next page load, providing immediate feedback.
         */
        return redirect()->route('profile.edit')
                        ->with('success', '✅ Profile updated successfully!');
    }

    /**
     * UPDATE PASSWORD
     * ==============
     * 
     * This method handles requests to change the user's password.
     * It requires the user to provide their current password for security verification.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Password Verification**: Comparing a plain-text password to a hashed one.
     * - **Strong Password Rules**: Enforcing complex password requirements.
     * - **Password Hashing**: Storing new passwords securely.
     * - **Security Best Practices**: Separating password changes from other profile updates.
     * 
     * AUTHENTICATION: This route is protected by Laravel's `auth` middleware.
     * ROUTE: `PUT /profile/password`
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /**
         * 👤 GET AUTHENTICATED USER
         * =====================
         * 
         * Fetch the currently logged-in user instance.
         */
        $user = Auth::user();

        /**
         * ✅ PASSWORD CHANGE VALIDATION
         * ===========================
         * 
         * Strict validation rules for a secure password change:
         * 1. `current_password`: Must be present and correct (verified manually below).
         * 2. `password`: The new password.
         *    - `Password::min(8)->letters()->numbers()`: Custom Laravel rule enforcing minimum length, letters, and numbers.
         *    - `confirmed`: Requires a `password_confirmation` field that matches `password`.
         */
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
        ]);

        /**
         * 🔒 VERIFY CURRENT PASSWORD
         * =========================
         * 
         * This is a critical security step: ensure the user knows their existing password
         * before allowing a change. This prevents unauthorized password changes if a session is hijacked.
         * 
         * `Hash::check(plainTextPassword, hashedPassword)`: Safely compares a plain-text password
         *   (from the form) with a hashed password (from the database).
         */
        if (!Hash::check($validated['current_password'], $user->password)) {
            // If passwords don't match, redirect back to the form with a specific error message.
            return redirect()->back()
                           ->withErrors(['current_password' => 'The current password you provided is incorrect.']);
        }

        /**
         * 🔄 UPDATE PASSWORD IN DATABASE
         * =============================
         * 
         * `user->update(['password' => ...])`: Updates the user's password.
         * Laravel automatically hashes the new password before saving it to the database
         * if it's assigned to the `password` attribute on an Eloquent model.
         */
        $user->update([
            'password' => $validated['password'], // Laravel will automatically hash this
        ]);

        /**
         * 🎉 SUCCESS MESSAGE WITH SECURITY CONTEXT
         * =======================================
         * 
         * Provide clear feedback to the user that their password has been successfully changed.
         * In a production application, you might also consider logging this action for security auditing
         * or sending a notification email to the user.
         */
        return redirect()->route('profile.edit')
                        ->with('success', '✅ Password updated successfully!');
    }

    /**
     * DELETE ACCOUNT (PERMANENT AND IRREVERSIBLE)
     * ==========================================
     * 
     * This method allows an authenticated user to permanently delete their account.
     * **This is an extremely sensitive and irreversible operation.**
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Strict Password Confirmation**: Requiring re-verification for destructive actions.
     * - **Data Cleanup Strategy**: Deciding what happens to related data (e.g., blog posts).
     * - **User Session Management**: Logging out the user after account deletion.
     * - **Redirects**: Guiding the user after an irreversible action.
     * 
     * AUTHENTICATION: This route is protected by Laravel's `auth` middleware.
     * ROUTE: `DELETE /profile`
     * 
     * IMPORTANT PRODUCTION NOTE:
     * In real-world applications, consider implementing "soft deletes" (adding a `deleted_at` timestamp
     * column) instead of permanent deletion. This allows for data recovery, maintains audit trails,
     * and is often required for compliance reasons.
     */
    public function destroy(Request $request): RedirectResponse
    {
        /**
         * 👤 GET AUTHENTICATED USER
         * =====================
         * 
         * Fetch the currently logged-in user instance.
         */
        $user = Auth::user();

        /**
         * ✅ PASSWORD CONFIRMATION FOR ACCOUNT DELETION
         * ===========================================
         * 
         * Before proceeding with account deletion, require the user to re-enter their password.
         * This prevents accidental deletions and adds a layer of security.
         */
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->password, $user->password)) {
            // If password doesn't match, redirect back with an error.
            return redirect()->back()
                           ->withErrors(['password' => 'The password is incorrect. Please try again.']);
        }

        /**
         * 🗑️ DATA CLEANUP STRATEGY
         * ========================
         * 
         * When a user account is deleted, you must decide what happens to data related to that user.
         * Common strategies:
         * 1. **Delete all related data (demonstrated here)**: All of the user's blog posts are permanently deleted.
         * 2. Keep data but anonymize it: Posts remain, but the author is set to 'Anonymous' or a generic user.
         * 3. Transfer data: Posts are assigned to an administrator or another user.
         *
         * For this educational application, we choose the simplest option: deleting all associated blog posts.
         * `user->blogPosts()->delete()`: This will delete all `BlogPost` records that belong to this user.
         */
        $user->blogPosts()->delete();

        /**
         * 🚶 LOGOUT USER
         * =============
         * 
         * After deleting the account, the user's session must be destroyed.
         * `Auth::logout()`: Invalidates the current user's session.
         * `request->session()->invalidate()`: Regenerates the session ID to prevent session fixation.
         * `request->session()->regenerateToken()`: Regenerates the CSRF token to prevent token reuse.
         */
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        /**
         * ⛔ DELETE THE USER ACCOUNT
         * ==========================
         * 
         * `user->delete()`: Permanently removes the user record from the `users` table.
         */
        $user->delete();

        /**
         * 🎉 REDIRECT TO HOME PAGE WITH SUCCESS MESSAGE
         * ============================================
         * 
         * Redirect the user to the application's home page after account deletion
         * and provide a clear, final confirmation message.
         */
        return redirect('/')
                      ->with('success', "✅ Your account has been successfully deleted. We're sorry to see you go!");
    }
}

/*
 * 🎓 EDUCATIONAL SUMMARY - PROFILE CONTROLLER
 * ============================================
 * 
 * This `ProfileController` demonstrates essential aspects of user account management,
 * providing a robust and secure foundation for any web application.
 * 
 * ✅ KEY LEARNINGS:
 * - **Secure User Operations**: How to implement password changes and account deletion safely.
 * - **Laravel Validation**: Advanced usage of validation rules including `Rule::unique`.
 * - **Eloquent ORM**: Efficiently updating and deleting records.
 * - **Authorization**: Ensuring users interact only with their own data.
 * - **User Experience**: Providing clear feedback and guiding users through sensitive actions.
 * - **Production Considerations**: Highlighting the difference between educational examples and real-world production practices (e.g., soft deletes).
 * 
 * This controller serves as an excellent resource for understanding how to build secure
 * and user-friendly profile management features in Laravel applications with Inertia.js.
 */ 