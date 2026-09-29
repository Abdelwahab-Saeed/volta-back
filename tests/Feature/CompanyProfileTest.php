<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Partner;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_partners_api_returns_only_active_rows_in_admin_order(): void
    {
        Partner::create(['name_ar' => 'ب', 'name_en' => 'B', 'type' => 'partner', 'logo' => 'b.png', 'sort_order' => 2]);
        Partner::create(['name_ar' => 'أ', 'name_en' => 'A', 'type' => 'client', 'logo' => 'a.png', 'sort_order' => 1]);
        Partner::create(['name_ar' => 'مخفي', 'name_en' => 'Hidden', 'type' => 'partner', 'logo' => 'h.png', 'is_active' => false]);

        $this->getJson('/api/partners', ['Accept-Language' => 'en'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'A')
            ->assertJsonPath('data.0.type', 'client')
            ->assertJsonPath('data.1.name', 'B');

        $this->getJson('/api/partners?type=partner')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name_en', 'B');

        $this->getJson('/api/partners?type=nonsense')->assertStatus(422);
    }

    public function test_name_falls_back_to_the_other_language(): void
    {
        Partner::create(['name_en' => 'Only English', 'type' => 'partner', 'logo' => 'x.png']);

        $this->getJson('/api/partners', ['Accept-Language' => 'ar'])
            ->assertJsonPath('data.0.name', 'Only English');
    }

    public function test_certificates_and_team_apis_are_localized(): void
    {
        Certificate::create([
            'title_ar' => 'أيزو', 'title_en' => 'ISO', 'issuer_ar' => 'جهة', 'issuer_en' => 'Body',
            'image' => 'c.png', 'issued_year' => 2024,
        ]);
        Certificate::create(['title_en' => 'Hidden', 'image' => 'h.png', 'is_active' => false]);
        TeamMember::create(['name_ar' => 'أحمد', 'name_en' => 'Ahmed', 'role_ar' => 'مهندس', 'role_en' => 'Engineer']);

        $this->getJson('/api/certificates', ['Accept-Language' => 'ar'])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'أيزو')
            ->assertJsonPath('data.0.issuer', 'جهة')
            ->assertJsonPath('data.0.issued_year', 2024);

        $this->getJson('/api/team', ['Accept-Language' => 'en'])
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Ahmed')
            ->assertJsonPath('data.0.role', 'Engineer')
            ->assertJsonPath('data.0.photo', null);
    }

    public function test_admin_can_create_update_and_delete_a_partner(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.partners.store'), [
            'name_en' => 'Acme',
            'type' => 'client',
            'logo' => UploadedFile::fake()->image('logo.png', 300, 150),
            'website_url' => 'https://acme.test',
            'sort_order' => 3,
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $partner = Partner::sole();
        $this->assertSame('client', $partner->type);
        $this->assertTrue($partner->is_active);
        Storage::disk('public')->assertExists($partner->logo);
        $oldLogo = $partner->logo;

        // Unchecked box = hidden; a new logo replaces (and deletes) the old one.
        $this->actingAs($admin)->put(route('admin.partners.update', $partner), [
            'name_ar' => 'أكمي',
            'type' => 'partner',
            'logo' => UploadedFile::fake()->image('new.png'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $partner->refresh();
        $this->assertFalse($partner->is_active);
        $this->assertSame('partner', $partner->type);
        Storage::disk('public')->assertMissing($oldLogo);
        Storage::disk('public')->assertExists($partner->logo);

        $this->actingAs($admin)->delete(route('admin.partners.destroy', $partner))->assertRedirect();
        $this->assertDatabaseCount('partners', 0);
        Storage::disk('public')->assertMissing($partner->logo);
    }

    public function test_partner_needs_a_name_in_one_language_and_a_logo(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.partners.store'), ['type' => 'partner'])
            ->assertSessionHasErrors(['name_ar', 'name_en', 'logo']);
    }

    public function test_admin_can_manage_certificates_and_team(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.certificates.store'), [
            'title_ar' => 'أيزو 9001',
            'image' => UploadedFile::fake()->image('cert.jpg', 600, 800),
            'issued_year' => 2023,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame(2023, Certificate::sole()->issued_year);

        $this->actingAs($admin)->post(route('admin.team-members.store'), [
            'name_ar' => 'سارة',
            'role_en' => 'Sales',
            'photo' => UploadedFile::fake()->image('p.jpg'),
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $member = TeamMember::sole();
        $photo = $member->photo;

        $this->actingAs($admin)->put(route('admin.team-members.update', $member), [
            'name_ar' => 'سارة',
            'role_en' => 'Sales',
            'remove_photo' => '1',
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($member->fresh()->photo);
        Storage::disk('public')->assertMissing($photo);

        foreach (['admin.partners.index', 'admin.certificates.index', 'admin.team-members.index', 'admin.partners.create', 'admin.certificates.create', 'admin.team-members.create'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
        $this->actingAs($admin)->get(route('admin.team-members.edit', $member))->assertOk();
        $this->actingAs($admin)->get(route('admin.certificates.edit', Certificate::sole()))->assertOk();
    }

    public function test_non_admins_cannot_reach_the_admin_pages(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.partners.index'))
            ->assertStatus(403);
    }
}
