<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\TeamMemberResource;
use App\Models\Certificate;
use App\Models\Partner;
use App\Models\TeamMember;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Company content managed from the admin dashboard and shown on the home page:
 * partners & clients, certificates and the team. Only active rows, in the admin's order.
 */
class CompanyProfileController extends Controller
{
    use ApiResponse;

    /** GET /api/partners?type=partner|client (both types when omitted) */
    public function partners(Request $request)
    {
        $request->validate(['type' => ['nullable', Rule::in(Partner::TYPES)]]);

        $partners = Partner::shown()
            ->when($request->query('type'), fn ($query, $type) => $query->where('type', $type))
            ->get();

        return $this->successResponse(PartnerResource::collection($partners), __('api.partners_fetched'));
    }

    /** GET /api/certificates */
    public function certificates()
    {
        return $this->successResponse(
            CertificateResource::collection(Certificate::shown()->get()),
            __('api.certificates_fetched')
        );
    }

    /** GET /api/team */
    public function team()
    {
        return $this->successResponse(
            TeamMemberResource::collection(TeamMember::shown()->get()),
            __('api.team_fetched')
        );
    }
}
