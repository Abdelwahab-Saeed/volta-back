<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'role' => $this->role,
            'role_ar' => $this->role_ar,
            'role_en' => $this->role_en,
            'bio' => $this->bio,
            'bio_ar' => $this->bio_ar,
            'bio_en' => $this->bio_en,
            'photo' => $this->photo,
            'linkedin_url' => $this->linkedin_url,
        ];
    }
}
