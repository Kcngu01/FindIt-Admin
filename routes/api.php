<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\VerificationController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\FcmTokenController;
use App\Http\Controllers\API\NotificationController;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$controller = ApiController::class;
// Login endpoint for mobile app
Route::post('/login', [ApiController::class,'login'])->name('login');
Route::middleware('auth:sanctum')->post('/logout', [ApiController::class,'logout'])->name('logout');
Route::post('/register', [ApiController::class,'register'])->name('register');

// Email verification routes
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.send');
Route::get('/email/verify-check', [VerificationController::class, 'verificationCheck'])
    ->middleware('auth:sanctum')
    ->name('verification.check');

Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])
->name('password.email');


// Protected routes that require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Get all students
    Route::get('/students', function () {
        $students = Student::select('id', 'name', 'email', 'matric_no')->get();
        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    });

    // Get a specific student by ID
    Route::get('/students/{id}', function ($id) {
        $student = Student::select('id', 'name', 'email', 'matric_no')
            ->where('id', $id)
            ->first();
            
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'student' => $student
        ]);
    });
    
    // Get current logged in user's information (if student)
    Route::get('/profile', function (Request $request) {
        $user = $request->user();
        
        if ($user instanceof \App\Models\Student) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'matricNo' => $user->matric_no,
                    'email_verified' => !is_null($user->email_verified_at),
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'User is not a student'
        ], 403);
    });

    // Routes that require email verification
    Route::middleware('verified')->group(function () {
        Route::get('/items', [ApiController::class, 'getItems']);
        Route::post('/items/student-id', [ApiController::class, 'getItemsByStudentId']);
        Route::get('/items/{id}', [ApiController::class, 'getItemById']);
        Route::post('/items', [ApiController::class, 'createItem']);
        Route::patch('/items/edit/{id}', [ApiController::class, 'updateItem']);
        Route::post('/items/edit/{id}', [ApiController::class, 'updateItem']);
        Route::delete('/items/delete/{id}', [ApiController::class, 'deleteItem']);
        Route::post('/items/claim',[ApiController::class,'claimItem']);
        Route::post('/items/claim/check', [ApiController::class, 'checkClaim']);
    });

    Route::get('/claims/student/{id}',[ApiController::class, 'getAllClaims']);
    Route::get('/claim/details/{id}',[ApiController::class,'getClaimDetails']);
    Route::post('/fcm-token', [FcmTokenController::class, 'register']);

    // Notification Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'getNotifications']);
        // Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount']);
        // Route::post('/notifications/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
        // Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markSingleAsRead']);
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/notifications', [NotificationController::class, 'store']);
    });
});

Route::prefix('/characteristics')->as('characteristics.')->middleware(['auth:sanctum', 'verified'])->group(function(){
    $controller = ApiController::class;
    Route::get('/categories', [$controller, 'getCategories']);
    Route::get('/locations', [$controller, 'getLocations']);
    Route::get('/colours', [$controller, 'getColours']);
    Route::get('/categories/{id}', [$controller, 'getCategoryById']);
    Route::get('/locations/{id}', [$controller, 'getLocationById']);
    Route::get('/colours/{id}', [$controller, 'getColourById']);
});





