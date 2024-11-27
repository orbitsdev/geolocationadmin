<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Arr;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CouncilPosition;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $page = $request->query('page', 1);
        $perPage = $request->query('perPage', 10);

        // Fetch the posts with pagination and load relationships
        $posts = Post::withPostRelations()->paginate($perPage, ['*'], 'page', $page);

        // Return the paginated API response
        return ApiResponse::paginated($posts, 'Posts retrieved successfully', PostResource::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validatedData = $request->validate([

        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'description' => 'nullable|string',
      'is_publish' => 'required|boolean',

        'media.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:50480', // Allowing images and videos
    ]);
    $user = $request->user();
    $councilPosition = $user->defaultCouncilPosition();

    if (!$councilPosition) {
        return ApiResponse::error('No default council position found for the user.', 403);
    }

    DB::beginTransaction();

    try {

        $postData = Arr::except($validatedData, ['media']);
        $postData['council_id'] = $councilPosition->council_id;
         $postData['council_position_id'] = $councilPosition->id;
        $post = Post::create($postData);


       // Handle media upload
       if ($request->hasFile('media')) {
        foreach ($request->file('media') as $file) {
            $post->addMedia($file)->preservingOriginal()->toMediaCollection('post_media');
        }
    }


    if ($validatedData['is_publish']) {
        $users = User::whereHas('councilPositions', function ($query) use ($councilPosition) {
            $query->where('council_id', $councilPosition->council_id);
        })->get();

        foreach ($users as $user) {
            foreach ($user->devices as $device) {
                FCMController::sendPushNotification(
                    $device->device_token,
                    'New Post Published',
                    "{$post->title}",
                    [
                        'notification_type' => 'post',
                        'post_id' => $post->id,
                    ]
                );
            }
        }
    }

        DB::commit();
        $post->loadPostRelations();

        return ApiResponse::success(new PostResource($post), 'Post created successfully', 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse::error('Failed to create post ' . $e->getMessage(), 500);
    }
}


 /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{

    return ApiResponse::success($request->all());
    $post = Post::findOrFail($id);

    // Validate the incoming request
    $validatedData = $request->validate([

        'title' => 'sometimes|string|max:255',
        'content' => 'sometimes|string',
        'description' => 'nullable|string',
        'is_publish' => 'required|in:true,false,1,0',

        'media.*' => ['nullable', 'file', 'mimes:jpeg,png,mp4', 'max:50480'],
    ]);


    $user = $request->user();
    $councilPosition = $user->defaultCouncilPosition();

    if (!$councilPosition) {
        return ApiResponse::error('No default council position found for the user.', 403);
    }


    DB::beginTransaction();

    try {
        $postData = Arr::except($validatedData, ['media']);
        $postData = array_merge($validatedData, [
            'council_position_id' => $councilPosition->id,
            'council_id' => $councilPosition->council_id,
        ]);
        $post->update($postData);

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $mediaItem = $post->addMedia($file)->preservingOriginal()->toMediaCollection('post_media');
            }
        }

        // Send notifications if the post is published
        if ($validatedData['is_publish']) {
            $users = User::whereHas('councilPositions', function ($query) use ($councilPosition) {
                $query->where('council_id', $councilPosition->council_id);
            })->get();

            foreach ($users as $user) {
                foreach ($user->devices as $device) {
                    FCMController::sendPushNotification(
                        $device->device_token,
                        'Post Updated',
                        "{$post->title}",
                        [
                            'notification_type' => 'post_update',
                            'post_id' => $post->id,
                        ]
                    );
                }
            }
        }

        // Load the relationships, including files (if they exist)
        $post->loadPostRelations();

        DB::commit();

        return ApiResponse::success(new PostResource($post), 'Post updated successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        return ApiResponse::error('Failed to update post: ' . $e->getMessage(), 500);
    }
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $post = Post::withPostRelations()->findOrFail($id);
        return ApiResponse::success(new PostResource($post), 'Post retrieved successfully');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);


        DB::beginTransaction();

        try {




            $post->delete();


            DB::commit();

            return ApiResponse::success(null, 'Post deleted successfully');
        } catch (\Exception $e) {

            DB::rollBack();
            return ApiResponse::error('Failed to delete post', 500);
        }
    }

    public function fetchByCouncil(Request $request, $councilId)
{
    // Pagination parameters
    $page = $request->query('page', 1);
    $perPage = $request->query('perPage', 10);


    $posts = Post::where('council_id', $councilId)
        ->withPostRelations() // Load relationships (e.g., council, councilPosition, media)
        ->paginate($perPage, ['*'], 'page', $page);

    // Return paginated response
    return ApiResponse::paginated($posts, 'Posts retrieved successfully', PostResource::class);
}


public function deleteMedia(Request $request, $postId, $mediaId)
{

    $post = Post::findOrFail($postId);


    $media = $post->getMedia('post_media')->where('id', $mediaId)->first();

    if (!$media) {
        return ApiResponse::error('Media not found for this task.', 404);
    }

    DB::beginTransaction();

    try {

        $media->delete();
        DB::commit();
        $post->refresh()->loadPostRelations();

        return ApiResponse::success(new PostResource($post), 'Media deleted successfully.');
    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('Failed to delete media for task', [
            'error' => $e->getMessage(),
            'post_id' => $postId,
            'media_id' => $mediaId,
        ]);

        return ApiResponse::error('Failed to delete media: ' . $e->getMessage(), 500);
    }

  }
}
