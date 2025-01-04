<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use App\Models\Event;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromView;

class AttendanceByCouncilPositionExport implements FromView
{
    protected $councilId;
    protected $councilPositionId;

    public function __construct($councilId, $councilPositionId)
    {
        $this->councilId = $councilId;
        $this->councilPositionId = $councilPositionId;
    }

    public function view(): View
    {
        // Get all events associated with the council
        $events = Event::where('council_id', $this->councilId)->get();

        // Get all attendances for these events and council position
        $attendances = Attendance::whereIn('event_id', $events->pluck('id'))
            ->where('council_position_id', $this->councilPositionId)
            ->with(['event', 'councilPosition.user'])
            ->get();

        return view('exports.attendance-by-council-position', [
            'events' => $events,
            'attendances' => $attendances,
        ]);
    }
}
