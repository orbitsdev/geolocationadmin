<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CouncilPosition;
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
        $councilId = $request->query('councilId', 10);

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
            'media.*' => ['nullable', 'file', 'mimes:jpeg,png,mp4', 'max:50480'],
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

            // $officer = CouncilPosition::findOrFail($validatedData['council_position_id']);
            // if($officer){
            //     foreach($officer->user->tokens as $token){
            //         FCMController::sendPushNotification($token, 'Task was assign', $task->title,  [
            //             'council_position_id' =>  $officer->id,
            //             'user_id'=> $officer->user->id,
            //             'notification'=> 'task',
            //         ]);
            // }
            
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
            DB::commit();

            return ApiResponse::success(new TaskResource($task), 'Task updated successfully');
        } catch (\Exception $e) {

            DB::rollBack();
            \Log::error('Failed to update task', ['error' => $e->getMessage()]);
            return ApiResponse::error('Failed to update task', 500);
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


    public function updateStatus(Request $request, $id)
{
    $task = Task::findOrFail($id);

    // Validate the request data
    $validatedData = $request->validate([
        'status' => 'required|string|in:' . implode(',', array_keys(Task::STATUS_OPTIONS)),
        'remarks' => 'sometimes|string|nullable', // Optional remarks for the status change
        'is_admin_action' => 'sometimes|boolean', // Optional boolean field to indicate an approval action
    ]);

    DB::beginTransaction();
    try {
        // Update the status and set the status_changed_at timestamp
        $task->status = $validatedData['status'];
        $task->status_changed_at = now();

        // Always set the completed_at if the status is 'Completed'
        if ($task->status === Task::STATUS_COMPLETED || $task->status === Task::STATUS_COMPLETED_LATE) {
            // Always set the completed_at if the status is Completed or Completed Late
            $task->completed_at = now();

            // Only set the approved_by_council_position_id if the action is flagged as an admin-like approval action
            if (!empty($validatedData['is_admin_action']) && $validatedData['is_admin_action']) {
                $task->approved_by_council_position_id = $request->user()->defaultCouncilPosition()->id;
            }
        } else {
            // For other statuses, reset only the approval-related fields, but leave completed_at intact
            $task->approved_by_council_position_id = null;
        }


        // If remarks are provided, update the remarks column
        if (isset($validatedData['remarks'])) {
            $task->remarks = $validatedData['remarks'];
        }

        // Save the task
        $task->save();
        $task->loadTaskRelations();

        DB::commit();

        return ApiResponse::success(new TaskResource($task), 'Task status updated successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to update task status', ['error' => $e->getMessage()]);
        return ApiResponse::error('Failed to update task status: ' . $e->getMessage(), 500);
    }
}

public function uploadFiles(Request $request, $id)
{
    $task = Task::findOrFail($id);

    // Validate the incoming request to ensure files are uploaded
    $validatedData = $request->validate([
        'files.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ]);

    DB::beginTransaction();

    try {
        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('task_files','public');
                $task->files()->create([
                    'file' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        DB::commit();

        return ApiResponse::success(null, 'Files uploaded successfully', 201);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to upload files for task', ['error' => $e->getMessage()]);
        return ApiResponse::error('Failed to upload files for task: ' . $e->getMessage(), 500);
    }
}
public function fetchByCouncilPositionOrCouncil(Request $request, $councilPositionId)
{
    // Fetch the CouncilPosition to get the related council_id
    $councilPosition = CouncilPosition::findOrFail($councilPositionId);

    // Now you have the council_id from the councilPosition object
    $councilId = $councilPosition->council_id;

    // Get the page and perPage from the query string, default to page 1 and 10 per page
    $page = $request->query('page', 1);
    $perPage = $request->query('perPage', 10);

    // Query tasks based on the council_position_id or council_id
    $tasks = Task::where('council_position_id', $councilPosition->id)
        ->whereHas('assignedCouncilPosition', function ($query) use ($councilId) {
            $query->where('council_id', $councilId);
        })
        ->withTaskRelations()
        ->paginate($perPage, ['*'], 'page', $page); // Apply pagination

    // Return the paginated tasks using the TaskResource
    return ApiResponse::paginated($tasks, 'Tasks retrieved successfully', TaskResource::class);
}




}
