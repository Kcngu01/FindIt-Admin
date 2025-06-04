<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    /**
     * Show the password reset form.
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
        
        // Find the student by ID
        $student = Student::find($id);
        
        if (!$student) {
            return view('auth.reset-error', [
                'error' => 'User not found.'
            ]);
        }
        
        // Verify hash matches
        if (!hash_equals($hash, sha1($student->getEmailForVerification()))) {
            return view('auth.reset-error', [
                'error' => 'Invalid password reset link.'
            ]);
        }
        
        // Verify token exists and is valid
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $student->email)
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
        
        return view('auth.reset-password', [
            'id' => $id,
            'hash' => $hash,
            'email' => $email ?? $student->email,
            'token' => $token
        ]);
    }
    
    /**
     * Show the forgot password form.
     *
     * @return \Illuminate\View\View
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }
    
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $student = Student::where('email', $request->email)->first();

        if (!$student) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'We cannot find a user with that email address.'
                ], 404);
            }
            
            return back()->withInput()->withErrors([
                'email' => 'We cannot find a user with that email address.'
            ]);
        }

        // Generate a unique token for this reset request
        $token = Str::random(64);
        
        // Store the token in the password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $student->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // Create reset URL with the token
        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $student->getKey(),
                'hash' => sha1($student->getEmailForVerification()),
                'token' => $token,
            ],
        );

        try {
            // Send email with reset link
            $student->notify(new \App\Notifications\ResetPasswordNotification($resetUrl));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password reset link sent to your email'
                ]);
            }
            
            return back()->with('status', 'Password reset link sent to your email. Please check your inbox.');
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send password reset link: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()->withErrors([
                'email' => 'Failed to send password reset link. Please try again later.'
            ]);
        }
    }

    /**
     * Reset the user's password.
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
            // Find the student by ID
            $student = Student::find($request->id);
            
            if (!$student) {
                Log::warning('Student not found for password reset: ID ' . $request->id);
                return back()->withInput()->withErrors([
                    'email' => 'User not found'
                ]);
            }
            
            // Verify the email matches the student's email
            if ($student->email !== $request->email) {
                Log::warning('Email mismatch in password reset attempt for student ID: ' . $request->id);
                return redirect('/')->with('error', 'Invalid reset link');
            }

            // Verify the hash matches
            if ($request->hash !== sha1($student->getEmailForVerification())) {
                Log::warning('Invalid hash in password reset attempt for student ID: ' . $request->id);
                return redirect('/')->with('error', 'Invalid reset link');
            }
            
            // Verify token exists and is valid
            $tokenRecord = DB::table('password_reset_tokens')
                ->where('email', $student->email)
                ->first();
                
            if (!$tokenRecord || $tokenRecord->token !== $request->token) {
                Log::warning('Invalid or already used token in password reset attempt for student ID: ' . $request->id);
                return back()->withInput()->withErrors([
                    'email' => 'This password reset link has already been used or is invalid.'
                ]);
            }

            // Update password
            $student->password = Hash::make($request->password);
            $student->save();
            
            // Delete the token to invalidate the reset link
            DB::table('password_reset_tokens')
                ->where('email', $student->email)
                ->delete();

            // Log successful password change
            Log::info('Student password reset successful for ID: ' . $student->id . ' (' . $student->email . ')');
            
            // Fire password reset event
            event(new PasswordReset($student));

            // Redirect to success page
            return redirect()->route('password.reset.success')
                ->with('reset_email', $student->email);
            
        } catch (\Exception $e) {
            Log::error('Password reset failed: ' . $e->getMessage());
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
        return view('auth.reset-password-success', [
            'email' => $email
        ]);
    }
} 