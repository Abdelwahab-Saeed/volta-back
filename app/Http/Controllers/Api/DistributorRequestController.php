<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributorRequest;
use App\Models\User;
use App\Notifications\NewDistributorRequestNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

/**
 * POST /api/distributor-requests — the storefront's "Become a Volta distributor" form.
 * Rate limited (throttle:distributor-requests). The admin manages the requests in the dashboard.
 */
class DistributorRequestController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $request->merge(['phone' => DistributorRequest::normalizePhone($request->input('phone'))]);
        $data = $request->validate(DistributorRequest::rules());

        // Bots fill every field, including the hidden one; pretend it worked so they don't adapt.
        if (filled($request->input('website'))) {
            return $this->successResponse(null, __('api.distributor_request_received'), 201);
        }

        // A double tap or a repeat within a day is one request, not two.
        $alreadyWaiting = DistributorRequest::where('phone', $data['phone'])
            ->where('status', 'new')
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if (! $alreadyWaiting) {
            $distributorRequest = DistributorRequest::create($data + ['status' => 'new', 'source' => 'website']);

            $admins = User::where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewDistributorRequestNotification($distributorRequest));
            }
        }

        return $this->successResponse(null, __('api.distributor_request_received'), 201);
    }
}
