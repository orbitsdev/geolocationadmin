<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromView;

class AttendanceExport implements FromView
{
    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function view(): View
    {
        return view('exports.attendance-export', [
            'event' => $this->event,
            'attendances' => $this->event->attendances()->with('councilPosition.user')->get(),
        ]);
    }
}
