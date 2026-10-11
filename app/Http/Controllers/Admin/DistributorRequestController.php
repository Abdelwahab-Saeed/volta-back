<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReadsListFilters;
use App\Http\Controllers\Controller;
use App\Models\DistributorRequest;
use App\Support\Governorates;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** "Become a distributor" requests: list, review, follow up, and add the ones that came in by phone. */
class DistributorRequestController extends Controller
{
    use ReadsListFilters;

    public function index(Request $request)
    {
        $filters = $this->listFilters($request, [
            'q' => 'search',
            'status' => array_keys(DistributorRequest::STATUSES),
            'governorate' => Governorates::keys(),
            'has_shop' => ['1', '0'],
            'has_commercial_register' => ['1', '0'],
            'from' => 'date',
            'to' => 'date',
        ]);

        $requests = DistributorRequest::query()
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($query) use ($term) {
                    foreach (['name', 'phone', 'center', 'area'] as $column) {
                        $query->orWhere($column, 'like', "%{$term}%");
                    }
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['governorate'] ?? null, fn ($query, $governorate) => $query->where('governorate', $governorate))
            ->when(isset($filters['has_shop']), fn ($query) => $query->where('has_shop', $filters['has_shop'] === '1'))
            ->when(isset($filters['has_commercial_register']), fn ($query) => $query->where('has_commercial_register', $filters['has_commercial_register'] === '1'))
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->where('created_at', '>=', $this->dayBoundary($date)))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->where('created_at', '<=', $this->dayBoundary($date, end: true)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.distributor-requests.index', compact('requests', 'filters'));
    }

    public function create()
    {
        return view('admin.distributor-requests.create', [
            'distributorRequest' => new DistributorRequest(['status' => 'new']),
        ]);
    }

    public function store(Request $request)
    {
        $distributorRequest = DistributorRequest::create($this->validated($request) + ['source' => 'admin']);

        return redirect()->route('admin.distributor-requests.show', $distributorRequest)->with('success', 'تمت إضافة الطلب بنجاح.');
    }

    public function show(DistributorRequest $distributorRequest)
    {
        return view('admin.distributor-requests.show', compact('distributorRequest'));
    }

    public function edit(DistributorRequest $distributorRequest)
    {
        return view('admin.distributor-requests.edit', compact('distributorRequest'));
    }

    public function update(Request $request, DistributorRequest $distributorRequest)
    {
        $distributorRequest->update($this->validated($request));

        return redirect()->route('admin.distributor-requests.show', $distributorRequest)->with('success', 'تم تحديث الطلب بنجاح.');
    }

    /** Status (and optionally the internal notes) from the list's inline picker or the request page. */
    public function updateStatus(Request $request, DistributorRequest $distributorRequest)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(DistributorRequest::STATUSES))],
            'admin_notes' => 'sometimes|nullable|string|max:2000',
        ], [], DistributorRequest::attributeNames());

        $distributorRequest->update($data);

        return back()->with('success', 'تم تحديث حالة الطلب.');
    }

    public function destroy(DistributorRequest $distributorRequest)
    {
        $distributorRequest->delete();

        return redirect()->route('admin.distributor-requests.index')->with('success', 'تم حذف الطلب.');
    }

    private function validated(Request $request): array
    {
        $request->merge(['phone' => DistributorRequest::normalizePhone($request->input('phone'))]);

        return $request->validate(DistributorRequest::rules() + [
            'status' => ['required', Rule::in(array_keys(DistributorRequest::STATUSES))],
            'admin_notes' => 'nullable|string|max:2000',
        ], [
            'phone.regex' => 'رقم الهاتف يجب أن يكون رقم موبايل مصري من 11 رقماً يبدأ بـ 01.',
        ], DistributorRequest::attributeNames());
    }
}
