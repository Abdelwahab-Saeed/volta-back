<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesUploads;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    use ManagesUploads;

    public function index(Request $request)
    {
        $type = in_array($request->query('type'), Partner::TYPES, true) ? $request->query('type') : null;

        $partners = Partner::query()
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.partners.index', compact('partners', 'type'));
    }

    public function create()
    {
        return view('admin.partners.create', ['partner' => new Partner(['type' => request('type', Partner::TYPE_PARTNER), 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['logo'] = $this->replaceUpload($request, 'logo', null, 'uploads/partners');

        Partner::create($data);

        return redirect()->route('admin.partners.index', ['type' => $data['type']])->with('success', 'تمت الإضافة بنجاح.');
    }

    public function edit(Partner $partner)
    {
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $data = $this->validated($request, false);
        $data['logo'] = $this->replaceUpload($request, 'logo', $partner->logo, 'uploads/partners');

        $partner->update($data);

        return redirect()->route('admin.partners.index', ['type' => $partner->type])->with('success', 'تم التحديث بنجاح.');
    }

    public function destroy(Partner $partner)
    {
        // Soft delete: the logo stays with the row
        $partner->delete();

        return redirect()->route('admin.partners.index')->with('success', 'تم الحذف بنجاح.');
    }

    private function validated(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'name_ar' => 'nullable|required_without:name_en|string|max:255',
            'name_en' => 'nullable|required_without:name_ar|string|max:255',
            'type' => ['required', Rule::in(Partner::TYPES)],
            'logo' => [$creating ? 'required' : 'nullable', 'image', 'max:10240'],
            'website_url' => 'nullable|url|max:255',
            'sort_order' => 'nullable|integer|min:0|max:100000',
        ], [], [
            'name_ar' => 'الاسم بالعربية',
            'name_en' => 'الاسم بالإنجليزية',
            'type' => 'النوع',
            'logo' => 'الشعار',
            'website_url' => 'رابط الموقع',
            'sort_order' => 'الترتيب',
        ]);

        unset($data['logo']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
