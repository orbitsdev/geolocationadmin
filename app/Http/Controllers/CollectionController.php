<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\CollectionResource;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $collections = Collection::with('collectionItems', 'councilPosition')->paginate(10);
        return ApiResponse::paginated($collections, 'Collections retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'council_id' => 'required|exists:councils,id',
            'council_position_id' => 'required|exists:council_positions,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Collection::CHART_OPTIONS)),
            'description' => 'nullable|string',
            'items' => 'sometimes|array',
            'items.*.label' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0',
        ]);


        DB::beginTransaction();

        try {

            $collection = Collection::create($validatedData);


            if (isset($validatedData['items'])) {
                foreach ($validatedData['items'] as $item) {
                    $collection->addItem($item);
                }
            }


            DB::commit();

            return ApiResponse::success(new CollectionResource($collection->getWithRelations()), 'Collection created successfully', 201);
        } catch (\Exception $e) {

            DB::rollBack();
            return ApiResponse::error('Failed to create collection', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $collection = Collection::with('collectionItems', 'councilPosition')->findOrFail($id);
        return ApiResponse::success(new CollectionResource($collection), 'Collection retrieved successfully');

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $collection = Collection::findOrFail($id);

        $validatedData = $request->validate([
            'council_id' => 'sometimes|exists:councils,id',
            'council_position_id' => 'sometimes|exists:council_positions,id',
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:' . implode(',', array_keys(Collection::CHART_OPTIONS)),
            'description' => 'nullable|string',
            'items' => 'sometimes|array',
            'items.*.label' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0',
        ]);


        DB::beginTransaction();

        try {

            $collection->update($validatedData);


            if (isset($validatedData['items'])) {
                foreach ($validatedData['items'] as $item) {
                    $collection->addItem($item);
                }
            }


            DB::commit();

            return ApiResponse::success(new CollectionResource($collection->getWithRelations()), 'Collection updated successfully');
        } catch (\Exception $e) {

            DB::rollBack();
            return ApiResponse::error('Failed to update collection', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $collection = Collection::findOrFail($id);


        DB::beginTransaction();

        try {
            $collection->delete();


            DB::commit();

            return ApiResponse::success(null, 'Collection deleted successfully');
        } catch (\Exception $e) {

            DB::rollBack();
            return ApiResponse::error('Failed to delete collection', 500);
        }
    }

    public function removeItem($collectionId, $itemId)
    {
        $collection = Collection::findOrFail($collectionId);


        DB::beginTransaction();

        try {
            $collection->removeItem($itemId);


            DB::commit();

            return ApiResponse::success(null, 'Collection item removed successfully');
        } catch (\Exception $e) {
           
            DB::rollBack();
            return ApiResponse::error('Failed to remove collection item', 500);
        }
    }
}
