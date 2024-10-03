<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\MessageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomResource extends JsonResource
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
            'council_id' => $this->council_id,
            'name' => $this->name,
            'messages' => MessageResource::collection($this->whenLoaded('messages')), 
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
