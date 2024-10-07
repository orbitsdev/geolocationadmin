<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Resources\CouncilPositionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'check_in_time' => $this->check_in_time ? $this->formattedDate($this->check_in_time) : null,
            'check_out_time' => $this->check_out_time ? $this->formattedDate($this->check_out_time) : null,
            'device_id' => $this->device_id,
            'device_name' => $this->device_name,
            'selfie_image_url' => $this->selfie_image ? url('storage/' . $this->selfie_image) : null,
            'created_at' => $this->created_at ? $this->formattedDate($this->created_at) : null,
            'updated_at' => $this->updated_at ? $this->formattedDate($this->updated_at) : null,
            'council_position' => new CouncilPositionResource($this->whenLoaded('councilPosition')),
        ];
    }

    /**
     * Format the date to be more human-readable, e.g., 'Monday, October 7, 2024, 7:00 PM'.
     *
     * @param  \Carbon\Carbon|string|null  $date
     * @return string|null
     */
    protected function formattedDate($date)
    {
        return Carbon::parse($date)->format('l, F j, Y, g:i A');
    }
}
