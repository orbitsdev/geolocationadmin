<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tasks = Task::with(['assignedCouncilPosition', 'approvedByCouncilPosition', 'file', 'files'])->paginate(10);

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
            'is_done' => 'sometimes|boolean',  // Validate as boolean
        ]);

        // Set default values if not provided
        $validatedData['status'] = $validatedData['status'] ?? 'To Do';

        DB::beginTransaction();

        try {
            // Create the task with the validated data
            $task = Task::create($validatedData);

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
        $task = Task::with(['assignedCouncilPosition', 'approvedByCouncilPosition'])->findOrFail($id);
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
            'status' => 'sometimes|string|in:To Do,In Progress,Completed',
            'is_lock' => 'sometimes|boolean',
            'is_done' => 'sometimes|boolean',
        ]);


        DB::beginTransaction();

        try {
            $task->update($validatedData);

            $task->load(['assignedCouncilPosition', 'approvedByCouncilPosition', 'file', 'files']);
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

    $validatedData = $request->validate([
        'status' => 'required|string|in:' . implode(',', array_keys(Task::STATUS_OPTIONS)),
        'remarks' => 'sometimes|string|nullable',  // Optional remarks for the status change
    ]);

    DB::beginTransaction();

    try {
        // Update the status and set the status_changed_at timestamp
        $task->status = $validatedData['status'];
        $task->status_changed_at = now();

        // If remarks are provided, update the remarks column
        if (isset($validatedData['remarks'])) {
            $task->remarks = $validatedData['remarks'];
        }

        // Save the task
        $task->save();
        $task->load(['assignedCouncilPosition', 'approvedByCouncilPosition', 'file', 'files']);
        DB::commit();

        return ApiResponse::success(new TaskResource($task), 'Task status updated successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to update task status', ['error' => $e->getMessage()]);
        return ApiResponse::error('Failed to update task status', 500);
    }
}

}