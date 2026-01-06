<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|max:2048',
            'status' => 'boolean',
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads/banners', 'public');
        }

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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'status' => 'boolean',
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status');

        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('uploads/banners', 'public');
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'تم تحديث البانر بنجاح.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'تم حذف البانر بنجاح.');
    }
}
