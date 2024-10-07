<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Attendance;
use App\Helpers\ApiResponse;
use App\Http\Resources\AttendanceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
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

        // Validate if current time is within event start and end times


    if ($event->restrict_event) {
        $formattedStartTime = $event->start_time->format('l, F j, Y, g:i A');
        $formattedEndTime = $event->end_time->format('l, F j, Y, g:i A');
        $currentTime = now()->format('l, F j, Y, g:i A');


        if (now()->lt($event->start_time)) {
            return ApiResponse::error(
                "The event has not started yet. It starts on {$formattedStartTime}. Current time is {$currentTime}. Please wait until the event begins.",
                403
            );
        }

        if (now()->gt($event->end_time)) {
            return ApiResponse::error(
                "The event has already ended. It ended on {$formattedEndTime}. Current time is {$currentTime}. You cannot check in at this time.",
                403
            );
        }
    }

        // Fetch existing attendance for this council position and event
        $attendance = Attendance::where('event_id', $eventId)
            ->where('council_position_id', $validatedData['council_position_id'])
            ->first();

        // Calculate distance between event location and user's location
        $distance = $this->calculateDistance(
            $event->latitude,
            $event->longitude,
            $validatedData['latitude'],
            $validatedData['longitude']
        );

        if ($distance <= $event->radius) {
            DB::beginTransaction();

            try {
                $selfiePath = null;

                // Handle selfie image upload
                if ($request->hasFile('selfie_image')) {
                    $selfiePath = $request->file('selfie_image')->store('selfies', 'public');
                }

                if ($attendance) {
                    // Update existing attendance record
                    $attendance->update([
                        'latitude' => $validatedData['latitude'],
                        'longitude' => $validatedData['longitude'],
                        'status' => 'present',
                        'check_in_time' => now(),
                        'device_id' => $validatedData['device_id'] ?? $attendance->device_id,
                        'device_name' => $validatedData['device_name'] ?? $attendance->device_name,
                        'selfie_image' => $selfiePath ?? $attendance->selfie_image,
                    ]);
                } else {
                    // Create a new attendance record
                    $attendance = Attendance::create([
                        'event_id' => $event->id,
                        'council_position_id' => $validatedData['council_position_id'],
                        'latitude' => $validatedData['latitude'],
                        'longitude' => $validatedData['longitude'],
                        'status' => 'present',
                        'check_in_time' => now(),
                        'device_id' => $validatedData['device_id'] ?? null,
                        'device_name' => $validatedData['device_name'] ?? null,
                        'selfie_image' => $selfiePath,
                    ]);
                }

                $attendance->loadRelations();
                DB::commit();

                return ApiResponse::success(new AttendanceResource($attendance), 'Attendance marked successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                return ApiResponse::error('Failed to mark attendance', 500);
            }
        } else {
            return ApiResponse::error(
                "You are not within the geofence. Calculated distance: {$distance} meters. Geofence radius: {$event->radius} meters.",
                403
            );
        }
    }

    public function checkOut(Request $request, $councilId, $eventId)
    {
        $validatedData = $request->validate([
            'council_position_id' => 'required|exists:council_positions,id',
        ]);

        $event = Event::where('council_id', $councilId)->findOrFail($eventId);

        if ($event->restrict_event) {


                    // Validate if current time is within event start and end times
                    $formattedStartTime = $event->start_time->format('l, F j, Y, g:i A');
                    $formattedEndTime = $event->end_time->format('l, F j, Y, g:i A');
                    $currentTime = now()->format('l, F j, Y, g:i A');

                    // Validate if current time is within event start and end times
                    if (now()->lt($event->start_time)) {
                        return ApiResponse::error(
                            "The event has not started yet. It starts on {$formattedStartTime}. Current time is {$currentTime}. You cannot check out at this time.",
                            403
                        );
                    }

                    if (now()->gt($event->end_time)) {
                        return ApiResponse::error(
                            "The event has already ended. It ended on {$formattedEndTime}. Current time is {$currentTime}. You cannot check out at this time.",
                            403
                        );
                    }
        }

        $attendance = Attendance::where('event_id', $event->id)
            ->where('council_position_id', $validatedData['council_position_id'])
            ->firstOrFail();

        DB::beginTransaction();

        try {
            $attendance->update([
                'check_out_time' => now(),
                'status' => 'checked-out',
            ]);

            $attendance->loadRelations();
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
