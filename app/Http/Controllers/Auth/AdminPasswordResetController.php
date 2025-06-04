<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Support\Facades\DB;

class AdminPasswordResetController extends Controller
{
    /**
     * Show the admin password reset form.
     *
     * @return \Illuminate\View\View
     */
    public function showForgotForm()
    {
        return view('auth.admin-forgot-password');
    }

    /**
     * Send a reset link to the given admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Log the request for debugging
        Log::info('Admin password reset request received for email: ' . $request->email);
        
        $request->validate(['email' => 'required|email']);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            Log::warning('Admin not found for password reset: ' . $request->email);
            return back()->withInput()->withErrors([
                'email' => 'We cannot find an admin with that email address.'
            ]);
        }

        // Log admin found
        Log::info('Admin found for password reset: ' . $admin->id . ' - ' . $admin->email);
        
        // Generate a unique token for this reset request
        $token = Str::random(64);
        
        // Store the token in the password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $admin->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // Create reset URL with the token
        $resetUrl = URL::temporarySignedRoute(
            'admin.password.reset',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $admin->getKey(),
                'hash' => sha1($admin->email),
                'token' => $token,
            ],
        );

        // Log the reset URL for debugging
        Log::info('Admin password reset URL generated: ' . $resetUrl);

        try {
            // Send email with reset link
            $admin->notify(new AdminResetPasswordNotification($resetUrl));
            
            Log::info('Admin password reset notification sent successfully');
            return back()->with('status', 'Password reset link sent to your email. Please check your inbox.');
        } catch (\Exception $e) {
            Log::error('Failed to send admin password reset email: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            
            return back()->withInput()->withErrors([
                'email' => 'Failed to send password reset link. Please try again later.'
            ]);
        }
    }

    /**
     * Show the admin password reset form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @param  string  $hash
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request, $id, $hash)
    {
        // Check if the URL signature is valid
        if (!$request->hasValidSignature()) {
            return view('auth.reset-error', [
                'error' => 'Invalid or expired password reset link.'
            ]);
        }
        
        $email = $request->query('email');
        $token = $request->query('token');
        
        // Find the admin by ID
        $admin = Admin::find($id);
        
        if (!$admin) {
            return view('auth.reset-error', [
                'error' => 'Admin not found.'
            ]);
        }
        
        // Verify hash matches
        if (!hash_equals($hash, sha1($admin->email))) {
            return view('auth.reset-error', [
                'error' => 'Invalid password reset link.'
            ]);
        }
        
        // Verify token exists and is valid
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $admin->email)
            ->first();
            
        if (!$tokenRecord || $tokenRecord->token !== $token) {
            return view('auth.reset-error', [
                'error' => 'Invalid or already used password reset link.'
            ]);
        }
        
        // Check if token is expired (optional additional check)
        if (Carbon::parse($tokenRecord->created_at)->addMinutes(Config::get('auth.verification.expire', 60))->isPast()) {
            return view('auth.reset-error', [
                'error' => 'Password reset link has expired.'
            ]);
        }
        
        return view('auth.admin-reset-password', [
            'id' => $id,
            'hash' => $hash,
            'email' => $email ?? $admin->email,
            'token' => $token
        ]);
    }

    /**
     * Reset the admin's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'hash' => 'required',
            'email' => 'required|email',
            'token' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/', // at least one uppercase letter
                'regex:/[a-z]/', // at least one lowercase letter
                'regex:/[0-9]/', // at least one number
                'regex:/[^a-zA-Z0-9]/' // at least one special character
            ],
        ]);

        try {
            // Find the admin by ID
            $admin = Admin::find($request->id);
            
            if (!$admin) {
                Log::warning('Admin not found for password reset: ID ' . $request->id);
                return back()->withInput()->withErrors([
                    'email' => 'Admin not found'
                ]);
            }
            
            // Verify the email matches the admin's email
            if ($admin->email !== $request->email) {
                Log::warning('Email mismatch in password reset attempt for admin ID: ' . $request->id);
                return redirect('/')->with('error', 'Invalid reset link');
            }

            // Verify the hash matches
            if ($request->hash !== sha1($admin->email)) {
                Log::warning('Invalid hash in password reset attempt for admin ID: ' . $request->id);
                return redirect('/')->with('error', 'Invalid reset link');
            }
            
            // Verify token exists and is valid
            $tokenRecord = DB::table('password_reset_tokens')
                ->where('email', $admin->email)
                ->first();
                
            if (!$tokenRecord || $tokenRecord->token !== $request->token) {
                Log::warning('Invalid or already used token in password reset attempt for admin ID: ' . $request->id);
                return back()->withInput()->withErrors([
                    'email' => 'This password reset link has already been used or is invalid.'
                ]);
            }

            // Update password
            $admin->password = Hash::make($request->password);
            $admin->save();
            
            // Delete the token to invalidate the reset link
            DB::table('password_reset_tokens')
                ->where('email', $admin->email)
                ->delete();

            // Log successful password change
            Log::info('Admin password reset successful for ID: ' . $admin->id . ' (' . $admin->email . ')');
            
            // Fire password reset event
            event(new PasswordReset($admin));

            // Store email in session and redirect to success page
            return redirect()->route('admin.password.reset.success')
                ->with('reset_email', $admin->email);
            
        } catch (\Exception $e) {
            Log::error('Admin password reset failed: ' . $e->getMessage());
            return back()->withInput()->withErrors([
                'email' => 'Failed to reset password: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the password reset success page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showResetSuccessPage(Request $request)
    {
        $email = $request->session()->get('reset_email', 'your account');
        return view('auth.reset-success', [
            'email' => $email
        ]);
    }
} 