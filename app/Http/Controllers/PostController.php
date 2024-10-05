<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Arr;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Filament\Resources\PostResource;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $posts = Post::with('files', 'councilPosition')->paginate(10);
        return ApiResponse::paginated($posts, 'Posts retrieved successfully');
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
        'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
    ]);

    DB::beginTransaction();

    try {
        // Exclude 'files' from the validated data because it's handled separately
        $postData = Arr::except($validatedData, ['files']);
        
        // Create the post without the 'files' field
        $post = Post::create($postData);

        // Handle file uploads separately
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('uploads','public');
                $post->files()->create([
                    'file' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        DB::commit();

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

        $post = Post::with('files', 'councilPosition')->findOrFail($id);
        return ApiResponse::success(new PostResource($post), 'Post retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        $validatedData = $request->validate([
            'council_position_id' => 'sometimes|exists:council_positions,id',
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'description' => 'nullable|string',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);


        DB::beginTransaction();

        try {

            $post->update($validatedData);


            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('uploads');
                    $post->files()->create([
                        'file' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }


            DB::commit();

            return ApiResponse::success(new PostResource($post), 'Post updated successfully');
        } catch (\Exception $e) {

            DB::rollBack();
            return ApiResponse::error('Failed to update post', 500);
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
}
