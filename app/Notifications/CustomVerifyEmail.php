<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as DefaultVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends DefaultVerifyEmail
{
    // Mesaggio di verifica email, sovrascrive quello di default di Laravel che è in inglese
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifica la tua email · BrainFlow')
            ->greeting('Ciao e benvenuto in BrainFlow!')
            ->line('Grazie per esserti registrato. Manca solo un passaggio per attivare il tuo account: verifica il tuo indirizzo email cliccando il pulsante qui sotto.')
            ->action('Verifica la mia email', $url)
            ->line('Il link è valido per 60 minuti. Se è scaduto, puoi richiederne uno nuovo dalla pagina di verifica.')
            ->line('Se non hai creato tu questo account, puoi ignorare questa email senza problemi.')
            ->salutation('A presto, il team di BrainFlow');
    }
}
