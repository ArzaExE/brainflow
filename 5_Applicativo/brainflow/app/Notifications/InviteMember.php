<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InviteMember extends Notification
{
    use Queueable;

    protected $project;
    protected $inviter;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project, User $inviter)
    {
        $this->project = $project;
        $this->inviter = $inviter;
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
        // Genera l'URL per andare direttamente alla pagina del progetto
        $projectUrl = route('projects.show', $this->project->id);

        return (new MailMessage)
            ->subject('Sei stato aggiunto a un nuovo progetto · BrainFlow')
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($this->inviter->name . ' ti ha appena aggiunto al progetto **' . $this->project->name . '**.')
            ->line('Da questo momento puoi collaborare con il team, gestire i task e seguire l\'avanzamento delle attività.')
            ->action('Apri il progetto', $projectUrl)
            ->line('Se hai domande, contatta il Project Manager o l\'amministratore che ti ha invitato.')
            ->salutation('Buon lavoro, il team di BrainFlow');
    }
}
