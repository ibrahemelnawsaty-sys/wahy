<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'avatar_url' => $this->avatar
                ? asset('storage/' . $this->avatar)
                : asset('storage/data/avatars/default-avatar.webp'),
            'school' => $this->whenLoaded('school', fn () => [
                'id' => $this->school->id,
                'name' => $this->school->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
