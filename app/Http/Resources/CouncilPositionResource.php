<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouncilPositionResource extends JsonResource
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
            'council_id' => $this->council_id,
            'council_name' => $this->council->name ?? null,
            'user_id' => $this->user_id,
            'fullname'=> $this->user->fullName(),
            'email'=> $this->user->email,
            'image'=> $this->user->getImage(),
            'position' => $this->position,
            'is_login' => $this->is_login,
            'grant_access' => $this->grant_access ??false,
            'total_to_do_tasks' => $this->total_to_do_tasks ?? 0,
            'total_in_progress_tasks' => $this->total_in_progress_tasks ?? 0,
            'total_completed_tasks' => $this->total_completed_tasks ?? 0,
            'total_needs_revision' => $this->total_needs_revision ?? 0,
            'total_rejected' => $this->total_rejected ?? 0,
          

        ];
    }
}
