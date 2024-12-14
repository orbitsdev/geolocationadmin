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
    
        $user = $request->user();
        $defaultCouncilPosition = $user->defaultCouncilPosition();
    
        if (!$defaultCouncilPosition) {
            return ApiResponse::error('The user does not have a default council position.', 403);
        }
    
        $councilId = $defaultCouncilPosition->council_id;
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
    
        $media = Media::whereHasMorph(
            'model',
            [\App\Models\Task::class],
            function ($query) use ($councilId) {
                $query->whereIn('council_position_id', function ($subQuery) use ($councilId) {
                    $subQuery->select('id')
                             ->from('council_positions')
                             ->where('council_id', $councilId);
                });
            }
        )->paginate($perPage, ['*'], 'page', $page);
    
        return response()->json([
            'data' => MediaResource::collection($media->items()),
            'meta' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
            ],
            'message' => 'Media files retrieved successfully',
        ]);
    }



}
