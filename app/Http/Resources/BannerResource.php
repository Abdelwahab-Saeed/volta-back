<?php
  
  namespace App\Http\Resources;
  
  use Illuminate\Http\Request;
  use Illuminate\Http\Resources\Json\JsonResource;
  
  class BannerResource extends JsonResource
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
              'title_ar' => $this->title_ar,
              'title_en' => $this->title_en,
              'description' => $this->description,
              'description_ar' => $this->description_ar,
              'description_en' => $this->description_en,
              'image' => $this->image,
              'status' => (bool) $this->status,
          ];
      }
  }
