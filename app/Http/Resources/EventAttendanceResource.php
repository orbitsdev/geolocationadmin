<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Support\Carbon;
class EventAttendanceResource extends JsonResource
{

    protected $attendance;

    public function __construct($resource, $attendance = null)
    {
        parent::__construct($resource);
        $this->attendance = $attendance;
    }
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

            // Include attendance details dynamically for the given council position
            'attendance' => $this->attendance ? [
                'check_in_time' => $this->attendance->check_in_time
                    ? $this->formattedDate($this->attendance->check_in_time)
                    : null,
                'check_out_time' => $this->attendance->check_out_time
                    ? $this->formattedDate($this->attendance->check_out_time)
                    : null,

                'check_in_coordinates' => $this->attendance->check_in_coordinates ? [
                    'latitude' => $this->attendance->check_in_latitude,
                    'longitude' => $this->attendance->check_in_longitude,
                ] : null,
                'check_out_coordinates' => $this->attendance->check_out_coordinates ? [
                    'latitude' => $this->attendance->check_out_latitude,
                    'longitude' => $this->attendance->check_out_longitude,
                ] : null,

                'check_in_selfie_url' => $this->attendance->getFirstMediaUrl('check_in_selfies') ?? null,
                'check_out_selfie_url' => $this->attendance->getFirstMediaUrl('check_out_selfies') ?? null,
            ] : null,
        ];
    }
    protected function formattedDate($date)
    {
        return Carbon::parse($date)->setTimezone(config('app.timezone'))->format('l, F j, Y, g:i A');
    }
}
