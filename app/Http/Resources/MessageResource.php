<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\CouncilPositionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'council_position' => new CouncilPositionResource($this->whenLoaded('councilPosition')),
            'chat_room_id' => $this->chat_room_id,
            'message' => $this->message,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
