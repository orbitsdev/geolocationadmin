<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CouncilPosition;
use App\Http\Resources\MediaResource;
use Google\Rpc\Context\AttributeContext\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    

    public function fetchMediaByCouncil(Request $request)
{
    $request->validate([
        'page' => 'integer|min:1',
        'per_page' => 'integer|min:1',
    ]);

    // Get the authenticated user
    $user = $request->user();
    $defaultCouncilPosition = $user->defaultCouncilPosition();

    if (!$defaultCouncilPosition) {
        return ApiResponse::error('The user does not have a default council position.', 403);
    }

    // Get the council_id of the default council position
    $councilId = $defaultCouncilPosition->council_id;

    // Pagination inputs
    $page = $request->input('page', 1);
    $perPage = $request->input('per_page', 10);

    // Adjust query: Use proper joins to ensure relationships are loaded correctly
    $media = Media::query()
        ->whereHasMorph(
            'model',
            [\App\Models\Task::class],
            function ($query) use ($councilId) {
                // Filter tasks by council_position_id belonging to the council_id
                $query->whereHas('councilPosition', function ($q) use ($councilId) {
                    $q->where('council_id', $councilId);
                });
            }
        )
        ->with(['model.councilPosition']) // Ensure relationships are loaded
        ->paginate($perPage, ['*'], 'page', $page);

    // Use the ApiResponse helper for consistent response
    return ApiResponse::paginated($media, 'Media files retrieved successfully', MediaResource::class);
}




}
