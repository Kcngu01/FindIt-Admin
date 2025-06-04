<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPasswordNotification extends Notification
{
    /**
     * The password reset URL.
     *
     * @var string
     */
    protected $resetUrl;

    /**
     * Create a new notification instance.
     *
     * @param  string  $resetUrl
     * @return void
     */
    public function __construct($resetUrl)
    {
        $this->resetUrl = $resetUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Admin Password')
            ->line('You are receiving this email because we received a password reset request for your admin account.')
            ->action('Reset Password', $this->resetUrl)
            ->line('This password reset link will expire in ' . config('auth.verification.expire', 60) . ' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
    
} 