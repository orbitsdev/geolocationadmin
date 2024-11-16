<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CouncilPosition;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EventResource;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $councilId)
    {
        // Get the page and perPage from the query string, default to page 1 and 10 items per page
        $page = $request->query('page', 1);
        $perPage = $request->query('perPage', 10);

        // Fetch the events for the given council with pagination
        $events = Event::where('council_id', $councilId)
            ->with('councilPosition', 'attendances')
            ->paginate($perPage, ['*'], 'page', $page);

        // Return paginated events
        return ApiResponse::paginated($events, 'Events retrieved successfully', EventResource::class);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $councilId)
    {
        $validatedData = $request->validate(    [
            'council_position_id' => 'required|exists:council_positions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'is_active' => 'sometimes|boolean',
            'restrict_event' => 'sometimes|boolean',
            'max_capacity' => 'sometimes|nullable|integer|min:1',
            'type' => 'sometimes|nullable|string',
        ]);

        
        $validatedData['council_id'] = $councilId;

        
        DB::beginTransaction();

        try {
            $event = Event::create($validatedData);

            DB::commit();

            return ApiResponse::success(new EventResource($event), 'Event created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to create event', 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show($councilId, $eventId)
    {
        $event = Event::where('council_id', $councilId)
            ->with('councilPosition', 'attendances.councilPosition')
            ->findOrFail($eventId);

        return ApiResponse::success(new EventResource($event), 'Event retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $councilId, $eventId)
    {
        $event = Event::where('council_id', $councilId)->findOrFail($eventId);

        $validatedData = $request->validate([
            'council_position_id' => 'sometimes|exists:council_positions,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'radius' => 'sometimes|numeric',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date',
            'is_active' => 'sometimes|boolean',
            'max_capacity' => 'nullable|integer',
            'type' => 'nullable|string',
        ]);

        // Begin transaction
        DB::beginTransaction();

        try {
            $event->update($validatedData);

            DB::commit();

            return ApiResponse::success(new EventResource($event), 'Event updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to update event', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($councilId, $eventId)
    {
        $event = Event::where('council_id', $councilId)->findOrFail($eventId);

        // Begin transaction
        DB::beginTransaction();

        try {
            $event->delete();

            DB::commit();

            return ApiResponse::success(null, 'Event deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to delete event', 500);
        }
    }
    public function fetchByCouncilPositionOrCouncil(Request $request, $councilPositionId)
{
    // Fetch the council position to get the related council_id
    $councilPosition = CouncilPosition::findOrFail($councilPositionId);
    $councilId = $councilPosition->council_id;

    // Get the page and perPage from the query string, default to page 1 and 10 items per page
    $page = $request->query('page', 1);
    $perPage = $request->query('perPage', 10);

    // Query events based on council_position_id or council_id
    $events = Event::where('council_position_id', $councilPositionId)
        ->orWhere('council_id', $councilId)
        ->with('councilPosition', 'attendances')
        ->paginate($perPage, ['*'], 'page', $page);

    // Return the paginated events using the appropriate resource
    return ApiResponse::paginated($events, 'Events retrieved successfully', EventResource::class);
}

}
