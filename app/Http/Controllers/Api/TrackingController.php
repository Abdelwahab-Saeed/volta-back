<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MetaService;

class TrackingController extends Controller
{
    protected $metaService;

    public function __construct(MetaService $metaService)
    {
        $this->metaService = $metaService;
    }

    public function pageView(Request $request)
    {
        $url = $request->input('url', request()->headers->get('referer', url('/')));
        
        $this->metaService->sendPageView($url);

        return response()->json(['success' => true]);
    }
}
