<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifica tu dirección de email')
            ->line('Por favor haz clic en el botón de abajo para verificar tu dirección de email.')
            ->action('Verificar Email', $verificationUrl)
            ->line('Si no creaste una cuenta, no es necesario realizar ninguna acción.');
    }

    protected function verificationUrl($notifiable)
    {
        $frontendUrl = config('app.frontend_url');
        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification())
            ],
            false // Esto evita que se genere una URL absoluta
        );
        
        // Extraer la parte de la query string
        $queryString = parse_url($url, PHP_URL_QUERY);
        
        // Construir la nueva URL con el dominio frontend
        return "{$frontendUrl}/verify-email?{$queryString}";
    }
}
