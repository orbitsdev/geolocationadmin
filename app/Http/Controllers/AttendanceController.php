<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Attendance;
use App\Helpers\ApiResponse;
use App\Http\Resources\AttendanceResource;
use App\Traits\EventValidationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        //
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
        'council_position_id' => 'required|exists:council_positions,id',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'device_id' => 'nullable|string',
        'device_name' => 'nullable|string',
        'selfie_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
    ]);

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
        $selfiePath = null;

        // Handle selfie image upload
        if ($request->hasFile('selfie_image')) {
            $selfiePath = $request->file('selfie_image')->store('selfies', 'public');
        }

        // Retrieve or create attendance record
        $attendance = Attendance::firstOrNew([
            'event_id' => $eventId,
            'council_position_id' => $validatedData['council_position_id'],
        ]);

        // Update attendance details
        $attendance->fill([
            'latitude' => $validatedData['latitude'],
            'longitude' => $validatedData['longitude'],
            'status' => 'present',
            'check_in_time' => now(),
            'device_id' => $validatedData['device_id'] ?? $attendance->device_id,
            'device_name' => $validatedData['device_name'] ?? $attendance->device_name,
            'selfie_image' => $selfiePath ?? $attendance->selfie_image,
        ])->save();

        $attendance->load('event', 'councilPosition'); // Eager load necessary relations

        DB::commit();

        return ApiResponse::success(new AttendanceResource($attendance), 'Attendance marked successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse::error('Failed to mark attendance', 500);
    }
}


public function checkOut(Request $request, $councilId, $eventId)
{
    $validatedData = $request->validate([
        'council_position_id' => 'required|exists:council_positions,id',
         'latitude' => 'required|numeric',  // Include latitude for distance validation
        'longitude' => 'required|numeric'
    ]);

    $event = Event::where('council_id', $councilId)->findOrFail($eventId);

    if ($event->restrict_event) {
        try {
            $this->validateEventTiming($event);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode());
        }
    }

    // // Optional: Validate distance from event location if needed
    // $distance = $this->calculateDistance(
    //     $event->latitude,
    //     $event->longitude,
    //     $validatedData['latitude'],
    //     $validatedData['longitude']
    // );

    // if ($distance > $event->radius + 5) { // 5 meters tolerance
    //     return ApiResponse::error(
    //         "You are not within the geofence. Calculated distance: {$distance} meters. Geofence radius: {$event->radius} meters.",
    //         403
    //     );
    // }

    $attendance = Attendance::where('event_id', $event->id)
        ->where('council_position_id', $validatedData['council_position_id'])
        ->firstOrFail();

    DB::beginTransaction();

    try {
        $attendance->update([
            'check_out_time' => now(),
            'status' => 'checked-out',
        ]);

        $attendance->load('event', 'councilPosition'); // Load necessary relations
        DB::commit();

        return ApiResponse::success(new AttendanceResource($attendance), 'Checked out successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse::error('Failed to check out', 500);
    }
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
