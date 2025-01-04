<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromView;

class TasksByCouncilPositionExport implements FromView
{
    protected $councilPositionId;

    public function __construct($councilPositionId)
    {
        $this->councilPositionId = $councilPositionId;
    }

    public function view(): View
    {
        // Fetch tasks for the specific council position ID
        $tasks = Task::where('council_position_id', $this->councilPositionId)
            ->with(['assignedCouncilPosition.user', 'approvedByCouncilPosition.user'])
            ->get();

        return view('exports.tasks-by-council-position', [
            'tasks' => $tasks,
        ]);
    }
}
