<?php

namespace App\Http\Controllers;

use App\Models\Council;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CouncilResource;

class CouncilController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $councils = Council::with('positions')->paginate(10);


        return ApiResponse::paginated($councils, 'Councils retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        // Start transaction
        DB::beginTransaction();

        try {
            $council = Council::create($validatedData);




            DB::commit();

            return ApiResponse::success(new CouncilResource($council), 'Council created successfully', 201);
        } catch (\Exception $e) {

            DB::rollBack();


            return ApiResponse::error('Failed to create council', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $council = Council::with('positions')->findOrFail($id);
        return ApiResponse::success(new CouncilResource($council), 'Council retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $council = Council::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        // Start transaction
        DB::beginTransaction();

        try {
            $council->update($validatedData);




            DB::commit();

            return ApiResponse::success(new CouncilResource($council), 'Council updated successfully');
        } catch (\Exception $e) {

            DB::rollBack();


            return ApiResponse::error('Failed to update council', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $council = Council::findOrFail($id);
        $council->delete();

        return ApiResponse::success(null, 'Council deleted successfully');
    }
}
