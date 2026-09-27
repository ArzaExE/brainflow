<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueSoon extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
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
        $taskUrl = route('projects.show', $this->task->project->id);

        return (new MailMessage)
            ->subject('Un task scade domani · BrainFlow')
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line('Il task **' . $this->task->title . '** nel progetto **' . $this->task->project->name . '** scade **domani** (' . $this->task->due_date->format('d.m.Y') . ').')
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
