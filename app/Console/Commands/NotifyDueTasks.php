<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\FCMController;

class NotifyDueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-due-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users of tasks nearing their due date';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        
      
        // \Log::info('NotifyDueTasks started.');

        // FCMController::sendPushNotification('dtDt-Ti8QJGx9yqJKM1MUI:APA91bF8s23xMbSH45U6DMTEpkTe6LANBupnkPdSSOjv37f0F5MoKPeq3KN3SCXRdX16JZYAWje3hoAk9wdmU32kUtXfiAOzUVnO5xnKNfa4ggvq4wwdEaA', 'Task Assigned', 'test', [

        //     'notification' => 'due_task',
        // ]);

        Log::info('NotifyDueTasks started.');


        
        $now = Carbon::now();
       // Uncomment the line for the threshold you want to use:

// $threshold = $now->addYear(); // Add 1 year to current time
$threshold = $now->addMonths(1); // Add 1 month to current time
// $threshold = $now->addWeek(); // Add 1 week to current time
// $threshold = $now->addDays(1); // Add 1 day to current time
// $threshold = $now->addMinutes(30); // Add 30 minutes to current time

// $threshold = Carbon::now()->subYear(); // Subtract 1 year from current time
// $threshold = Carbon::now()->subMonths(1); // Subtract 1 month from current time
// $threshold = Carbon::now()->subDays(3); // Subtract 3 days from current time
// $threshold = Carbon::now()->subMinutes(30); // Subtract 30 minutes from current time

        // Fetch tasks near their due date
        $tasks = Task::where('due_date', '>=', $now)
            ->where('due_date', '<=', $threshold)
            ->where('is_done', false)
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('No tasks nearing their due date.');
            \Log::info('No tasks nearing their due date.');
            return;
        }

        foreach ($tasks as $task) {
            $user = $task->councilPosition->user ?? null;

            if (!$user) {
                \Log::warning("No user found for task ID: {$task->id}");
                continue;
            }

            // Send notification via Laravel Notification
            // $user->notify(new \App\Notifications\TaskDueNotification($task));

            // Send push notification (FCM example)
            foreach ($user->deviceTokens as $token) {
                FCMController::sendPushNotification(
                    $token,
                    'Task Due Soon',
                    "Task: {$task->title} is due soon at " . $task->due_date->format('M d, Y h:i A'),
                    [
                        'task_id' => $task->id,
                        'due_date' => $task->due_date,
                    ]
                );
            }

            $this->info("Notification sent for task '{$task->title}' to user '{$user->name}'.");
        }

        \Log::info('NotifyDueTasks completed.');

   
    }
}
