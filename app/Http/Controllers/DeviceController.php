<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
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
    public function storeOrUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'device_token' => 'required|string',
            'device_id' => 'required|string',
            'device_name' => 'nullable|string',
            'device_type' => 'nullable|string',
        ]);

        // Retrieve the authenticated user using Sanctum
        $user = $request->user(); // This retrieves the user associated with the token

        // Find or create the device for this user
        $device = Device::updateOrCreate(
            ['user_id' => $user->id, 'device_id' => $validatedData['device_id']],
            [
                'device_token' => $validatedData['device_token'],
                'device_name' => $validatedData['device_name'] ?? null,
                'device_type' => $validatedData['device_type'] ?? null,
            ]
        );

        return ApiResponse::success($device, 'Device registered/updated successfully');
    }

    // Optionally delete a device (e.g., if the user logs out)
    public function destroy(Request $request, $deviceId)
    {
        // Retrieve the authenticated user using Sanctum
        $user = $request->user();

        // Find the device by user_id and device_id
        $device = Device::where('user_id', $user->id)->where('device_id', $deviceId)->first();

        if ($device) {
            $device->delete();
            return ApiResponse::success(null, 'Device removed successfully');
        } else {
            return ApiResponse::error('Device not found', 404);
        }
    }

}
