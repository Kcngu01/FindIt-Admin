<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;

class VerificationController extends Controller
{
    /**
     * Verify email address
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        // Add debugging
        \Illuminate\Support\Facades\Log::info('Email verification attempt', [
            'id' => $request->id,
            'hash' => $request->hash,
            'signature_valid' => $request->hasValidSignature(),
            'full_url' => $request->fullUrl(),
            'query_string' => $request->getQueryString(),
        ]);
        
        // Check if the URL is valid
        if (!$request->hasValidSignature()) {
            \Illuminate\Support\Facades\Log::warning('Invalid verification signature', [
                'id' => $request->id,
                'hash' => $request->hash,
                'query' => $request->query(),
                'expires' => $request->query('expires'),
                'signature' => $request->query('signature'),
            ]);
            
            // Check if the request wants JSON response
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification link or link has expired'
                ], 400);
            }
            
            // Return view for web browsers
            return view('auth.verification-error', [
                'error' => 'Invalid verification link or link has expired'
            ]);
        }

        try {
            $student = Student::findOrFail($request->id);
            
            \Illuminate\Support\Facades\Log::info('Found student for verification', [
                'student_id' => $student->id,
                'email' => $student->email,
            ]);

            // Check if the student has already verified their email
            if ($student->hasVerifiedEmail()) {
                \Illuminate\Support\Facades\Log::info('Student email already verified', [
                    'student_id' => $student->id,
                    'email' => $student->email,
                ]);
                
                // Check if the request wants JSON response
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Email already verified'
                    ]);
                }
                
                // Return view for web browsers
                return view('auth.verification-success', [
                    'message' => 'Your email has already been verified. You can now use all features of the app.'
                ]);
            }

            // Mark the email as verified
            if ($student->markEmailAsVerified()) {
                event(new Verified($student));
                
                \Illuminate\Support\Facades\Log::info('Student email verified successfully', [
                    'student_id' => $student->id,
                    'email' => $student->email,
                ]);
            }

            // Check if the request wants JSON response
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email has been verified successfully'
                ]);
            }
            
            // Return view for web browsers
            return view('auth.verification-success', [
                'message' => 'Your email has been verified successfully! You can now use all features of the app.'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error during email verification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Return view for web browsers with error message
            return view('auth.verification-error', [
                'error' => 'An error occurred while verifying your email: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check if the current user's email is verified
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificationCheck(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'verified' => false
            ], 401);
        }
        
        return response()->json([
            'success' => true,
            'verified' => !is_null($user->email_verified_at)
        ]);
    }

    /**
     * Resend the email verification notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(Request $request)
    {
        $student = $request->user();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'You need to be logged in to request a verification link'
            ], 401);
        }

        if ($student->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ]);
        }

        // Send verification email
        $student->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification link has been sent to your email'
        ]);
    }
} 