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
        
        
        return view('auth.reset-password', [
            'id' => $id,
            'hash' => $hash,
            'email' => $email ?? $student->email
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

        // Create reset URL
        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $student->getKey(),
                'hash' => sha1($student->getEmailForVerification()),
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
            \Illuminate\Support\Facades\Log::error('Failed to send password reset email: ' . $e->getMessage());
            
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
            'password' => 'required|min:8|confirmed',
        ]);

        try {
            // Find the student by email
            $student = Student::where('email', $request->email)->first();
            
            if (!$student) {
                return back()->withInput()->withErrors([
                    'email' => 'User not found'
                ]);
            }

            // Verify the hash matches
            if ($request->hash !== sha1($student->getEmailForVerification())) {
                return redirect('/')->with('error', 'Invalid reset link');
            }

            // Update password
            $student->password = Hash::make($request->password);
            $student->save();

            // Fire password reset event
            event(new PasswordReset($student));

            // Return with success message to display success section
            return back()->with('success', true)->with('status', 'Password has been reset successfully');
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Password reset failed: ' . $e->getMessage());
            return back()->withInput()->withErrors([
                'email' => 'Failed to reset password: ' . $e->getMessage()
            ]);
        }
    }
} 