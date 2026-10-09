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
              // image: tablets and computers (1920×600). image_mobile: phones (1080×630), or null to use image there too.
              'image' => $this->image,
              'image_mobile' => $this->image_mobile,
              // null (not clickable), a full http(s) URL (open outside the store) or a store path starting with / (navigate in the app)
              'redirect_url' => $this->redirect_url,
              'status' => (bool) $this->status,
          ];
      }
  }
