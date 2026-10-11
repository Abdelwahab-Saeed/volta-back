<?php

namespace Tests\Feature;

use App\Models\DistributorRequest;
use App\Models\User;
use App\Notifications\NewDistributorRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DistributorRequestTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'محمد علي',
            'phone' => '01012345678',
            'governorate' => 'Sharqia',
            'center' => 'بلبيس',
            'area' => 'قرية الزوامل',
            'has_commercial_register' => true,
            'has_shop' => false,
            'notes' => 'عندي محل أدوات كهربائية',
        ], $overrides);
    }

    private function makeRequest(array $overrides = []): DistributorRequest
    {
        return DistributorRequest::create(array_merge($this->payload(), ['status' => 'new', 'source' => 'website'], $overrides));
    }

    public function test_storefront_submission_is_saved_and_admins_are_notified(): void
    {
        Notification::fake();
        $admin = $this->admin();

        $this->postJson('/api/distributor-requests', $this->payload(['phone' => '٠١٠ 1234-5678']), ['Accept-Language' => 'en'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('api.distributor_request_received', [], 'en'));

        $saved = DistributorRequest::sole();
        $this->assertSame('01012345678', $saved->phone); // Arabic digits and separators normalized
        $this->assertSame('new', $saved->status);
        $this->assertSame('website', $saved->source);
        $this->assertTrue($saved->has_commercial_register);
        $this->assertFalse($saved->has_shop);

        Notification::assertSentTo($admin, NewDistributorRequestNotification::class,
            fn ($notification) => $notification->toArray($admin)['distributor_request_id'] === $saved->id);
    }

    public function test_invalid_submissions_are_rejected(): void
    {
        $this->postJson('/api/distributor-requests', $this->payload(['phone' => '12345']))->assertStatus(422)->assertJsonValidationErrors('phone');
        $this->postJson('/api/distributor-requests', $this->payload(['governorate' => 'Atlantis']))->assertStatus(422)->assertJsonValidationErrors('governorate');

        $missing = $this->payload();
        unset($missing['has_shop'], $missing['center']);
        $this->postJson('/api/distributor-requests', $missing)->assertStatus(422)->assertJsonValidationErrors(['has_shop', 'center']);

        $this->assertDatabaseCount('distributor_requests', 0);
    }

    public function test_honeypot_and_repeat_submissions_are_not_saved_twice(): void
    {
        Notification::fake();
        $this->admin();

        $this->postJson('/api/distributor-requests', $this->payload(['website' => 'http://spam.example']))->assertCreated();
        $this->assertDatabaseCount('distributor_requests', 0);

        $this->postJson('/api/distributor-requests', $this->payload())->assertCreated();
        $this->postJson('/api/distributor-requests', $this->payload())->assertCreated();
        $this->assertDatabaseCount('distributor_requests', 1);
        Notification::assertCount(1);

        // Once the team has followed up, the same trader can apply again
        DistributorRequest::sole()->update(['status' => 'contacted']);
        $this->postJson('/api/distributor-requests', $this->payload())->assertCreated();
        $this->assertDatabaseCount('distributor_requests', 2);
    }

    public function test_submissions_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/distributor-requests', $this->payload(['phone' => '0101234567' . $i]))->assertCreated();
        }

        $this->postJson('/api/distributor-requests', $this->payload(['phone' => '01112345678']))->assertStatus(429);
    }

    public function test_admin_pages_are_for_admins_only(): void
    {
        $item = $this->makeRequest();
        $customer = User::factory()->create(['role' => 'user']);

        $this->get(route('admin.distributor-requests.index'))->assertRedirect();
        $this->actingAs($customer)->get(route('admin.distributor-requests.show', $item))->assertForbidden();
    }

    public function test_admin_pages_render_and_filter(): void
    {
        $admin = $this->admin();
        $shop = $this->makeRequest(['name' => 'تاجر بمحل', 'has_shop' => true, 'governorate' => 'Cairo']);
        $this->makeRequest(['name' => 'تاجر بدون محل', 'phone' => '01198765432', 'status' => 'approved']);

        $this->actingAs($admin);
        $this->get(route('admin.distributor-requests.index'))->assertOk()->assertSee('تاجر بمحل')->assertSee('تاجر بدون محل');
        $this->get(route('admin.distributor-requests.index', ['has_shop' => '1']))->assertOk()->assertSee('تاجر بمحل')->assertDontSee('تاجر بدون محل');
        $this->get(route('admin.distributor-requests.index', ['status' => 'approved']))->assertOk()->assertDontSee('تاجر بمحل');
        $this->get(route('admin.distributor-requests.index', ['governorate' => 'Cairo']))->assertOk()->assertSee('تاجر بمحل')->assertDontSee('تاجر بدون محل');
        $this->get(route('admin.distributor-requests.index', ['q' => '0119876']))->assertOk()->assertSee('تاجر بدون محل')->assertDontSee('تاجر بمحل');

        $this->get(route('admin.distributor-requests.show', $shop))->assertOk()->assertSee('القاهرة')->assertSee('01012345678');
        $this->get(route('admin.distributor-requests.create'))->assertOk();
        $this->get(route('admin.distributor-requests.edit', $shop))->assertOk();
    }

    public function test_admin_updates_status_and_internal_notes(): void
    {
        $item = $this->makeRequest();

        $this->actingAs($this->admin())
            ->patch(route('admin.distributor-requests.status', $item), ['status' => 'contacted', 'admin_notes' => 'اتصلنا به'])
            ->assertRedirect();

        $item->refresh();
        $this->assertSame('contacted', $item->status);
        $this->assertSame('اتصلنا به', $item->admin_notes);

        $this->patch(route('admin.distributor-requests.status', $item), ['status' => 'nonsense'])->assertSessionHasErrors('status');
    }

    public function test_admin_can_add_and_edit_a_request_without_notifying(): void
    {
        Notification::fake();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.distributor-requests.store'), $this->payload(['has_commercial_register' => '0', 'has_shop' => '1', 'status' => 'contacted']))
            ->assertRedirect();

        $item = DistributorRequest::sole();
        $this->assertSame('admin', $item->source);
        $this->assertSame('contacted', $item->status);
        $this->assertTrue($item->has_shop);
        Notification::assertNothingSent();

        $this->put(route('admin.distributor-requests.update', $item), $this->payload(['name' => 'اسم معدل', 'has_commercial_register' => '1', 'has_shop' => '0', 'status' => 'approved']))
            ->assertRedirect(route('admin.distributor-requests.show', $item));
        $this->assertSame('اسم معدل', $item->refresh()->name);
        $this->assertSame('admin', $item->source); // editing keeps where it came from
    }

    public function test_delete_is_soft(): void
    {
        $item = $this->makeRequest();

        $this->actingAs($this->admin())->delete(route('admin.distributor-requests.destroy', $item))
            ->assertRedirect(route('admin.distributor-requests.index'));

        $this->assertSoftDeleted($item);
    }

    public function test_notification_links_to_the_request(): void
    {
        $admin = $this->admin();
        $item = $this->makeRequest();
        $admin->notify(new NewDistributorRequestNotification($item));

        $this->actingAs($admin)->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee(route('admin.distributor-requests.show', $item));
    }
}
