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

    $councilId = $defaultCouncilPosition->council_id;

    $page = $request->input('page', 1);
    $perPage = $request->input('per_page', 10);

    // Query Media with loaded relationships
    $media = Media::whereHasMorph(
        'model',
        [\App\Models\Task::class],
        function ($query) use ($councilId) {
            $query->whereHas('councilPosition', function ($subQuery) use ($councilId) {
                $subQuery->where('council_id', $councilId);
            });
        }
    )
    ->with(['model' => function ($query) {
        $query->with('councilPosition'); // Load councilPosition relationship on Task
    }])
    ->paginate($perPage, ['*'], 'page', $page);

    return ApiResponse::paginated($media, 'Media files retrieved successfully', MediaResource::class);
}





}
