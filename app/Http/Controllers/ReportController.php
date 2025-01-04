<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Council;
use Illuminate\Http\Request;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceByCouncilPositionExport;

class ReportController extends Controller
{
    public function exportEventAttendance($eventId)
    {   
        
        $event = Event::findOrFail($eventId);

        $createdDate = $event->created_at
            ? $event->created_at->format('F_j_Y')
            : 'Unknown_Date';

        $filename = $event->title .'-Attendance-'. $createdDate . '.xlsx';

        return Excel::download(new AttendanceExport($event), $filename);
    }

    public function exportAttendanceByCouncilPosition($councilId, $councilPositionId)
    {
        $council = Council::findOrFail($councilId);

        $createdDate = now()->format('F_j_Y');
        $filename = 'Council-' . $council->id . '-Position-' . $councilPositionId . '-Attendance-' . $createdDate . '.xlsx';

        return Excel::download(new AttendanceByCouncilPositionExport($councilId, $councilPositionId), $filename);
    }
}
