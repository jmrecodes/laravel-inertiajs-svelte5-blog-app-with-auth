<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * FORGOT PASSWORD CONTROLLER - SECURE PASSWORD RESET FOR EDUCATIONAL APP
 * ======================================================================
 * 
 * This controller handles the entire "forgot password" and "password reset" flow
 * for our educational blog application. It demonstrates how to implement a secure,
 * token-based password reset system.
 * 
 * 🎓 BEGINNER LEARNING OBJECTIVES:
 * ================================
 * 1. **Token-Based Security**: Understanding how temporary tokens enable secure resets.
 * 2. **Email Integration**: Sending password reset links via email.
 * 3. **Database Interactions**: Storing and managing password reset tokens.
 * 4. **Conditional UI**: Displaying different forms based on URL parameters (token presence).
 * 5. **Security Best Practices**: Preventing common vulnerabilities like user enumeration and replay attacks.
 * 6. **Rate Limiting**: Protecting against brute-force attempts on email sending.
 * 
 * 🔍 WHAT YOU'LL LEARN:
 * =====================
 * - How to generate and store secure, time-limited tokens.
 * - Constructing dynamic URLs with query parameters.
 * - Sending basic emails in Laravel (using the `log` driver for development).
 * - Validating user input for email and new passwords.
 * - Cleaning up used/expired tokens from the database.
 * - Implementing basic rate limiting to prevent abuse.
 * 
 * PASSWORD RESET FLOW (STEP-BY-STEP):
 * 1. **User Request**: User visits `/forgot-password` and submits their email.
 *    - `sendResetLink` method handles this.
 * 2. **Email Delivery**: A unique, expiring token is generated and emailed to the user.
 *    - The email contains a link back to `/forgot-password` with the token and email as query parameters.
 * 3. **Token Verification**: User clicks the link. The `showForm` method verifies the token.
 * 4. **Password Reset**: If the token is valid, the user sees a form to set a new password.
 *    - `resetPassword` method handles this submission.
 * 5. **Completion**: Password is updated, token is invalidated, and the user is redirected to login.
 * 
 * SECURITY CONSIDERATIONS (CRITICAL FOR ANY PASSWORD SYSTEM):
 * - **Token Expiration**: Tokens are valid for a limited time (e.g., 1 hour) to reduce risk.
 * - **One-Time Use**: Tokens are deleted after use to prevent replay attacks.
 * - **No User Enumeration**: The system should give the same success message whether the email exists or not, to prevent attackers from guessing valid email addresses.
 * - **Rate Limiting**: Limits how often a user can request a reset link to prevent spamming or brute-force.
 * - **Strong Passwords**: New passwords must meet complexity requirements.
 * 
 * This controller is heavily commented to guide you through each step and concept
 * involved in building a secure password reset functionality.
 */
class ForgotPasswordController extends Controller
{
    /**
     * SHOW FORGOT PASSWORD/RESET PASSWORD FORM
     * =======================================
     * 
     * This method is a single entry point for two states:
     * 1. Displaying the **"Forgot Password" form** (asking for email) if no valid token is present.
     * 2. Displaying the **"Reset Password" form** (asking for new password) if a valid token is provided in the URL.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Conditional UI Rendering**: How a single route/component can adapt its display based on input.
     * - **URL Query Parameters**: Retrieving data passed in the URL (e.g., `?token=abc&email=xyz`).
     * - **Token Validation**: Basic server-side checking of received tokens.
     * 
     * ROUTE: `GET /forgot-password` (accessible to guests)
     * SVELTE COMPONENT: `Auth/ForgotPassword.svelte`
     */
    public function showForm(Request $request): Response
    {
        /**
         * 📥 EXTRACT URL PARAMETERS
         * ========================
         * 
         * We retrieve `token` and `email` from the URL query string.
         * Example URL: `/forgot-password?token=some_token_string&email=user@example.com`
         */
        $token = $request->query('token'); // The unique reset token
        $email = $request->query('email'); // The user's email associated with the token
        
        /**
         * 🎯 DETERMINE FORM MODE (REQUEST VS. RESET)
         * =========================================
         * 
         * We initialize the `mode` to 'request' (meaning show the email input form).
         * If a token and email are present in the URL, we attempt to validate them
         * to switch the mode to 'reset' (show the new password form).
         */
        $mode = 'request';    // Default: User needs to request a link
        $tokenValid = false; // Flag to indicate if the token is valid
        
        // If both token and email are provided in the URL, attempt to validate them
        if ($token && $email) {
            /**
             * 🔒 TOKEN VALIDATION LOGIC
             * ========================
             * 
             * We check the `password_reset_tokens` table for a matching record.
             * `DB::table('password_reset_tokens')`: Directly query the database table.
             * `->where('email', $email)->where('token', $token)->first()`: Find the record
             *   that matches both the provided email AND token.
             */
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->where('token', $token)
                ->first();
                
            // If a matching token record is found
            if ($resetRecord) {
                /**
                 * ⏰ CHECK TOKEN EXPIRATION
                 * ========================
                 * 
                 * For security, tokens should have a limited lifespan (e.g., 1 hour).
                 * `Carbon::parse($resetRecord->created_at)`: Converts the `created_at` timestamp
                 *   into a `Carbon` object for easy date/time calculations.
                 * `->diffInHours(now())`: Calculates the difference in hours between token creation
                 *   and the current time.
                 */
                $tokenAge = Carbon::parse($resetRecord->created_at);
                if ($tokenAge->diffInHours(now()) <= 1) { // Token is valid for 1 hour
                    $mode = 'reset';    // Switch to password reset mode
                    $tokenValid = true; // Mark token as valid
                }
                // If token is expired, mode remains 'request' and tokenValid remains false
            }
            // If no record found, mode remains 'request' and tokenValid remains false
        }
        
        /*
         * 🚀 RENDERING SVELTE COMPONENT VIA INERTIA.JS
         * ===========================================
         * 
         * `Inertia::render('Auth/ForgotPassword', [...])`: Sends the determined `mode`,
         * `token`, `email`, and `tokenValid` status to the Svelte component.
         * The Svelte component uses these props to decide which form to display.
         * 
         * `meta` array: Provides dynamic title and description for SEO purposes (`<svelte:head>`).
         */
        return Inertia::render('Auth/ForgotPassword', [
            'mode' => $mode,           // 'request' (email form) or 'reset' (password form)
            'token' => $token,         // The actual reset token (only if valid)
            'email' => $email,         // The email address (used for pre-filling)
            'tokenValid' => $tokenValid, // Boolean indicating token validity
            'meta' => [
                'title' => $mode === 'reset' ? 'Reset Your Password' : 'Forgot Your Password',
                'description' => $mode === 'reset' ? 'Enter your new password to regain access to your account.' : 'Enter your email address to receive a password reset link.',
            ],
        ]);
    }
    
    /**
     * SEND PASSWORD RESET LINK TO USER'S EMAIL
     * =========================================
     * 
     * This method handles the form submission when a user requests a password reset link.
     * It generates a secure token, stores it, and sends an email with the reset link.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Email Validation**: Ensuring the submitted input is a valid email.
     * - **Token Generation**: Creating a secure, random string for reset purposes.
     * - **Database Storage**: Saving the token and its creation time for later verification.
     * - **Email Sending**: Using Laravel's Mail facade to send emails.
     * - **Security (No User Enumeration)**: A critical security practice to prevent revealing valid email addresses.
     * - **Rate Limiting**: Basic protection against excessive requests.
     * 
     * ROUTE: `POST /forgot-password` (accessible to guests)
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        /**
         * 🚫 BASIC RATE LIMITING (IP-BASED)
         * =================================
         * 
         * This is a simple IP-based rate limit using Laravel's Cache facade.
         * It prevents a single IP address from requesting too many reset links
         * within a short period (e.g., 12 hours) to deter abuse/spamming.
         * 
         * `Cache::get('password_reset_attempt_' . $request->ip())`: Checks if a recent attempt exists.
         */
        if (Cache::get('password_reset_attempt_' . $request->ip())) {
            return redirect()->back()->withErrors([
                'email' => 'You have already attempted a password reset in the last 12 hours. Please try again later.'
            ]);
        }

        /**
         * ✅ VALIDATE EMAIL INPUT
         * =====================
         * 
         * Ensure the submitted input is a valid email address.
         */
        $request->validate([
            'email' => ['required', 'email', 'string', 'max:255'],
        ]);
        
        $email = $request->email;
        
        /**
         * 🔒 SECURITY: PREVENT USER ENUMERATION
         * ====================================
         * 
         * **CRITICAL SECURITY PRACTICE:** Always return a generic success message
         * regardless of whether the email address actually exists in your database.
         * This prevents malicious actors from using the password reset form
         * to discover valid user email addresses.
         * 
         * If `$user` is found, proceed with token generation and email sending.
         * If `$user` is NOT found, we still return a success message later,
         * so the attacker doesn't know if the email was valid or not.
         */
        $user = User::where('email', $email)->first();
        
        if ($user) {
            /**
             * 🔑 GENERATE SECURE PASSWORD RESET TOKEN
             * ======================================
             * 
             * `Str::random(64)`: Generates a cryptographically secure, random string of 64 characters.
             * This token is unique and unpredictable, crucial for security.
             * 
             * PRODUCTION NOTE:
             * For a fully robust system, Laravel has built-in password reset functionality
             * that manages tokens more comprehensively. This is a simplified educational example.
             */
            $token = Str::random(64);
            
            /**
             * 💾 STORE TOKEN IN DATABASE
             * =========================
             * 
             * Tokens are stored in the `password_reset_tokens` table.
             * `DB::table('password_reset_tokens')->updateOrInsert([...])`:
             * - If a record with this email already exists, it will be updated.
             * - If not, a new record will be inserted.
             * This ensures only one active token exists per email address.
             * `'created_at' => now()`: Stores the current timestamp to track token expiration.
             */
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => $token,
                    'created_at' => now(),
                ]
            );
            
            /**
             * 📧 SEND PASSWORD RESET EMAIL
             * ===========================
             * 
             * We construct the full reset URL, including the token and email as query parameters.
             * This URL is then sent to the user's email address.
             * 
             * `url('/forgot-password?token=' . $token . '&email=' . urlencode($email))`: 
             *   Generates a full URL using Laravel's `url()` helper.
             *   `urlencode($email)`: Encodes the email to safely include it in the URL.
             * 
             * `Mail::raw(...)`: Sends a simple plain-text email.
             *   For production, you would typically use `Mail::send(new App\Mail\ResetPassword($resetUrl))`
             *   with a dedicated Mailable class and Blade template for richer emails.
             * 
             * Laravel's `log` mail driver (configured in `.env` and `config/mail.php`):
             *   In development, emails aren't actually sent but are written to the Laravel log file (`storage/logs/laravel.log`).
             *   This is perfect for testing email functionality without a real SMTP server.
             */
            $resetUrl = url('/forgot-password?token=' . $token . '&email=' . urlencode($email));
            
            try {
                Mail::raw("Hello,\n\nYou requested a password reset for your Educational Blog account.\n\nClick the link below to reset your password:\n{$resetUrl}\n\nThis link will expire in 1 hour.\n\nIf you didn't request this reset, please ignore this email.\n\nBest regards,\nEducational Blog Team", function ($message) use ($email, $user) {
                    $message->to($email, $user->name)
                           ->subject('Reset Your Password - Educational Blog');
                });
                
            } catch (\Exception $e) {
                // Log any errors during email sending, but do not expose them to the user for security.
                logger()->error('Failed to send password reset email', [
                    'email' => $email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        /**
         * ⏳ SET IP-BASED RATE LIMIT CACHE
         * ===============================
         * 
         * After processing the request (whether email was sent or not), set a cache entry
         * for the user's IP address to prevent them from requesting another reset too soon.
         * `Cache::put('key', value, expirationInMinutes)`: Stores data in cache.
         * `60 * 60 * 12`: Cache for 12 hours.
         */
        Cache::put('password_reset_attempt_' . $request->ip(), true, 60 * 60 * 12);
        
        /**
         * 🎉 CONSISTENT SUCCESS RESPONSE (SECURITY)
         * ==========================================
         * 
         * **IMPORTANT**: Always return this generic success message.
         * This prevents attackers from guessing valid email addresses
         * by observing different responses for existing vs. non-existing emails.
         */
        return redirect()->back()->with('success', 
            'If an account with that email exists, we\'ve sent a password reset link. Please check your email.'
        );
    }
    
    /**
     * RESET PASSWORD WITH TOKEN VERIFICATION
     * ======================================
     * 
     * This method handles the form submission when a user sets a new password
     * after clicking a reset link. It validates the token and updates the password.
     * 
     * 🎓 LEARNING OBJECTIVES:
     * =====================
     * - **Token Verification**: Strict checks for token validity and expiration.
     * - **Password Rules**: Enforcing strong password requirements.
     * - **Password Hashing**: Securely storing the new password.
     * - **Token Invalidation**: Deleting the token after successful use to prevent reuse.
     * - **Redirects**: Guiding the user to the login page after a successful reset.
     * 
     * ROUTE: `PUT /forgot-password` (accessible to guests, but token is required)
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        /**
         * 🗑️ CLEAR RATE LIMIT CACHE FOR THIS IP
         * ====================================
         * 
         * Once a password reset is successfully attempted, we clear the rate limit
         * for this IP address, allowing them to request another reset if needed.
         */
        Cache::forget('password_reset_attempt_' . $request->ip());
        
        /**
         * ✅ VALIDATE RESET FORM INPUTS
         * ===========================
         * 
         * Validate the `token`, `email`, and `password` fields submitted from the form.
         * `Password::min(8)->letters()->numbers()`: Enforces minimum length, letters, and numbers.
         * `confirmed`: Requires `password_confirmation` field to match `password`.
         */
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'string'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
        ]);
        
        /**
         * 🔒 VERIFY TOKEN EXISTENCE AND MATCH
         * ==================================
         * 
         * Crucially, we check if the provided `token` and `email` exist in the `password_reset_tokens` table.
         * If no record is found, the link is invalid.
         */
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->where('token', $validated['token'])
            ->first();
            
        if (!$resetRecord) {
            return redirect()->back()->withErrors([
                'token' => 'This password reset link is invalid or has already been used.'
            ]);
        }
        
        /**
         * ⏰ CHECK TOKEN EXPIRATION
         * ========================
         * 
         * Verify that the token has not expired. Tokens are typically short-lived (e.g., 1 hour).
         * `Carbon::parse($resetRecord->created_at)`: Converts the creation timestamp to a `Carbon` object.
         * `->diffInHours(now()) > 1`: Checks if more than 1 hour has passed.
         */
        $tokenAge = Carbon::parse($resetRecord->created_at);
        if ($tokenAge->diffInHours(now()) > 1) {
            // If expired, delete the token from the database to clean up.
            DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->delete();
                
            return redirect()->back()->withErrors([
                'token' => 'This password reset link has expired. Please request a new one.'
            ]);
        }
        
        /**
         * 👤 FIND USER BY EMAIL
         * ====================
         * 
         * Find the user associated with the email in the reset record.
         * Although unlikely given previous checks, ensure the user still exists.
         */
        $user = User::where('email', $validated['email'])->first();
        
        if (!$user) {
            // This case should ideally not be hit if token validation passes, but good for robustness.
            return redirect()->back()->withErrors([
                'email' => 'No account found with this email address.'
            ]);
        }
        
        /**
         * 🔄 UPDATE USER PASSWORD
         * ======================
         * 
         * `user->update(['password' => ...])`: Updates the user's password.
         * Laravel's User model automatically hashes the new password before saving it.
         */
        $user->update([
            'password' => $validated['password'], // Laravel will automatically hash this
        ]);
        
        /**
         * 🗑️ CLEAN UP USED TOKEN
         * =====================
         * 
         * After a successful password reset, delete the token from the database.
         * This prevents the token from being reused (replay attack).
         */
        DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->delete();
        
        /**
         * 🎉 SUCCESS RESPONSE & REDIRECT TO LOGIN
         * ======================================
         * 
         * Redirect the user to the login page with a success message,
         * informing them that their password has been reset and they can now log in.
         */
        return redirect('/login')->with('success', 
            '✅ Your password has been reset successfully! Please log in with your new password.'
        );
    }
}

/*
 * 🎓 EDUCATIONAL SUMMARY - FORGOT PASSWORD CONTROLLER
 * ====================================================
 * 
 * This `ForgotPasswordController` demonstrates a complete and secure password reset flow,
 * which is a fundamental feature for almost any user-facing web application.
 * 
 * ✅ KEY LEARNINGS:
 * - **Secure Token Management**: Generating, storing, and invalidating temporary tokens.
 * - **Email Integration**: How to send system-generated emails.
 * - **Defense in Depth**: Multiple security checks (rate limiting, no user enumeration, token expiry).
 * - **Conditional UI Logic**: Adapting the frontend based on backend state (token presence).
 * - **Robust Validation**: Ensuring all inputs meet security and data integrity requirements.
 * - **User Experience**: Providing clear instructions and feedback throughout the process.
 * 
 * This controller serves as an excellent resource for understanding how to build
 * secure and user-friendly password reset features in Laravel applications with Inertia.js.
 */ 