<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\CollectionItemResource;
use App\Http\Resources\CouncilPositionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
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
            'title' => $this->title,
            'type' => $this->type,
            'description' => $this->description,
            'council_position' => new CouncilPositionResource($this->whenLoaded('councilPosition')),
            'items' => CollectionItemResource::collection($this->whenLoaded('collectionItems')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
