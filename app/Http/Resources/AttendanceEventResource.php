<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceEventResource extends JsonResource
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
            'event_id' => $this->event_id,
            'council_position_id' => $this->council_position_id,
            'check_in_time' => $this->check_in_time
                ? $this->formattedDate($this->check_in_time)
                : null,
            'check_out_time' => $this->check_out_time
                ? $this->formattedDate($this->check_out_time)
                : null,
            'check_in_coordinates' => $this->check_in_coordinates ? [
                'latitude' => $this->check_in_latitude,
                'longitude' => $this->check_in_longitude,
            ] : null,
            'check_out_coordinates' => $this->check_out_coordinates ? [
                'latitude' => $this->check_out_latitude,
                'longitude' => $this->check_out_longitude,
            ] : null,
          'check_in_selfie_url' => $this->getFirstMediaUrl('check_in_selfies') ?: url('images/placeholder-image.jpg'),
           'check_out_selfie_url' => $this->getFirstMediaUrl('check_out_selfies') ?: url('images/placeholder-image.jpg'),

            'status' => $this->status,
            'attendance_time' => $this->attendance_time
                ? $this->attendance_time->toDateTimeString()
                : null,
            'device_id' => $this->device_id,
            'device_name' => $this->device_name,
            'attendance_allowed' => $this->attendance_allowed,
            'notes' => $this->notes,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'council_position' => new CouncilPositionResource($this->whenLoaded('councilPosition')),
            'event' => new EventResource($this->whenLoaded('event')),
        ];
    }

    protected function formattedDate($date)
    {
        return Carbon::parse($date)->format('m/d/Y, g:i A');
        // return Carbon::parse($date)->format('l, F j, Y, g:i A');
    }

    
}
