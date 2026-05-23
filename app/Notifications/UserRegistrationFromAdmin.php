<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegistrationFromAdmin extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $registeredBy;
    protected $temporaryPassword;

    public function __construct(User $registeredBy, string $temporaryPassword)
    {
        $this->registeredBy = $registeredBy;
        $this->temporaryPassword = $temporaryPassword;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // URL della pagina di login per il primo accesso
        $loginUrl = route('login');

        return (new MailMessage)
            ->subject('Il tuo account è stato creato · BrainFlow')
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($this->registeredBy->name . ' ha creato un account BrainFlow a tuo nome.')
            ->line('Puoi accedere usando il tuo indirizzo email e la password temporanea qui sotto:')
            ->line('**Password temporanea:** ' . $this->temporaryPassword)
            ->line('Questa password resterà valida finché non la cambi. Per impostarne una nuova, usa la funzione **"Password dimenticata?"** nella pagina di accesso: riceverai un link per scegliere la tua password personale.')
            ->action('Vai al login', $loginUrl)
            ->line('Per la sicurezza del tuo account, ti consigliamo di cambiare la password al primo accesso.')
            ->salutation('Il team di BrainFlow');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
