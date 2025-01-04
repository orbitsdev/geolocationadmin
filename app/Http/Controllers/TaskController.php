<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\CouncilPosition;
use App\Notifications\TaskUpdate;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
    $page = $request->query('page', 1);
        $perPage = $request->query('perPage', 10);
        $councilId = $request->query('councilId', );

        // Fetch the tasks with pagination
        $tasks = Task::taskBelongToCouncilOf($councilId)->withTaskRelations()->paginate($perPage, ['*'], 'page', $page);

        // Return the paginated API response
        return ApiResponse::paginated($tasks, 'Tasks retrieved successfully', \App\Http\Resources\TaskResource::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'council_position_id' => 'required|exists:council_positions,id',
            'title' => 'required|string|max:255',
            'task_details' => 'nullable|string',
            'due_date' => 'required|date',
            'is_lock' => 'sometimes|boolean',  // Validate as boolean
            'is_done' => 'sometimes|boolean',
           'media.*' => ['nullable', 'file', 'max:50480'],
        ]);

        // Set default values if not provided
        $validatedData['status'] = $validatedData['status'] ?? 'To Do';

        DB::beginTransaction();

        try {
            // Create the task with the validated data
            $task = Task::create($validatedData);

            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $mediaItem = $task->addMedia($file)->preservingOriginal()->toMediaCollection('task_media');

                    // $mediaItem = $review->addMedia($file)->toMediaCollection('review_media');
                    $filesData[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ];
                }
            }


        $officer = CouncilPosition::findOrFail($validatedData['council_position_id']);


        if ($officer && $officer->user) {

            $officer->user->notify(new TaskUpdate($task->id, 'Task Assigned', $task->title));

            foreach ($officer->user->deviceTokens() as $token) {
                FCMController::sendPushNotification(
                    $token,
                    'Task Assigned',
                    "{$task->title} - Due: " . ($task->due_date ? Carbon::parse($task->due_date)->format('M d, Y h:i A') : 'No due date'),
                    [
                        'council_position_id' => $officer->id,
                        'user_id' => $officer->user->id,
                        'notification' => 'task',
                        'task_id' => $task->id,
                        'due_date' => $task->due_date, // Send raw due_date for client processing if needed
                    ]
                );
            }
        }
            DB::commit();

            // Return the response with the created task resource
            return ApiResponse::success(new TaskResource($task), 'Task created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // Return an error response in case of failure
            return ApiResponse::error('Failed to create task', 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::withTaskRelations()->findOrFail($id);
        return ApiResponse::success(new TaskResource($task), 'Task retrieved successfully');

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);

        $validatedData = $request->validate([
            'council_position_id' => 'sometimes|exists:council_positions,id',
            'approved_by_council_position_id' => 'sometimes|exists:council_positions,id',
            'title' => 'sometimes|string|max:255',
            'task_details' => 'sometimes|string',
            'due_date' => 'sometimes|date',
            'completed_at' => 'sometimes|nullable|date',
            'status' => 'sometimes|string|in:' . implode(',', array_keys(Task::STATUS_OPTIONS)),
            'is_lock' => 'sometimes|boolean',
            'is_done' => 'sometimes|boolean',
        ]);


        DB::beginTransaction();

        try {
            $task->update($validatedData);

            $task->loadTaskRelations();
            $officer = CouncilPosition::findOrFail($task->assignedCouncilPosition->id);

        if ($officer && $officer->user) {
            $officer->user->notify(new TaskUpdate($task->id, 'Task Assigned', $task->title));
            foreach ($officer->user->deviceTokens() as $token) {
                FCMController::sendPushNotification(
                    $token,
                    'Task Updated',
                    "{$task->title} - Updated Due: " . ($task->due_date ? Carbon::parse($task->due_date)->format('M d, Y h:i A') : 'No due date') .
                    ", Status: " . ucfirst($task->status),
                    [
                        'council_position_id' => $officer->id,
                        'user_id' => $officer->user->id,
                        'notification' => 'task',
                        'task_id' => $task->id,
                        'due_date' => $task->due_date,
                        'status' => $task->status,
                    ]
                );
            }
        }
            DB::commit();

            return ApiResponse::success(new TaskResource($task), 'Task updated successfully');
        } catch (\Exception $e) {

            DB::rollBack();
            \Log::error('Failed to update task', ['error' => $e->getMessage()]);
            return ApiResponse::error('Failed to update task '.$e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);


        DB::beginTransaction();

        try {
            $task->delete();


            DB::commit();

            return ApiResponse::success(null, 'Task deleted successfully');
        } catch (\Exception $e) {

            DB::rollBack();

            return ApiResponse::error('Failed to delete task', 500);
        }
    }


//     public function updateStatus(Request $request, $id)
// {
//     $task = Task::findOrFail($id);

   
//     $validatedData = $request->validate([
//         'status' => 'required|string|in:' . implode(',', array_keys(Task::STATUS_OPTIONS)),
//         'remarks' => 'sometimes|string|nullable', 
//         'is_admin_action' => 'sometimes|boolean', 
//     ]);

//     DB::beginTransaction();
//     try {
      
//         $task->status = $validatedData['status'];
//         $task->status_changed_at = now();

//         if ($task->status === Task::STATUS_COMPLETED) {
           
//             $task->completed_at = now();

          
//             if (!empty($validatedData['is_admin_action']) && $validatedData['is_admin_action']) {
//                 $task->approved_by_council_position_id = $request->user()->defaultCouncilPosition()->id;
//             }
//         } else {
           
//             $task->approved_by_council_position_id = null;
//         }

      
//         if (!empty($validatedData['remarks'])) {
//             $task->remarks = $validatedData['remarks'];
//         }

//         $task->save();
//         $task->loadTaskRelations();

//         $user = $request->user();

//         if ($user->isNotAdmin()) {
//             $councilId = $task->assignedCouncilPosition->council_id;

//             // Fetch all council positions with grant access (admins)
//             $adminUsers = CouncilPosition::where('council_id', $councilId)
//                 ->where('grant_access', true) // Only admins with grant access
//                 ->whereHas('user') // Ensure a user is associated with the position
//                 ->get()
//                 ->pluck('user');

//             foreach ($adminUsers as $admin) {
//                 $admin->notify(new TaskUpdate(
//                     $task->id,
//                     'Task Status Updated',
//                     "{$task->title} - Status: " . ucfirst($task->status)
//                 ));

//                 // Send FCM push notifications to admin users
//                 foreach ($admin->deviceTokens() as $token) {
//                     FCMController::sendPushNotification(
//                         $token,
//                         'Task Status Updated',
//                         "{$task->title} - Status: " . ucfirst($task->status) .
//                         ", Updated By: {$task->assignedCouncilPosition->user->fullName()}",
//                         [
//                             'council_position_id' => $admin->defaultCouncilPosition()->id ?? null,
//                             'user_id' => $admin->id,
//                             'notification' => 'task',
//                             'task_id' => $task->id,
//                             'status' => $task->status,
//                         ]
//                     );
//                 }
//             }
//         }

       



//         DB::commit();

//         return ApiResponse::success(new TaskResource($task), 'Task status updated successfully');
//     } catch (\Exception $e) {
//         DB::rollBack();
//         \Log::error('Failed to update task status', ['error' => $e->getMessage()]);
//         return ApiResponse::error('Failed to update task status: ' . $e->getMessage(), 500);
//     }
// }

public function updateStatus(Request $request, $id)
{
    $task = Task::findOrFail($id);

    $validatedData = $request->validate([
        'status' => 'required|string|in:' . implode(',', array_keys(Task::STATUS_OPTIONS)),
        'remarks' => 'sometimes|string|nullable',
        'is_admin_action' => 'sometimes|boolean',
    ]);

    DB::beginTransaction();
    try {
        $task->status = $validatedData['status'];
        $task->status_changed_at = now();

        if ($task->status === Task::STATUS_COMPLETED) {
            $task->completed_at = now();
            if (!empty($validatedData['is_admin_action']) && $validatedData['is_admin_action']) {
                $task->approved_by_council_position_id = $request->user()->defaultCouncilPosition()->id;
            }
        } else {
            $task->approved_by_council_position_id = null;
        }

        if (!empty($validatedData['remarks'])) {
            $task->remarks = $validatedData['remarks'];
        }

        $task->save();
        $task->loadTaskRelations();

        $user = $request->user();

        if ($user->isNotAdmin()) {
            $assignedCouncilPosition = $task->assignedCouncilPosition;

            if ($assignedCouncilPosition) {
                $councilId = $assignedCouncilPosition->council_id;

                if ($councilId) {
                    $adminUsers = CouncilPosition::where('council_id', $councilId)
                        ->where('grant_access', true)
                        ->whereHas('user')
                        ->with('user.devices')
                        ->get()
                        ->pluck('user');

                    foreach ($adminUsers as $admin) {
                        $admin->notify(new TaskUpdate(
                            $task->id,
                            'Task Status Updated',
                            "{$task->title} - Status: " . ucfirst($task->status)
                        ));

                        $tokens = $admin->devices()->pluck('device_token')->toArray();
                        if (!empty($tokens)) {
                            FCMController::sendPushNotification(
                                $tokens,
                                'Task Status Updated',
                                "{$task->title} - Status: " . ucfirst($task->status) .
                                ", Updated By: {$assignedCouncilPosition->user->fullName()}",
                                [
                                    'council_position_id' => $admin->defaultCouncilPosition()->id ?? null,
                                    'user_id' => $admin->id,
                                    'notification' => 'task',
                                    'task_id' => $task->id,
                                    'status' => $task->status,
                                ]
                            );
                        }
                    }
                }
            }
        }

        DB::commit();

        return ApiResponse::success(new TaskResource($task), 'Task status updated successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to update task status', [
            'task_id' => $task->id,
            'error' => $e->getMessage(),
            'user_id' => $request->user()->id,
        ]);
        return ApiResponse::error('Failed to update task status: ' . $e->getMessage(), 500);
    }
}

public function uploadFiles(Request $request, $id)
{

    $task = Task::findOrFail($id);


    $validatedData = $request->validate([
        'media.*' => ['nullable', 'file', 'max:50480'],
    ]);

    DB::beginTransaction();

    try {

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $task->addMedia($file)
                     ->preservingOriginal()
                     ->toMediaCollection('task_media');
            }
        }

        DB::commit();


        $task->refresh()->loadTaskRelations();


        return ApiResponse::success(new TaskResource($task), 'Files uploaded successfully', 201);
    } catch (\Throwable $e) { // Catch all exceptions, including errors
        DB::rollBack(); // Roll back transaction on failure
        \Log::error('Failed to upload files for task', [
            'error' => $e->getMessage(),
            'task_id' => $id,
        ]);

        return ApiResponse::error('Failed to upload files for task: ' . $e->getMessage(), 500);
    }
}

public function deleteMedia(Request $request, $taskId, $mediaId)
{

    $task = Task::findOrFail($taskId);


    $media = $task->getMedia('task_media')->where('id', $mediaId)->first();

    if (!$media) {
        return ApiResponse::error('Media not found for this task.', 404);
    }

    DB::beginTransaction();

    try {

        $media->delete();
        DB::commit();
        $task->refresh()->loadTaskRelations();

        return ApiResponse::success(new TaskResource($task), 'Media deleted successfully.');
    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('Failed to delete media for task', [
            'error' => $e->getMessage(),
            'task_id' => $taskId,
            'media_id' => $mediaId,
        ]);

        return ApiResponse::error('Failed to delete media: ' . $e->getMessage(), 500);
    }
}

public function fetchByCouncilPositionOrCouncil(Request $request, $councilPositionId)
{
    // Validate input
    $request->validate([
        'page' => 'integer|min:1',
        'per_page' => 'integer|min:1',
        'status' => 'nullable|string',
    ]);

    $page = $request->input('page', 1);
    $perPage = $request->input('per_page', 10);
    $status = $request->input('status');

    // Find the council position and handle error if it doesn't exist
    $councilPosition = CouncilPosition::findOrFail($councilPositionId);

    

    $councilId = $councilPosition->council_id;

    // Query tasks
    $tasksQuery = Task::where('council_position_id', $councilPosition->id)
        ->whereHas('assignedCouncilPosition', function ($query) use ($councilId) {
            $query->where('council_id', $councilId);
        });

    // Apply optional status filter
    if (!empty($status)) {
        $tasksQuery->where('status', $status);
    }

    // Paginate tasks
    $tasks = $tasksQuery->withTaskRelations()->latest()->paginate($perPage, ['*'], 'page', $page);

    // Return tasks
    return ApiResponse::paginated(
        $tasks,
        'Tasks retrieved successfully',
        TaskResource::class
    );
}

public function fetchTasksByCouncilPositionAndStatus(Request $request, $councilPositionId)
{
   
    $request->validate([
        'page' => 'integer|min:1',
        'per_page' => 'integer|min:1',
        'status' => 'nullable|string|in:' . implode(',', array_keys(Task::STATUS_OPTIONS)),
    ]);

    $page = $request->input('page', 1);
    $perPage = $request->input('per_page', 10);
    $status = $request->input('status');

    
    $tasksQuery = Task::where('council_position_id', $councilPositionId);

    
    if (!empty($status)) {
        $tasksQuery->where('status', $status);
    
    $tasks = $tasksQuery->withTaskRelations()->latest()->paginate($perPage, ['*'], 'page', $page);

    // Return API response
    return ApiResponse::paginated(
        $tasks,
        'Tasks retrieved successfully',
        TaskResource::class
    );
}

}





}
