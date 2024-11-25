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
        $totalAmount = $this->collectionItems 
            ? $this->collectionItems->sum(fn($item) => (double)$item->amount) 
            : 0.00;




        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'description' => $this->description,
            'council' => new CouncilResource($this->whenLoaded('council')),
            'council_position' => new CouncilPositionResource($this->whenLoaded('councilPosition')),
            // 'council_position' => new PostCounsilPositionResource($this->whenLoaded('councilPosition')),
            'items' => CollectionItemResource::collection($this->whenLoaded('collectionItems')),
            'total_amount' => $totalAmount,
            'item_count' => (int) $this->collection_items_count,
            'is_publish' => $this->is_publish, // Include the is_publish attribute
            'last_updated' => $this->updated_at ? $this->updated_at->diffForHumans() : null,

           
        ];
    }
}
