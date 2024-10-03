<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\CouncilPositionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'task_details' => $this->task_details,
            'due_date' => $this->due_date->toDateTimeString(),
            'completed_at' => $this->completed_at ? $this->completed_at->toDateTimeString() : null,
            'status' => $this->status,
            'is_lock' => $this->is_lock,
            'is_done' => $this->is_done,
            'assigned_council_position' => new CouncilPositionResource($this->whenLoaded('assignedCouncilPosition')),
            'approved_by_council_position' => new CouncilPositionResource($this->whenLoaded('approvedByCouncilPosition')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
