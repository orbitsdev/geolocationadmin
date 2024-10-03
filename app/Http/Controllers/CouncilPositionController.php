<?php

namespace App\Http\Controllers;

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
        $positions = CouncilPosition::where('council_id', $councilId)->with('user')->paginate(10);
        return ApiResponse::paginated($positions, 'Council positions retrieved successfully');
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


        DB::beginTransaction();

        try {
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
        $position = CouncilPosition::with('user', 'council')->findOrFail($id);
        return ApiResponse::success(new CouncilPositionResource($position), 'Council position retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $position = CouncilPosition::findOrFail($id);

        $validatedData = $request->validate([
            'council_id' => 'sometimes|exists:councils,id',
            'user_id' => 'sometimes|exists:users,id',
            'position' => 'sometimes|string|max:255',
        ]);


        DB::beginTransaction();

        try {
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
}
