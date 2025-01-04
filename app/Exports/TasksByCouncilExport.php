<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromView;

class TasksByCouncilExport implements FromView
{
    protected $councilId;

    public function __construct($councilId)
    {
        $this->councilId = $councilId;
    }

    public function view(): View
    {
        // Fetch tasks linked to council through council positions
        $tasks = Task::whereHas('assignedCouncilPosition', function ($query) {
            $query->where('council_id', $this->councilId);
        })
        ->with(['assignedCouncilPosition.user', 'approvedByCouncilPosition.user'])
        ->get();

        return view('exports.tasks-by-council', [
            'tasks' => $tasks,
        ]);
    }
}
