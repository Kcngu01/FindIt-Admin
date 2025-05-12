<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $appUrl = config('app.url');
        
        // Generate a signed URL
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            // false // Don't use absolute URL 
        );
        
        return $verifyUrl;
        // Format for API response - remove the domain part from the signed URL
        // This will be appended to the API URL in the frontend
        // return $appUrl . '/api/email/verify' . substr($verifyUrl, strpos($verifyUrl, '?'));
    }
} 