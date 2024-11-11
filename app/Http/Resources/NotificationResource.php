<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $createdAt = $this->created_at ? Carbon::parse($this->created_at)->format('M d, Y h:i:s A') : null;
        $updatedAt = $this->updated_at ? Carbon::parse($this->updated_at)->format('M d, Y h:i:s A') : null;
        $readAt = $this->read_at ? Carbon::parse($this->read_at)->format('M d, Y h:i:s A') : null;

        $createdAtHumanReadable = $this->created_at ? Carbon::parse($this->created_at)->diffForHumans() : null;
        $updatedAt = $this->updated_at ? Carbon::parse($this->updated_at)->format('M d, Y h:i:s A') : null;

        return [
            'id' => $this->id,
            'type' => $this->type,    
            'data' => $this->data ? json_decode($this->data, true) : null,
            'read_at' => $readAt,
            'created_at' => $createdAtHumanReadable,
            'updated_at' => $updatedAt,
        ];
    }
}
