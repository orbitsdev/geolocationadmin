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

        FCMController::sendPushNotification('dtDt-Ti8QJGx9yqJKM1MUI:APA91bF8s23xMbSH45U6DMTEpkTe6LANBupnkPdSSOjv37f0F5MoKPeq3KN3SCXRdX16JZYAWje3hoAk9wdmU32kUtXfiAOzUVnO5xnKNfa4ggvq4wwdEaA', 'Task Assigned', 'test', [

            'notification' => 'due_task',
        ]);

   
    }
}
