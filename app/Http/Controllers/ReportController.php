<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function exportEventAttendance($eventId)
    {   
        
        $event = Event::findOrFail($eventId);

        $createdDate = $event->created_at
            ? $event->created_at->format('F_j_Y')
            : 'Unknown_Date';

        $filename = $event->title .'-'. $createdDate . '.xlsx';

        return Excel::download(new AttendanceExport($event), $filename);
    }
}
