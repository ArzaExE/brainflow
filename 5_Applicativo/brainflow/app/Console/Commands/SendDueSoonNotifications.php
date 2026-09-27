<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueSoon;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDueSoonNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:notify-due-soon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invia una notifica agli assegnatari dei task che scadono il giorno seguente';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $tasks = Task::query()
            ->whereNotNull('due_date') // estrae solo i task con la data di scadenza
            ->whereDate('due_date', $tomorrow)  // controlla data
            // Escludo i task che si trovano nella colonna "completati" (is_done)
            ->whereHas('column', function ($query) {
                $query->where('is_done', false);
            })
            // Escludo i task dei progetti archiviati (archived_at NULL = attivo)
            ->whereHas('project', function ($query) {
                $query->whereNull('archived_at');
            })
            ->with(['assignees', 'project', 'column'])
            ->get();

        $sent = 0;

        foreach ($tasks as $task) {
            foreach ($task->assignees as $assignee) {
                $assignee->notify(new TaskDueSoon($task));
                $sent++;
            }
        }

        Log::info("tasks:notify-due-soon completato — {$sent} notifiche accodate.");

        return self::SUCCESS;
    }
}
