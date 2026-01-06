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
        $banners = Banner::where('status', true)->latest()->get();
        return $this->successResponse($banners, 'تم جلب البانرات بنجاح');
    }
}
