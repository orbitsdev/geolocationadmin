<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\CouncilResource;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\CouncilPositionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'description' => $this->description,
            'content' => $this->content,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'start_time' => $this->start_time ? $this->start_time->toDateTimeString() : null,
            'end_time' => $this->end_time ? $this->end_time->toDateTimeString() : null,
            'is_active' => $this->is_active,
            'max_capacity' => $this->max_capacity,
            'type' => $this->type,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'council' => new CouncilResource($this->whenLoaded('council')),
            'council_position' => new CouncilPositionResource($this->whenLoaded('councilPosition')),
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
        ];
    }
}
