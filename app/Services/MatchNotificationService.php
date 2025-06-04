<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemMatch;
use App\Models\FcmToken;
use App\Models\StudentNotification;
use Illuminate\Support\Facades\Log;

class MatchNotificationService
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Send notification to the owner of a lost item when a new match is found
     *
     * @param ItemMatch $match The match record
     * @return bool
     */
    public function sendMatchNotification(ItemMatch $match)
    {
        try {
            // Get the lost item
            $lostItem = $match->lostItem;
            
            // Get the student who reported the lost item
            $student = $lostItem->student;
            
            if (!$student) {
                Log::error('Cannot send match notification: Student not found for lost item #' . $lostItem->id);
                return false;
            }
            
            // Get user FCM tokens
            $tokens = FcmToken::where('student_id', $student->id)
                        ->pluck('device_token')
                        ->toArray();
            
            // Get the found item
            $foundItem = $match->foundItem;
            
            // Prepare notification content
            $title = 'Potential Item Match Found';
            $body = "We found a potential match for your lost {$lostItem->name}!";
            
            // Additional data for the app to process
            $data = [
                'match_id' => (string) $match->id,
                'lost_item_id' => (string) $lostItem->id,
                'found_item_id' => (string) $foundItem->id,
                'similarity_score' => (string) $match->similarity_score,
                'notification_type' => 'potential_match'
            ];
            
            // Store notification in the database
            $notification = StudentNotification::create([
                'student_id' => $student->id,
                'title' => $title,
                'body' => $body,
                'type' => 'potential_match',
                'data' => $data,
                'status' => 'unread'
            ]);
            
            // Send push notification if tokens are available
            if (!empty($tokens)) {
                $this->firebaseService->sendMulticastNotification($tokens, $title, $body, $data);
                Log::info("Match notification sent to student {$student->id} for match {$match->id}");
            } else {
                Log::info("No FCM tokens found for student {$student->id}, notification stored in database only");
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error sending match notification', [
                'match_id' => $match->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    
    /**
     * Send notification when no potential matches are found for a lost item
     *
     * @param Item $lostItem The lost item
     * @return bool
     */
    public function sendNoMatchesNotification(Item $lostItem)
    {
        try {
            // Make sure this is a lost item
            if ($lostItem->type !== 'lost') {
                return false;
            }
            
            // Get the student who reported the lost item
            $student = $lostItem->student;
            
            if (!$student) {
                Log::error('Cannot send no-match notification: Student not found for lost item #' . $lostItem->id);
                return false;
            }
            
            // Get user FCM tokens
            $tokens = FcmToken::where('student_id', $student->id)
                        ->pluck('device_token')
                        ->toArray();
            
            // Prepare notification content
            $title = 'Lost Item Reported';
            $body = "Your {$lostItem->name} has been reported as lost. We'll notify you when potential matches are found if you have attached image in your report.";
            
            // Additional data for the app to process
            $data = [
                'lost_item_id' => (string) $lostItem->id,
                'notification_type' => 'no_matches'
            ];
            
            // Store notification in the database
            $notification = StudentNotification::create([
                'student_id' => $student->id,
                'title' => $title,
                'body' => $body,
                'type' => 'no_matches',
                'data' => $data,
                'status' => 'unread'
            ]);
            
            // Send push notification if tokens are available
            if (!empty($tokens)) {
                $this->firebaseService->sendMulticastNotification($tokens, $title, $body, $data);
                Log::info("No-match notification sent to student {$student->id} for lost item {$lostItem->id}");
            } else {
                Log::info("No FCM tokens found for student {$student->id}, notification stored in database only");
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error sending no-match notification', [
                'lost_item_id' => $lostItem->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
} 