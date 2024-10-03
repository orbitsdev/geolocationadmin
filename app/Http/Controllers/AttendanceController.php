<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Attendance;
use App\Helpers\ApiResponse;
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
            'selfie_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',  // Validate selfie as file
        ]);

        $event = Event::where('council_id', $councilId)->findOrFail($eventId);

        // Calculate distance between event location and user's location
        $distance = $this->calculateDistance(
            $event->latitude,
            $event->longitude,
            $validatedData['latitude'],
            $validatedData['longitude']
        );

        // return ApiResponse::success([
        //     'calculated_distance' => $distance,
        //     'event_latitude' => $event->latitude,
        //     'event_longitude' => $event->longitude,
        //     'user_latitude' => $validatedData['latitude'],
        //     'user_longitude' => $validatedData['longitude'],
        // ], 'Distance calculated successfully');
        

        if ($distance <= $event->radius) {
            DB::beginTransaction();

            try {
                $selfiePath = null;

                // Handle selfie image upload
                if ($request->hasFile('selfie_image')) {
                    $selfiePath = $request->file('selfie_image')->store('selfies', 'public');
                }

                // Create attendance record
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

                DB::commit();

                return ApiResponse::success($attendance, 'Attendance marked successfully');
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
        // Same as before, no change for selfie handling here
        $validatedData = $request->validate([
            'council_position_id' => 'required|exists:council_positions,id',
        ]);

        $event = Event::where('council_id', $councilId)->findOrFail($eventId);

        $attendance = Attendance::where('event_id', $event->id)
            ->where('council_position_id', $validatedData['council_position_id'])
            ->firstOrFail();

        DB::beginTransaction();

        try {
            $attendance->update([
                'check_out_time' => now(),
                'status' => 'checked-out',
            ]);

            DB::commit();

            return ApiResponse::success($attendance, 'Checked out successfully');
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
