<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'question_type' => $this->question_type,
            'points' => (int) ($this->points ?? 0),
            'passing_score' => $this->passing_score,
            'duration_minutes' => $this->duration_minutes,
            'max_attempts' => $this->max_attempts,
            'is_homework' => (bool) $this->is_homework,
            'is_team_activity' => (bool) $this->is_team_activity,
            'is_family_activity' => (bool) $this->is_family_activity,
            'is_featured' => (bool) $this->is_featured,
            'due_date' => $this->due_date?->toIso8601String(),
            'lesson' => $this->whenLoaded('lesson', fn () => [
                'id' => $this->lesson->id,
                'title' => $this->lesson->title,
            ]),
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
