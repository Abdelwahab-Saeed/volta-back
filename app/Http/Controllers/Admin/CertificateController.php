<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesUploads;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    use ManagesUploads;

    public function index()
    {
        $certificates = Certificate::orderBy('sort_order')->orderBy('id')->paginate(20);

        return view('admin.certificates.index', compact('certificates'));
    }

    public function create()
    {
        return view('admin.certificates.create', ['certificate' => new Certificate(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['image'] = $this->replaceUpload($request, 'image', null, 'uploads/certificates');

        Certificate::create($data);

        return redirect()->route('admin.certificates.index')->with('success', 'تمت إضافة الشهادة بنجاح.');
    }

    public function edit(Certificate $certificate)
    {
        return view('admin.certificates.edit', compact('certificate'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $data = $this->validated($request, false);
        $data['image'] = $this->replaceUpload($request, 'image', $certificate->image, 'uploads/certificates');

        $certificate->update($data);

        return redirect()->route('admin.certificates.index')->with('success', 'تم تحديث الشهادة بنجاح.');
    }

    public function destroy(Certificate $certificate)
    {
        // Soft delete: the image stays with the row
        $certificate->delete();

        return redirect()->route('admin.certificates.index')->with('success', 'تم حذف الشهادة بنجاح.');
    }

    private function validated(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'title_ar' => 'nullable|required_without:title_en|string|max:255',
            'title_en' => 'nullable|required_without:title_ar|string|max:255',
            'issuer_ar' => 'nullable|string|max:255',
            'issuer_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string|max:2000',
            'description_en' => 'nullable|string|max:2000',
            'image' => [$creating ? 'required' : 'nullable', 'image', 'max:4096'],
            'issued_year' => 'nullable|integer|min:1950|max:'.(now()->year + 1),
            'sort_order' => 'nullable|integer|min:0|max:100000',
        ], [], [
            'title_ar' => 'العنوان بالعربية',
            'title_en' => 'العنوان بالإنجليزية',
            'image' => 'صورة الشهادة',
            'issued_year' => 'سنة الحصول',
            'sort_order' => 'الترتيب',
        ]);

        unset($data['image']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
