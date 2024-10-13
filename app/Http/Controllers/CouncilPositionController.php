<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Council;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CouncilPosition;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CouncilPositionResource;

class CouncilPositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $councilId)
    {
        $positions = CouncilPosition::where('council_id', $councilId)
        ->with(['user'])  // Only eager load users, not tasks
         ->withTaskCounts()
        ->paginate(10);
        return ApiResponse::paginated($positions, 'Council positions retrieved successfully', CouncilPositionResource::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'council_id' => 'required|exists:councils,id',
            'user_id' => 'required|exists:users,id',
            'position' => 'required|string|max:255',
        ]);

        // Check if the user already has a position in the same council
        $existingPosition = CouncilPosition::where('council_id', $validatedData['council_id'])
            ->where('user_id', $validatedData['user_id'])
            ->first();

        if ($existingPosition) {
            return ApiResponse::error('User already has a position in this council', 400);
        }

        DB::beginTransaction();

        try {
            // Check if the user already has any positions with 'is_login' set to true
            $hasLoginCouncilPosition = CouncilPosition::where('user_id', $validatedData['user_id'])
                ->where('is_login', true)
                ->get();

            // If the user has a previous position with 'is_login' set to true, set it to false
            if ($hasLoginCouncilPosition->count() > 0) {
                CouncilPosition::where('user_id', $validatedData['user_id'])
                    ->where('is_login', true)
                    ->update(['is_login' => false]);
            }

            // Create the new council position and set 'is_login' to true
            $validatedData['is_login'] = true;
            $position = CouncilPosition::create($validatedData);

            DB::commit();

            return ApiResponse::success(new CouncilPositionResource($position), 'Council position created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error('Failed to create council position', 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $position = CouncilPosition::with('user', 'council')
         ->withTaskCounts()
        ->findOrFail($id);
        return ApiResponse::success(new CouncilPositionResource($position), 'Council position retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $position = CouncilPosition::findOrFail($id);

        $validatedData = $request->validate([
            'position' => 'sometimes|string|max:255',
            'is_login' => 'sometimes|boolean', // Validate `is_login`
        ]);

        DB::beginTransaction();

        try {
            // If `is_login` is being updated to `true`, update other positions to `false`
            if (isset($validatedData['is_login']) && $validatedData['is_login'] === true) {
                // Set other positions with `is_login` to `false` for the same user
                CouncilPosition::where('user_id', $position->user_id)
                    ->where('is_login', true)
                    ->update(['is_login' => false]);
            }

            // Update the position with the validated data
            $position->update($validatedData);

            DB::commit();

            return ApiResponse::success(new CouncilPositionResource($position), 'Council position updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error('Failed to update council position', 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $position = CouncilPosition::findOrFail($id);

        // Begin transaction
        DB::beginTransaction();

        try {
            $position->delete();


            DB::commit();

            return ApiResponse::success(null, 'Council position deleted successfully');
        } catch (\Exception $e) {

            DB::rollBack();

            return ApiResponse::error('Failed to delete council position', 500);
        }
    }
    public function switchPosition(Request $request, $id)
    {
        $position = CouncilPosition::findOrFail($id);

        DB::beginTransaction();

        try {
            // Set 'is_login' to false for any existing positions with 'is_login' set to true for the same user
            CouncilPosition::where('user_id', $position->user_id)
                ->where('is_login', true)
                ->update(['is_login' => false]);

            // Set 'is_login' to true for the selected position
            $position->update(['is_login' => true]);

            DB::commit();

            return ApiResponse::success(new CouncilPositionResource($position), 'Council position switched successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error('Failed to switch council position', 500);
        }
    }

    public function availableUsers(Request $request, $councilId)
    {
        // Fetch users who do not already have a position in this council
        $users = User::whereDoesntHave('councilPositions', function($query) use ($councilId) {
            $query->where('council_id', $councilId);
        })
        ->select('id', 'first_name', 'last_name', 'email') // Select only the necessary fields
        ->get(); // Fetch all users without pagination
    
        return ApiResponse::success($users, 'Available users for council position');
    }
    

}
