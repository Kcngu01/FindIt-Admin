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
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request)
    {
        // Check if the URL is valid
        if (!$request->hasValidSignature()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link or link has expired'
            ], 400);
        }

        $student = Student::findOrFail($request->id);

        // Check if the student has already verified their email
        if ($student->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified'
            ]);
        }

        // Mark the email as verified
        if ($student->markEmailAsVerified()) {
            event(new Verified($student));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email has been verified successfully'
        ]);
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