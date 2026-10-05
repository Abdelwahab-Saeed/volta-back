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
        $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'required|image|max:10240',
            'status' => 'boolean',
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status');

        $data['image'] = $this->replaceUpload($request, 'image', null, 'uploads/banners');

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
        $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'status' => 'boolean',
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status');

        $data['image'] = $this->replaceUpload($request, 'image', $banner->image, 'uploads/banners');

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'تم تحديث البانر بنجاح.');
    }

    public function destroy(Banner $banner)
    {
        // Soft delete: the image stays with the row
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'تم حذف البانر بنجاح.');
    }
}
