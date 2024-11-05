<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Council;
use App\Helpers\ApiResponse;
use App\Http\Resources\AvailableUserResource;
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
        ->with(['user'])
        ->latest()  // Only eager load users, not tasks
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
            'grant_access' => 'sometimes|boolean',
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

            return ApiResponse::error('Failed to create council position '.$e->getMessage(), 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $council, string $id)
    {
        $position = CouncilPosition::withRelation()
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
            'is_login' => 'sometimes|boolean',
            'grant_access' => 'sometimes|boolean', // Validate grant_access field
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
    public function destroy($councilId, $id)
    {
        // Check if the council and the position both exist
        $position = CouncilPosition::where('council_id', $councilId)->findOrFail($id);

        // Begin transaction
        DB::beginTransaction();

        try {
            // Delete the council position
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
        // Get optional search and pagination parameters
        $search = $request->input('search');
        $perPage = $request->input('perPage', 20); // Default to 20 items per batch

        // Fetch users who do not already have a position in this council
        $usersQuery = User::whereDoesntHave('councilPositions', function($query) use ($councilId) {
            $query->where('council_id', $councilId);
        });

        // Apply search if provided
        if ($search) {
            $usersQuery->where(function($query) use ($search) {
                $query->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Apply the limit without skipping (no skip here)
        $users = $usersQuery
            ->take($perPage)
            ->get();

        return ApiResponse::success(AvailableUserResource::collection($users), 'Available users for council position');
    }


    public function searchOfficer(Request $request, $councilId)
    {
        // Get optional search and pagination parameters
        $search = $request->input('search');
        $councilId = $request->input('councilId');
        $perPage = $request->input('perPage', 20); // Default to 20 items per batch

        // Fetch users who do not already have a position in this council
        // $officersQuery = CouncilPosition::where('council_id', $councilId)->withRelation();

        // // Apply search if provided
        // if ($search) {
        //     $officersQuery->where('position', 'Like',  "%{$search}%")->orWhereHas('user',function($query) use ($search) {
        //         $query->where('first_name', 'LIKE', "%{$search}%")
        //             ->orWhere('last_name', 'LIKE', "%{$search}%")
        //             ->orWhere('email', 'LIKE', "%{$search}%");
        //     });
        // }

        // // Apply the limit without skipping (no skip here)
        // $officers = $officersQuery
        //     ->take($perPage)
        //     ->get();

        return ApiResponse::success([ $search, $councilId], 'Available officers for this academic year');
        // return ApiResponse::success(CouncilPositionResource::collection($officers), 'Available officers for this academic year');
    }




}
