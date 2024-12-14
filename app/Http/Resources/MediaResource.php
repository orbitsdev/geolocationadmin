<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\CouncilPositionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $model = $this->model; // Retrieve the owning model (e.g., Task)

        return [
            'id' => $this->id, // Media ID
            'file_name' => $this->file_name,
            'url' => $this->getUrl(),
            'type' => $this->mime_type,
            'extension' => $this->file_name ? pathinfo($this->file_name, PATHINFO_EXTENSION) : null,
            'size' => $this->size,
            'created_at' => $this->created_at ? $this->created_at->format('F j, Y, g:i A') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('F j, Y, g:i A') : null,

            // Include the related CouncilPosition resource if available
            'council_position' => $model && method_exists($model, 'councilPosition')
                ? new CouncilPositionResource($model->councilPosition)
                : null,
        ];
    }
}
