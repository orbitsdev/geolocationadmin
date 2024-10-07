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
            'latitude' => number_format($this->latitude, 8, '.', ''),  // Preserve 8 decimal places as string
            'longitude' => number_format($this->longitude, 8, '.', ''),
            'radius' => $this->radius,
            'start_time' => $this->start_time ? $this->formattedDate($this->start_time) : null, // Custom format
            'end_time' => $this->end_time ? $this->formattedDate($this->end_time) : null,     // Custom format
            'is_active' => $this->is_active,
            'max_capacity' => $this->max_capacity,
            'type' => $this->type,

            'council' => new ($this->whenLoaded('council')),
            'council_position' => new PostCounsilPositionResource($this->whenLoaded('councilPosition')),
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
        ];
    }
    protected function formattedDate($date)
    {
        // Parse the date and format it for public display
        return Carbon::parse($date)->format('l, F j, Y, g:i A');
    }
}
