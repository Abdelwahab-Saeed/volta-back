<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Traits\ApiResponse;

class BannerController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of active banners.
     */
    public function index()
    {
        $banners = \App\Models\Banner::where('status', true)->latest()->get();
        return $this->successResponse(\App\Http\Resources\BannerResource::collection($banners), __('api.banners_fetched'));
    }
}
