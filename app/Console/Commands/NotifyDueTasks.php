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

    

   
    }
}
