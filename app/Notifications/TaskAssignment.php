<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignment extends Notification
{
    use Queueable;

    protected $task;
    protected $assigner;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, User $assigner)
    {
        $this->task = $task;
        $this->assigner = $assigner;
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
        // Genera l'URL per andare direttamente alla pagina del task
        $taskUrl = route('projects.show', $this->task->project->id);

        $mail = (new MailMessage)
            ->subject('Ti è stato assegnato un nuovo task · BrainFlow')
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($this->assigner->name . ' ti ha assegnato il task **' . $this->task->title . '** nel progetto **' . $this->task->project->name . '**.');

        // La descrizione non è obbligatoria: la mostro solo se presente
        if (!empty($this->task->description)) {
            $mail->line('**Descrizione:** ' . $this->task->description);
        }

        // La data di scadenza non è obbligatoria: la mostro solo se presente
        if (!empty($this->task->due_date)) {
            $mail->line('**Scadenza:** ' . $this->task->due_date->format('d.m.Y'));
        }

        return $mail
            ->action('Vai al progetto', $taskUrl)
            ->line('Buon lavoro!')
            ->salutation('Il team di BrainFlow');
    }
}
