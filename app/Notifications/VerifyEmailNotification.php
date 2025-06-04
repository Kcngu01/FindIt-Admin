<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    // $notifiable is an object that represents the entity receiving the notification, typically a User model instance. It contains methods like getKey() (to get the user ID) and getEmailForVerification() (to get the email address).
    // parameter is automatically passed to notification methods and contains the model that the notification is being sent to.
    protected function verificationUrl($notifiable)
    {
        $appUrl = config('app.url');
        
        // Generate a signed URL
        // clicking link will follow the route and other parameters will be passed to the route
        // the APP_URL from the .env file is used as the base URL when generating the verification URL.
        // The URL::temporarySignedRoute() method uses this base URL from the config (which gets its value 
        // from APP_URL in .env) to construct the full verification URL
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
        
        return $verifyUrl;
    }
    
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        
        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not create an account, no further action is required.');
    }
} 