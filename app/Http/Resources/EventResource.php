<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'latitude' => $this->latitude,   // Keep as raw value
            'longitude' => $this->longitude, // Keep as raw value
            'radius' => $this->radius,       // Keep as raw value
            'start_time' => $this->start_time ? $this->formattedDate($this->start_time) : null,
            'end_time' => $this->end_time ? $this->formattedDate($this->end_time) : null,
            'is_active' => $this->is_active,
            'restrict_event' => $this->restrict_event,
            'max_capacity' => $this->max_capacity,
            'type' => $this->type,
            'specified_location' => $this->specified_location,
            'map_location' => $this->map_location,
            'place_id' => $this->place_id,
            'council' => new CouncilResource($this->whenLoaded('council')),
            'council_position' => new PostCounsilPositionResource($this->whenLoaded('councilPosition')),
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
        ];
    }
    protected function formattedDate($date)
    {
        return Carbon::parse($date)->setTimezone(config('app.timezone'))->format('l, F j, Y, g:i A');
    }
}
