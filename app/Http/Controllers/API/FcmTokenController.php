<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FcmToken;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    //
    public function register(Request $request){
        $validated = $request->validate([
            'device_token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios'
        ]);

        $student = Auth::user();
        
         // Update or create FCM token record
        FcmToken::updateOrCreate(
            [
                'student_id' => $student->id,
                'device_token' => $validated['device_token'],
            ],
            [
                'device_type' => $validated['device_type'] ?? null,
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'FCM token registered successfully'
        ]);
    }
}
