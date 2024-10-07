<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PostCounsilPositionResource;

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
            'council_position' => new PostCounsilPositionResource($this->whenLoaded('councilPosition')),
            'chat_room_id' => $this->chat_room_id,
            'message' => $this->message,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('D g:i A') : null, // Format inline
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->format('D g:i A') : null,
        ];
    }
}
