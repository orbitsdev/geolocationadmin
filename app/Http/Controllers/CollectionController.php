<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
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
    
    $page = $request->query('page', 1);
    $perPage = $request->query('perPage', 10);

  
    $collections = Collection::withRelation()->paginate($perPage, ['*'], 'page', $page);

    
    return ApiResponse::paginated($collections, 'Collections retrieved successfully', CollectionResource::class);
}


public function store(Request $request)
{
    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'type' => 'required|in:' . implode(',', array_keys(Collection::CHART_OPTIONS)),
        'description' => 'nullable|string',
        'is_publish' => 'required|boolean', 
        'items' => 'sometimes|array',
        'items.*.label' => 'required|string|max:255',
        'items.*.amount' => 'required|numeric',
    ]);

  
    $user = $request->user();
    $councilPosition = $user->defaultCouncilPosition();
    if (!$councilPosition) {
        return ApiResponse::error('No default council position found for the user.', 403);
    }

    DB::beginTransaction();

    try {
        $collectionData = array_merge(
            $validatedData,
            [
                'council_position_id' => $councilPosition->id,
                'council_id' => $councilPosition->council_id,
                ]
        );
        unset($collectionData['items']);

        $collection = Collection::create($collectionData);
        $collection->collectionItems()->createMany($validatedData['items'] ?? []);

        if ($validatedData['is_publish']) {
           $post = Post::create([
                'council_position_id' => $councilPosition->id,
                'title' => $collection->title,
                'content' => $collection->description,
                'description' => 'Published collection: ' . $collection->title,
                'postable_type' => Collection::class,
                'postable_id' => $collection->id,
            ]);


            $councilUsers = User::whereHas('councilPositions', function ($query) use ($councilPosition) {
                $query->where('council_id', $councilPosition->council_id);
            })->get();

            foreach ($councilUsers as $recipient) {
                foreach ($recipient->deviceTokens as $token) {
                    FCMController::sendPushNotification(
                        $token,
                        'New Collection Published',
                        "Collection: {$collection->title} has been published!",
                        [
                            'notification_type' => 'collection',
                            'collection_id' => $collection->id,
                            'post_id' => $post->id,
                            'council_id' => $councilPosition->council_id,
                        ]
                    );
                }
            }
        }

        DB::commit();

        return ApiResponse::success(
            new CollectionResource($collection->load('collectionItems')),
            'Collection created successfully',
            201
        );
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to create collection', [
            'error' => $e->getMessage(),
            'request' => $request->all(),
            'user_id' => $request->user()->id,
        ]);
        return ApiResponse::error('Failed to create collection', 500);
    }
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $collection = Collection::withRelation()->findOrFail($id);
        return ApiResponse::success(new CollectionResource($collection), 'Collection retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Retrieve the collection or fail
        $collection = Collection::findOrFail($id);
    
        // Validate the incoming data
        $validatedData = $request->validate([
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:' . implode(',', array_keys(Collection::CHART_OPTIONS)),
            'description' => 'nullable|string',
            'items' => 'sometimes|array',
            'items.*.id' => 'nullable|exists:collection_items,id', // Allow nullable IDs for new items
            'items.*.label' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric',
        ]);
    
        $user = $request->user();
        $councilPosition = $user->defaultCouncilPosition();
        if (!$councilPosition) {
            return ApiResponse::error('No default council position found for the user.', 403);
        }
    
        DB::beginTransaction();
    
        try {
            $collectionData = $validatedData;
            unset($collectionData['items']);
    
            // Update collection
            $collection->update($collectionData);
    
            $newItems = collect($validatedData['items'] ?? []);
    
            // Split the items into two groups
            $existingItems = $newItems->filter(fn($item) => isset($item['id']));
            $newItems = $newItems->filter(fn($item) => !isset($item['id']));
            
            // Update existing items
            foreach ($existingItems as $itemData) {
                $collection->collectionItems()
                    ->where('id', $itemData['id'])
                    ->first()
                    ->update($itemData);
            }
    
            // Delete items no longer in the request
            $collection->collectionItems()
                ->whereNotIn('id', $existingItems->pluck('id'))
                ->delete();
    
            // Create new items
            $collection->collectionItems()->createMany($newItems);

            if ($validatedData['is_publish'] ?? false) {
                $postData = [
                    'title' => $collection->title,
                    'content' => $collection->description,
                    'council_id' => $collection->council_id,
                    'council_position_id' => $collection->council_position_id,
                ];
    
                // Update or create the post
                $collection->post()->updateOrCreate(['postable_id' => $collection->id, 'postable_type' => Collection::class], $postData);

                $users = User::whereHas('councilPositions', function ($query) use ($collection) {
                    $query->where('council_id', $collection->council_id);
                })->get();
    
                foreach ($users as $user) {
                    foreach ($user->deviceTokens() as $token) {
                        FCMController::sendPushNotification(
                            $token,
                            'Updated Collection Published',
                            "{$collection->title} has been updated and published.",
                            [
                                'council_id' => $collection->council_id,
                                'council_position_id' => $collection->council_position_id,
                                'collection_id' => $collection->id,
                                'notification' => 'collection_update',
                            ]
                        );
                    }
                }
            } else {
                // If `is_publish` is false, delete the associated post
                $collection->post()->delete();
            }
    
            DB::commit();
    
            return ApiResponse::success(
                new CollectionResource($collection->load('collectionItems')),
                'Collection updated successfully'
            );
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
            return ApiResponse::success(null, 'Collection and its items deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
    
            \Log::error('Failed to delete collection', ['error' => $e->getMessage()]);
            return ApiResponse::error('Failed to delete collection', 500);
        }
    }
    

    public function removeItem($collectionId, $itemId)
{
    DB::beginTransaction();

    try {
        // Ensure the collection exists
        $collection = Collection::findOrFail($collectionId);

        // Check if the item exists and belongs to the collection
        $item = $collection->collectionItems()->where('id', $itemId)->first();

        if (!$item) {
            return ApiResponse::error('Collection item not found or does not belong to this collection.', 404);
        }

        // Delete the item
        $item->delete();

        DB::commit();

        return ApiResponse::success(null, 'Collection item removed successfully');
    } catch (\Exception $e) {
        DB::rollBack();

        \Log::error('Failed to remove collection item', [
            'error' => $e->getMessage(),
            'collection_id' => $collectionId,
            'item_id' => $itemId,
        ]);

        return ApiResponse::error('Failed to remove collection item', 500);
    }
}



    public function fetchByCouncil(Request $request)
    {
        // Retrieve the user's default council position
        $user = $request->user();
        $councilPosition = $user->defaultCouncilPosition();
    
        if (!$councilPosition) {
            return ApiResponse::error('No default council position found for the user.', 403);
        }
    
        $page = $request->query('page', 1);
        $perPage = $request->query('perPage', 10);
    
        // Fetch collections by council ID with relationships
        $collections = Collection::where('council_id', $councilPosition->council_id)
            ->withRelation()
            ->paginate($perPage, ['*'], 'page', $page);
            // return ApiResponse::success([$request->all(), ]);
    
        return ApiResponse::paginated($collections, 'Collections retrieved successfully', CollectionResource::class);
    }
    

}
