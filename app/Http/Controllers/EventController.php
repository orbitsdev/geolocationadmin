<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Event;
use App\Models\Attendance;
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
            ->withRelation()
            ->paginate($perPage, ['*'], 'page', $page);

        // Return paginated events
        return ApiResponse::paginated($events, 'Events retrieved successfully', EventResource::class);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $councilId)
    {



        $validatedData = $request->validate(  [
            'council_position_id' => 'required|exists:council_positions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric|min:1',
            'specified_location' => 'sometimes|nullable|string',
            'map_location' => 'required|string',
            'place_id' => 'required|string',
            'is_publish' => 'required|boolean', 
            'start_time' => 'required|date|before:end_time',
             'end_time' => 'required|date|after:start_time',
            'is_active' => 'sometimes|boolean',
            'restrict_event' => 'sometimes|boolean',
            'max_capacity' => 'sometimes|nullable|integer|min:1',
            'type' => 'sometimes|nullable|string',
        ]);


        $validatedData['council_id'] = $councilId;


        DB::beginTransaction();

        try {
            $event = Event::create($validatedData);


            if ($validatedData['is_publish']) {
                // Create Post
                $post = Post::create([
                    'council_position_id' => $validatedData['council_position_id'],
                    'title' => $event->title,
                    'content' => $event->description,
                    'description' => 'Published event: ' . $event->title,
                    'postable_type' => Event::class,
                    'postable_id' => $event->id,
                ]);
    
                // Notify users in the same council
                $councilUsers = User::whereHas('councilPositions', function ($query) use ($councilId) {
                    $query->where('council_id', $councilId);
                })->get();
    
                foreach ($councilUsers as $recipient) {
                    foreach ($recipient->devices as $device) {
                        FCMController::sendPushNotification(
                            $device->device_token,
                            'New Event Published',
                            "Event: {$event->title} has been published!",
                            [
                                'notification_type' => 'event',
                                'event_id' => $event->id,
                                'post_id' => $post->id,
                                'council_id' => $councilId,
                            ]
                        );
                    }
                }
            }
            $event->load(['council', 'councilPosition'])->loadCount('attendances');
            DB::commit();

       
            return ApiResponse::success(new EventResource($event), $request->all(), 'Event created successfully',);
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
        ->withRelation()
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
             'radius' => 'required|numeric|min:1',
            'specified_location' => 'sometimes|nullable|string',
            'map_location' => 'required|string',
            'place_id' => 'required|string',
            'is_publish' => 'required|boolean', 
            'start_time' => 'required|date|before:end_time',
             'end_time' => 'required|date|after:start_time',
            'is_active' => 'sometimes|boolean',
            'restrict_event' => 'sometimes|boolean',
            'max_capacity' => 'sometimes|nullable|integer|min:1',
            'type' => 'sometimes|nullable|string',
        ]);

        // Begin transaction
        DB::beginTransaction();

        try {
            $event->update($validatedData);

            if ($validatedData['is_publish'] ?? false) {
                // Prepare post data
                $postData = [
                    'title' => $event->title,
                    'content' => $event->description,
                    'description' => 'Published event: ' . $event->title,
                    'council_id' => $event->council_id,
                    'council_position_id' => $event->council_position_id,
                    'postable_type' => Event::class,
                    'postable_id' => $event->id,
                ];
    
                
                $event->post()->updateOrCreate(['postable_id' => $event->id, 'postable_type' => Event::class], $postData);
    
                // Notify users in the council
                $users = User::whereHas('councilPositions', function ($query) use ($event) {
                    $query->where('council_id', $event->council_id);
                })->get();
    
                foreach ($users as $user) {
                    foreach ($user->devices as $device) {
                        FCMController::sendPushNotification(
                            $device->device_token,
                            'Updated Event Published',
                            "{$event->title} has been updated and published.",
                            [
                                'notification_type' => 'event_update',
                                'event_id' => $event->id,
                                'post_id' => $event->post->id ?? null,
                                'council_id' => $event->council_id,
                            ]
                        );
                    }
                }
            } else {
                // If `is_publish` is false, delete the associated post
                $event->post()->delete();
            }

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
