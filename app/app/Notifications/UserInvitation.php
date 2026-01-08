<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class UserInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $appName = config('app.name', 'Simplead ERP');
        $expiresIn = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject(Lang::get('You\'ve been invited to :app', ['app' => $appName]))
            ->greeting(Lang::get('Welcome to :app!', ['app' => $appName]))
            ->line(Lang::get('An administrator has created an account for you.'))
            ->line(Lang::get('Click the button below to set your password and access the application.'))
            ->action(Lang::get('Set Your Password'), $url)
            ->line(Lang::get('This link will expire in :count minutes.', ['count' => $expiresIn]))
            ->line(Lang::get('If you did not expect this invitation, no further action is required.'));
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
