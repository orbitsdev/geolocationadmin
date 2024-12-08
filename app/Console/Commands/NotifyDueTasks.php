<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
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

        $user = User::find(5);

        if (!$user) {
            $this->error('User with ID 5 not found.');
            return;
        }


        foreach ($user->deviceTokens as $token) {
                            FCMController::sendPushNotification(
                                $token->device_token,
                                'Task Due Soon',
                                "Due date test ",
                                [
                                   
                                    'user_id' => $user->id,
                                    'notification_type' => 'task_due',
                                   
                                ]
                            );

                            $this->info('send');
                        }
    
        // Send a test notification
        // $user->notify(new TaskUpdate(0, 'Test Notification', 'This is a test notification to check the system.'));
    
        // $this->info('Test notification sent to user ID 5.');
        // $now = Carbon::now();
        // $soon = $now->addMinutes(1); // Define "about to be due" threshold

        // // Fetch tasks nearing their due date
        // $tasks = Task::where('due_date', '>=', $now)
        //     ->where('due_date', '<=', $soon)
        //     ->where('is_done', false)
        //     ->get();

        //     foreach ($tasks as $task) {
        //         $user = $task->user;
    
        //         if ($user) {
        //             // Notify via Laravel Notifications
        //             $user->notify(new \App\Notifications\TaskUpdate($task->id, 'Task Due Soon', $task->title));
    
        //             // Notify via push notification
        //             foreach ($user->deviceTokens as $token) {
        //                 FCMController::sendPushNotification(
        //                     $token->device_token,
        //                     'Task Due Soon',
        //                     "{$task->title} - Due: " . Carbon::parse($task->due_date)->format('M d, Y h:i A'),
        //                     [
        //                         'council_position_id' => $task->council_position_id,
        //                         'user_id' => $user->id,
        //                         'notification' => 'task_due',
        //                         'task_id' => $task->id,
        //                         'due_date' => $task->due_date,
        //                     ]
        //                 );
        //             }
    
        //             $this->info("Notified user {$user->id} for task {$task->id}");
        //         }
        //     }
    }
}
