<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostCounsilPositionResource extends JsonResource
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
    
        ];
    }
}
