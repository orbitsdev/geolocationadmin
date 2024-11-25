<?php

namespace App\Http\Controllers;

use App\Models\Post;
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
        'council_position_id' => 'required|exists:council_positions,id',
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'description' => 'nullable|string',
        'media.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5048'
    ]);

    DB::beginTransaction();

    try {

        $postData = Arr::except($validatedData, ['files']);


        $post = Post::create($postData);


        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $post->addMedia($file)
                     ->preservingOriginal()
                     ->toMediaCollection('post_media');
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
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $post = Post::withPostRelations()->findOrFail($id);
        return ApiResponse::success(new PostResource($post), 'Post retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $post = Post::findOrFail($id);

    // Validate the incoming request
    $validatedData = $request->validate([
        'council_position_id' => 'sometimes|exists:council_positions,id',
        'title' => 'sometimes|string|max:255',
        'content' => 'sometimes|string',
        'description' => 'nullable|string',
        'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ]);

    DB::beginTransaction();

    try {
        // Update the post with the validated data
        $post->update($validatedData);

        // Check if any files are uploaded and handle the file upload process
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('uploads', 'public');
                $post->files()->create([
                    'file' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);


        DB::beginTransaction();

        try {

            foreach ($post->files as $file) {
                Storage::delete($file->file);  // Delete file from storage
                $file->delete();  // Remove from database
            }


            $post->delete();


            DB::commit();

            return ApiResponse::success(null, 'Post deleted successfully');
        } catch (\Exception $e) {

            DB::rollBack();
            return ApiResponse::error('Failed to delete post', 500);
        }
    }

    public function fetchByCouncilPositionOrCouncil(Request $request, $councilPositionId)
    {
        
        $councilPosition = CouncilPosition::findOrFail($councilPositionId);


        $councilId = $councilPosition->council_id;


        $page = $request->query('page', 1);
        $perPage = $request->query('perPage', 10);


        $posts = Post::where('council_position_id', $councilPosition->id)
            ->whereHas('councilPosition', function ($query) use ($councilId) {
                $query->where('council_id', $councilId);
            })
            ->withPostRelations()
            ->paginate($perPage, ['*'], 'page', $page);


        return ApiResponse::paginated($posts, 'Posts retrieved successfully', PostResource::class);
    }

}
