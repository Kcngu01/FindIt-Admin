<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        try {
            // $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
            // Use storage_path helper to get the absolute path
        $credentialsPath = storage_path('app/firebase/findit-96de8-firebase-adminsdk-fbsvc-519b8eecb8.json');
        
        // Check if the file exists before using it
        if (!file_exists($credentialsPath)) {
            Log::error('Firebase credentials file not found at: ' . $credentialsPath);
            return;
        }
        
        $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase initialization error: ' . $e->getMessage());
        }
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        try {
            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data)
                ->withAndroidConfig(['priority' => 'high'])
                ->toToken($token);
                
            return $this->messaging->send($message);
        } catch (\Exception $e) {
            Log::error('Firebase notification error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function sendMulticastNotification($tokens, $title, $body, $data = [])
    {
        if (empty($tokens)) {
            Log::info('No tokens provided for multicast notification');
            return false;
        }
        
        try {
            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data)
                ->withAndroidConfig(['priority' => 'high']);
                
            return $this->messaging->sendMulticast($message, $tokens);
        } catch (\Exception $e) {
            Log::error('Firebase multicast notification error: ' . $e->getMessage());
            return false;
        }
    }
}