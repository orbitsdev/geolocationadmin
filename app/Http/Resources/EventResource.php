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
            'title' => $this->title ?? null,
            'description' => $this->description ?? null,
            'content' => $this->content ?? null,
            'latitude' => $this->latitude ?? null,   
            'longitude' => $this->longitude ?? null,
            'radius' => $this->radius ?? null,     
            'start_time' => $this->start_time ? $this->formattedDate($this->start_time) : null,
            'end_time' => $this->end_time ? $this->formattedDate($this->end_time) : null,
            'is_active' => $this->is_active ?? null,
            'restrict_event' => $this->restrict_event ?? null,
            'max_capacity' => $this->max_capacity ?? null,
            'type' => $this->type ?? null,
            'specified_location' => $this->specified_location ?? null,
            'map_location' => $this->map_location ?? null,
            'place_id' => $this->place_id ?? null,
            'council' => new CouncilResource($this->whenLoaded('council')),
            'council_position' => new CouncilPositionResource($this->whenLoaded('councilPosition')),
            'total_attendance' => $this->attendances_count ?? 0,
            // 'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
        ];
    }
    protected function formattedDate($date)
    {
        return Carbon::parse($date)->setTimezone(config('app.timezone'))->format('l, F j, Y, g:i A');
    }
}
