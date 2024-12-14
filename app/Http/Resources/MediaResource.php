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
        $model = $this->whenLoaded('model'); // Load the model relationship (e.g., Task)

        return [
            'id' => $this->id,
            'file_name' => $this->file_name ?? 'N/A',
            'url' => $this->getUrl(),
            'type' => $this->mime_type ?? 'unknown',
            'extension' => $this->file_name ? pathinfo($this->file_name, PATHINFO_EXTENSION) : null,
            'size' => $this->size ?? 0,
            'collection_name' => $this->collection_name,

            // Include council position data if the model is a Task
            'council_position' => $model instanceof \App\Models\Task
                ? new CouncilPositionResource($model->councilPosition)
                : null,
        ];
    }
}
