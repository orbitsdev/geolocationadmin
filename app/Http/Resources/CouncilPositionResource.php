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
            'grant_access' => $this->grant_access ??false,
            'total_to_do_tasks' => $this->total_to_do_tasks ?? 0,
            'total_in_progress_tasks' => $this->total_in_progress_tasks ?? 0,
            'total_completed_tasks' => $this->total_completed_tasks ?? 0,
            'total_completed_late_tasks' => $this->total_completed_late_tasks ?? 0,
            'total_due_tasks' => $this->total_due_tasks?? 0,
            'total_on_hold_tasks' => $this->total_on_hold_tasks?? 0,
            'total_cancelled_tasks' => $this->total_cancelled_tasks?? 0,
            'total_review_tasks' => $this->total_review_tasks?? 0,
            'total_blocked_tasks' => $this->total_blocked_tasks?? 0,

        ];
    }
}
