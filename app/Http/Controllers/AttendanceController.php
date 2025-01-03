<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Attendance;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\EventValidationTrait;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\AttendanceEventResource;
use App\Http\Resources\EventAttendanceResource;

class AttendanceController extends Controller
{

    use EventValidationTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
      
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function checkIn(Request $request, $councilId, $eventId)
{
    $validatedData = $request->validate([
        'check_in_coordinates' => 'required|array',
        'check_in_coordinates.latitude' => 'required|numeric',
        'check_in_coordinates.longitude' => 'required|numeric',
        'device_id' => 'nullable|string',
        'device_name' => 'nullable|string',
        'selfie_image' => ['nullable', 'file', 'mimes:jpeg,png', 'max:50480'],
    ]);

    $user = $request->user();
    $councilPosition = $user->defaultCouncilPosition();

    if (!$councilPosition) {
        return ApiResponse::error('No default council position found for the user.', 403);
    }

    $event = Event::where('council_id', $councilId)->findOrFail($eventId);

    // Validate event timing if restriction is enabled
    if ($event->restrict_event) {
        try {
            $this->validateEventTiming($event);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode());
        }
    }

    // Calculate distance between event location and user's location
    // $distance = $this->calculateDistance(
    //     $event->latitude,
    //     $event->longitude,
    //     $validatedData['latitude'],
    //     $validatedData['longitude']
    // );

    // if ($distance > $event->radius + 5) {
    //     return ApiResponse::error(
    //         "You are not within the geofence. Calculated distance: {$distance} meters. Geofence radius: {$event->radius} meters.",
    //         403
    //     );
    // }

    DB::beginTransaction();

    try {
        // Retrieve or create attendance record
        $attendance = Attendance::firstOrNew([
            'event_id' => $eventId,
            'council_position_id' => $councilPosition->id,
        ]);

        // Handle check-in selfie upload
        if ($request->hasFile('selfie_image')) {
            $attendance
                ->addMediaFromRequest('selfie_image')
                ->preservingOriginal()
                ->toMediaCollection('check_in_selfies');
        }

        // Update attendance details
           $attendance->fill([
            'check_in_coordinates' => $validatedData['check_in_coordinates'], // Save JSON coordinates
            'status' => 'checked-in',
            'check_in_time' => now(),
            'device_id' => $validatedData['device_id'] ?? $attendance->device_id,
            'device_name' => $validatedData['device_name'] ?? $attendance->device_name,
        ])->save();

        $attendance->load('event', 'councilPosition'); // Load necessary relations

        DB::commit();

        return ApiResponse::success(new EventAttendanceResource($event, $attendance), 'Check-in successful');
    } catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse::error('Failed to mark attendance'.$e->getMessage(), 500);
    }



}


public function checkOut(Request $request, $councilId, $eventId)
{
    $validatedData = $request->validate([

        'check_out_coordinates' => 'required|array',
        'check_out_coordinates.latitude' => 'required|numeric',
        'check_out_coordinates.longitude' => 'required|numeric',
        'selfie_image' => ['nullable', 'file', 'mimes:jpeg,png', 'max:50480'],
    ]);
    $user = $request->user();
    $councilPosition = $user->defaultCouncilPosition();

    if (!$councilPosition) {
        return ApiResponse::error('No default council position found for the user.', 403);
    }

    $event = Event::where('council_id', $councilId)->findOrFail($eventId);

    // Validate event timing if restriction is enabled
    if ($event->restrict_event) {
        try {
            $this->validateEventTiming($event);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode());
        }
    }

    $attendance = Attendance::where('event_id', $event->id)
    ->where('council_position_id', $councilPosition->id) // Use the default council position
    ->firstOrFail();

    if ($attendance->check_out_time) {
        return ApiResponse::error('You have already checked out.', 403);
    }

    DB::beginTransaction();

    try {
        // Handle check-out selfie upload
        if ($request->hasFile('selfie_image')) {
            $attendance
                ->addMediaFromRequest('selfie_image')
                ->preservingOriginal()
                ->toMediaCollection('check_out_selfies');
        }


        $attendance->update([
            'check_out_coordinates' => $validatedData['check_out_coordinates'], // Save check-out JSON coordinates
            'check_out_time' => now(),
            'status' => 'checked-out',
        ]);

        $attendance->load('event', 'councilPosition'); // Load necessary relations

        DB::commit();

        return ApiResponse::success(new EventAttendanceResource($event, $attendance), 'Check-out successful');
    } catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse::error('Failed to check out'.$e->getMessage(), 500);
    }
}


public function myAttendance(Request $request)
{

    $page = $request->query('page', 1);
    $perPage = $request->query('perPage', 10);

    $user = $request->user();
    $councilPosition = $user->defaultCouncilPosition();

    if (!$councilPosition) {
        return ApiResponse::error('No  position found for the user.', 403);
    }
 
    
    $attendances = Attendance::whereHas('councilPosition',function($query) use($councilPosition){
        $query->where('council_id', $councilPosition->council_id);
    })->latest()
        ->withRelations() 
        ->paginate($perPage, ['*'], 'page', $page);


    // Return paginated response
    return ApiResponse::paginated($attendances, 'Attendances retrieved successfully', AttendanceEventResource::class);
}

public function showEventAttendance(Request $request,  $eventId)
{

    $page = $request->query('page', 1);
    $perPage = $request->query('perPage', 10);

    $user = $request->user();
    $councilPosition = $user->defaultCouncilPosition();

    if (!$councilPosition) {
        return ApiResponse::error('No default council position found for the user.', 403);
    }
 
    
    $attendances = Attendance::where('event_id', $eventId)
        ->latest()
        ->withRelations() 
        ->paginate($perPage, ['*'], 'page', $page);


    // Return paginated response
    return ApiResponse::paginated($attendances, 'Attendances retrieved successfully', AttendanceEventResource::class);
}

public function showEventAttendanceRecord(Request $request,  $eventId)
{
    $page = $request->query('page', 1);
    $perPage = $request->query('perPage', 10);

    $user = $request->user();
    $councilPosition = $user->defaultCouncilPosition();

    if (!$councilPosition) {
        return ApiResponse::error('No default council position found for the user.', 403);
    }
 
    
    $attendances = Attendance::where('event_id', $eventId)
        ->latest()
        ->withRelations() // Load necessary relationships
        ->paginate($perPage, ['*'], 'page', $page);


    // Return paginated response
    return ApiResponse::paginated($attendances, 'Attendances retrieved successfully', AttendanceEventResource::class);
}




    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c * 1000; // Distance in meters
    }
}
