<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromView;

class EventsByCouncilExport implements FromView
{
    protected $councilId;

    public function __construct($councilId)
    {
        $this->councilId = $councilId;
    }

    public function view(): View
    {
        // Get all events for the given council ID
        $events = Event::where('council_id', $this->councilId)->get();

        return view('exports.events-by-council', [
            'events' => $events,
        ]);
    }
}
