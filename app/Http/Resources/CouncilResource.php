<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\CouncilPositionResource;
use Illuminate\Http\Resources\Json\JsonResource;


class CouncilResource extends JsonResource
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
            'name' => $this->name,
            'is_active' => $this->is_active,
            'council_positions' => CouncilPositionResource::collection($this->whenLoaded('councilPositions')),
            'created_at' => $this->created_at ? $this->created_at->format('F j, Y, g:i A') : null,
            'created_at' => $this->created_at ? $this->created_at->format('F j, Y, g:i A') : null,
        ];
    }
}
