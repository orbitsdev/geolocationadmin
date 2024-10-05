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
            'image'=> $this->user->getImage(),
            'position' => $this->position,
            'is_login' => $this->is_login,
            'total_to_do_tasks' => $this->total_to_do_tasks,
            'total_in_progress_tasks' => $this->total_in_progress_tasks,
            'total_completed_tasks' => $this->total_completed_tasks,
            'total_completed_late_tasks' => $this->total_completed_late_tasks,
            'total_due_tasks' => $this->total_due_tasks,
            'total_on_hold_tasks' => $this->total_on_hold_tasks,
            'total_cancelled_tasks' => $this->total_cancelled_tasks,
            'total_review_tasks' => $this->total_review_tasks,
            'total_blocked_tasks' => $this->total_blocked_tasks,

        ];
    }
}
