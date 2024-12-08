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

        
      
        \Log::info('NotifyDueTasks started.');

    $user = User::find(5);

    if (!$user) {
        $this->error('User with ID 5 not found.');
        \Log::error('User with ID 5 not found.');
        return;
    }

    if ($user->deviceTokens->isEmpty()) {
        $this->error('No device tokens found for the user.');
        \Log::error('No device tokens found for user ID 5.');
        return;
    }

    foreach ($user->deviceTokens() as $token) {
        try {
            FCMController::sendPushNotification(
                $token,
                'Task Due Soon',
                "Due date test",
                [
                    'user_id' => $user->id,
                    'notification_type' => 'task_due',
                ]
            );
            $this->info("Notification sent to token: {$token}");
            \Log::info("Notification sent to token: {$token}");
        } catch (\Exception $e) {
            $this->error("Failed to send notification to token: {$token}. Error: {$e->getMessage()}");
            \Log::error("Failed to send notification to token: {$token}. Error: {$e->getMessage()}");
        }
    }

    \Log::info('NotifyDueTasks completed.');
    }
}
