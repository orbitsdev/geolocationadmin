<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
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
            'check_in_time' => $this->check_in_time ? $this->check_in_time->toDateTimeString() : null,
            'check_out_time' => $this->check_out_time ? $this->check_out_time->toDateTimeString() : null,
            'device_id' => $this->device_id,
            'device_name' => $this->device_name,
            'selfie_image_url' => $this->selfie_image ? url('storage/' . $this->selfie_image) : null, // Provide the URL to the stored selfie image
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'council_position' => new CouncilPositionResource($this->whenLoaded('councilPosition')), // Include council position if loaded
        ];
    }
}
