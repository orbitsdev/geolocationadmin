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
        $totalAmount = $this->collectionItems->sum('amount');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'description' => $this->description,
            'council_position' => new PostCounsilPositionResource($this->whenLoaded('councilPosition')),
            'items' => CollectionItemResource::collection($this->whenLoaded('collectionItems')),
            'total_amount' => $totalAmount,
            'item_count' => $this->collectionItems->count(),
            'last_updated' => $this->updated_at->toDateTimeString(),
           
        ];
    }
}
