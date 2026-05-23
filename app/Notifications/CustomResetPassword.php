<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as DefaultResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends DefaultResetPassword
{

    // Messaggio di reset della password, sovrascrive quello di default di Laravel che è in inglese
    public function toMail($notifiable): MailMessage
    {
        // Costruzione del url per il reset
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Reimposta la tua password · BrainFlow')
            ->greeting("Ciao {$notifiable->name}!")
            ->line('Abbiamo ricevuto una richiesta di reimpostazione della password per il tuo account BrainFlow.')
            ->action('Reimposta la password', $url)
            ->line("Il link è valido per {$expireMinutes} minuti. Trascorso questo tempo dovrai richiederne uno nuovo.")
            ->line('Se non hai richiesto tu il reset della password, ignora questa email: la tua password attuale rimane valida e nessuna modifica verrà apportata al tuo account.')
            ->salutation('A presto, il team di BrainFlow');
    }
}
