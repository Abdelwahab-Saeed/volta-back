<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesUploads;
use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    use ManagesUploads;

    public function index()
    {
        $members = TeamMember::orderBy('sort_order')->orderBy('id')->paginate(20);

        return view('admin.team-members.index', compact('members'));
    }

    public function create()
    {
        return view('admin.team-members.create', ['member' => new TeamMember(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['photo'] = $this->replaceUpload($request, 'photo', null, 'uploads/team');

        TeamMember::create($data);

        return redirect()->route('admin.team-members.index')->with('success', 'تمت إضافة العضو بنجاح.');
    }

    public function edit(TeamMember $teamMember)
    {
        return view('admin.team-members.edit', ['member' => $teamMember]);
    }

    public function update(Request $request, TeamMember $teamMember)
    {
        $data = $this->validated($request);
        $data['photo'] = $this->replaceUpload($request, 'photo', $teamMember->photo, 'uploads/team');

        if ($request->boolean('remove_photo') && ! $request->hasFile('photo')) {
            $this->deleteUpload($teamMember->photo);
            $data['photo'] = null;
        }

        $teamMember->update($data);

        return redirect()->route('admin.team-members.index')->with('success', 'تم تحديث بيانات العضو بنجاح.');
    }

    public function destroy(TeamMember $teamMember)
    {
        // Soft delete: the photo stays with the row
        $teamMember->delete();

        return redirect()->route('admin.team-members.index')->with('success', 'تم حذف العضو بنجاح.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name_ar' => 'nullable|required_without:name_en|string|max:255',
            'name_en' => 'nullable|required_without:name_ar|string|max:255',
            'role_ar' => 'nullable|required_without:role_en|string|max:255',
            'role_en' => 'nullable|required_without:role_ar|string|max:255',
            'bio_ar' => 'nullable|string|max:1000',
            'bio_en' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:2048',
            'linkedin_url' => 'nullable|url|max:255',
            'sort_order' => 'nullable|integer|min:0|max:100000',
        ], [], [
            'name_ar' => 'الاسم بالعربية',
            'name_en' => 'الاسم بالإنجليزية',
            'role_ar' => 'المسمى الوظيفي بالعربية',
            'role_en' => 'المسمى الوظيفي بالإنجليزية',
            'photo' => 'الصورة',
            'linkedin_url' => 'رابط لينكدإن',
            'sort_order' => 'الترتيب',
        ]);

        unset($data['photo']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
