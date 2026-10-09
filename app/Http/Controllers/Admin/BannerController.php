<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesUploads;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use ManagesUploads;

    public function index()
    {
        $banners = Banner::latest()->paginate(15);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['image'] = $this->replaceUpload($request, 'image', null, 'uploads/banners');
        $data['image_mobile'] = $this->replaceUpload($request, 'image_mobile', null, 'uploads/banners');

        Banner::create($data);

        return redirect()->route('admin.banners.index')->with('success', 'تم إضافة البانر بنجاح.');
    }

    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validated($request, false);
        $data['image'] = $this->replaceUpload($request, 'image', $banner->image, 'uploads/banners');
        $data['image_mobile'] = $this->replaceUpload($request, 'image_mobile', $banner->image_mobile, 'uploads/banners');

        if ($request->boolean('remove_image_mobile') && ! $request->hasFile('image_mobile')) {
            $this->deleteUpload($banner->image_mobile);
            $data['image_mobile'] = null;
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'تم تحديث البانر بنجاح.');
    }

    public function destroy(Banner $banner)
    {
        // Soft delete: the image stays with the row
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'تم حذف البانر بنجاح.');
    }

    private function validated(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => [$creating ? 'required' : 'nullable', 'image', 'max:10240'],
            'image_mobile' => ['nullable', 'image', 'max:10240'],
            // A full http(s) URL, or a store path like /offers/3 (not //host, which browsers treat as another site).
            // Anything else (javascript:, mailto:...) is refused because the store puts it straight into a link.
            'redirect_url' => ['nullable', 'string', 'max:2048', 'regex:~^(https?://[^\s/]+[^\s]*|/(?![/\\\\])[^\s]*)$~i'],
            'status' => 'boolean',
        ], [
            'redirect_url.regex' => 'رابط التوجيه يجب أن يبدأ بـ https:// أو بـ / لصفحة داخل المتجر.',
        ], [
            'image' => 'صورة الكمبيوتر',
            'image_mobile' => 'صورة الموبايل',
            'redirect_url' => 'رابط التوجيه',
        ]);

        unset($data['image'], $data['image_mobile']);
        $data['status'] = $request->has('status');

        return $data;
    }
}
