<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated student
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $student = Auth::user();
        
        // Get all notifications for the student, ordered by created_at (newest first)
        $notifications = StudentNotification::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Mark a notification as read
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $student = Auth::user();
        
        // Find the notification and ensure it belongs to the authenticated student
        $notification = StudentNotification::where('id', $id)
            ->where('student_id', $student->id)
            ->first();
        
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }
        
        // Only update if currently unread
        if ($notification->status === 'unread') {
            $notification->status = 'read';
            $notification->read_at = Carbon::now();
            $notification->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }
    
    /**
     * Mark all notifications as read for the authenticated student
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        $student = Auth::user();
        
        // Update all unread notifications for the student
        StudentNotification::where('student_id', $student->id)
            ->where('status', 'unread')
            ->update([
                'status' => 'read',
                'read_at' => Carbon::now()
            ]);
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }
    
    /**
     * Create a new notification
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|string|max:255',
            'data' => 'nullable|string',
        ]);
        
        $notification = StudentNotification::create([
            'student_id' => $request->student_id,
            'title' => $request->title,
            'body' => $request->body,
            'type' => $request->type,
            'data' => $request->data,
            'status' => 'unread',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notification created successfully',
            'notification' => $notification
        ], 201);
    }
}