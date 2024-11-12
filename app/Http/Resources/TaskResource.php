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
            'due_date' => $this->due_date ? $this->due_date->format('F j, Y, g:i A') : null,
            'completed_at' => $this->completed_at ? $this->completed_at->format('F j, Y, g:i A') : null,
            'status' => $this->status,
            'is_lock' => $this->is_lock,
            'is_done' => $this->is_done,
            'status_changed_at' => $this->status_changed_at ? $this->status_changed_at->format('F j, Y, g:i A') : null,
            'remarks' => $this->remarks,  
            'assigned_council_position' => new CouncilPositionResource($this->whenLoaded('assignedCouncilPosition')),
            'approved_by_council_position' => new CouncilPositionResource($this->whenLoaded('approvedByCouncilPosition')),
            'media' => $this->getMedia('task_media')->map(function ($media) {
                $extension = $media->file_name ? pathinfo($media->file_name, PATHINFO_EXTENSION) : null;

                return [
                    'id' => $media->id, // Include the media ID
                    'file_name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'type' => $media->mime_type,
                    'extension' => $extension, 
                ];
            }),

            'created_at' => $this->created_at ? $this->created_at->format('F j, Y, g:i A') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('F j, Y, g:i A') : null,
        ];
    }
}
