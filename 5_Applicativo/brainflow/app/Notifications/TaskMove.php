<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskMove extends Notification
{
    use Queueable;

    protected $task;
    protected $mover;
    protected $fromColumn;
    protected $toColumn;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, User $mover, string $fromColumn, string $toColumn)
    {
        $this->task = $task;
        $this->mover = $mover;
        $this->fromColumn = $fromColumn;
        $this->toColumn = $toColumn;
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

        return (new MailMessage)
            ->subject('Un task è stato spostato · BrainFlow')
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line($this->mover->name . ' ha spostato il task **' . $this->task->title . '** nel progetto **' . $this->task->project->name . '**.')
            ->line('Da **' . $this->fromColumn . '** a **' . $this->toColumn . '**.')
            ->action('Apri il progetto', $taskUrl)
            ->line('Buon lavoro!')
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
