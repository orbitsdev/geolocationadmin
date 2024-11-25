<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\FileResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'council_position_id' => $this->council_position_id,
            'title' => $this->title,
            'content' => $this->content,
            'description' => $this->description,
           
            'created_at' => $this->created_at ? $this->created_at->format('F j, Y, g:i A') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->diffForHumans() : null,
            'council' => new CouncilResource($this->whenLoaded('council')),
            'council_position' => new CouncilPositionResource($this->whenLoaded('councilPosition')),
            'media' => $this->getMedia('post_media')->map(function ($media) {
                $extension = $media->file_name ? pathinfo($media->file_name, PATHINFO_EXTENSION) : null;

                return [
                    'id' => $media->id, // Include the media ID
                    'file_name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'type' => $media->mime_type,
                    'extension' => $extension, 
                ];
            }),
            // 'file' => new FileResource($this->whenLoaded('file')),
            // 'files' => FileResource::collection($this->whenLoaded('files')),
           
        ];
    }
}
