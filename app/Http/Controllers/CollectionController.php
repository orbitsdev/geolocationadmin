<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CouncilPosition;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CollectionResource;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    // Get the page and perPage from the query string, default to page 1 and 10 per page
    $page = $request->query('page', 1);
    $perPage = $request->query('perPage', 10);

    // Fetch collections with relationships and apply pagination
    $collections = Collection::with('collectionItems', 'councilPosition')
        ->paginate($perPage, ['*'], 'page', $page);

    // Return the paginated API response
    return ApiResponse::paginated($collections, 'Collections retrieved successfully', CollectionResource::class);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $validatedData = $request->validate([
            'council_position_id' => 'required|exists:council_positions,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Collection::CHART_OPTIONS)),
            'description' => 'nullable|string',
            'items' => 'sometimes|array',  // Validate but don't include in $validatedData for Collection creation
            'items.*.label' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Remove 'items' from $validatedData since it doesn't belong in the collections table
            $collectionData = $validatedData;
            unset($collectionData['items']);

            // Create the collection without 'items'
            $collection = Collection::create($collectionData);

            // Handle the collection items if they are provided
            if (isset($validatedData['items'])) {
                foreach ($validatedData['items'] as $item) {
                    $collection->collectionItems()->create($item);  // Assuming 'collectionItems' is the relationship
                }
            }

            DB::commit();

            return ApiResponse::success(new CollectionResource($collection->load('collectionItems')), 'Collection created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create collection', ['error' => $e->getMessage()]);
            return ApiResponse::error($e->getMessage(), 500);
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
        // Remove 'items' from $validatedData since it doesn't belong in the collections table
        $collectionData = $validatedData;
        unset($collectionData['items']);

        // Update the collection without 'items'
        $collection->update($collectionData);

        // Handle the items, either by adding new or updating existing items
        if (isset($validatedData['items'])) {
            foreach ($validatedData['items'] as $item) {
                // You can either update existing items or add new items.
                // Assuming addItem() handles both adding and updating items:
                $collection->addItem($item);
            }
        }

        DB::commit();

        return ApiResponse::success(new CollectionResource($collection->load('collectionItems')), 'Collection updated successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to update collection', ['error' => $e->getMessage()]);
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

    public function fetchByCouncilPositionOrCouncil(Request $request, $councilPositionId)
{

    $councilPosition = CouncilPosition::findOrFail($councilPositionId);

    $councilId = $councilPosition->council_id;
    


    $page = $request->query('page', 1);
    $perPage = $request->query('perPage', 10);

    // Query collections based on the council_position_id or council_id
    $collections = Collection::where('council_position_id', $councilPosition->id)
        ->whereHas('councilPosition', function ($query) use ($councilId) {
            $query->where('council_id', $councilId);
        })
        ->with('collectionItems', 'councilPosition')
        ->paginate($perPage, ['*'], 'page', $page); // Apply pagination

    // Return the paginated collections using the appropriate resource
    return ApiResponse::paginated($collections, 'Collections retrieved successfully', CollectionResource::class);
}

}
