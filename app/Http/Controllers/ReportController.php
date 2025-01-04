<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Council;
use Illuminate\Http\Request;
use App\Models\CouncilPosition;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TasksByCouncilExport;
use App\Exports\EventsByCouncilExport;
use App\Exports\CollectionsByCouncilExport;
use App\Exports\TasksByCouncilPositionExport;
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


    public function exportEventsByCouncil($councilId)
    {
        $council = Council::findOrFail($councilId);

        $createdDate = now()->format('F_j_Y');
        $filename = 'Council-' . $council->id . '-Events-' . $createdDate . '.xlsx';

        return Excel::download(new EventsByCouncilExport($councilId), $filename);
    }

    public function exportCollectionsByCouncil($councilId)
    {
        $council = Council::findOrFail($councilId);

        $createdDate = now()->format('F_j_Y');
        $filename = 'Council-' . $council->id . '-Collections-' . $createdDate . '.xlsx';

        return Excel::download(new CollectionsByCouncilExport($councilId), $filename);
    }


    public function exportTasksByCouncilPosition($councilPositionId)
    {
        $councilPosition = CouncilPosition::findOrFail($councilPositionId);
    $name = $councilPosition->user->fullName();

        $createdDate = now()->format('F_j_Y');
        $filename = $name.'-Tasks-' . $createdDate . '.xlsx';

        return Excel::download(new TasksByCouncilPositionExport($councilPositionId), $filename);
    }

    public function exportTasksByCouncil($councilId)
    {
        $council = Council::findOrFail($councilId);

        $createdDate = now()->format('F_j_Y');
        $filename = 'Council-' . $council->id . '-Tasks-' . $createdDate . '.xlsx';

        return Excel::download(new TasksByCouncilExport($councilId), $filename);
    }
}
