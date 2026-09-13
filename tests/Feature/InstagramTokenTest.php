<?php

namespace Tests\Feature;

use App\Mail\InstagramTokenAlertMail;
use App\Models\Setting;
use App\Models\User;
use App\Services\InstagramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InstagramTokenTest extends TestCase
{
    use RefreshDatabase;

    private function service(): InstagramService
    {
        return app(InstagramService::class);
    }

    private function setMeta(array $overrides): void
    {
        Setting::set('instagram_token_meta', array_merge([
            'refreshed_at'    => now()->subDays(40)->toIso8601String(),
            'expires_at'      => now()->addDays(20)->toIso8601String(),
            'last_attempt_at' => now()->subDays(40)->toIso8601String(),
            'last_error'      => null,
            'failure_count'   => 0,
        ], $overrides));
    }

    public function test_successful_refresh_persists_token_and_meta_and_busts_feed_cache(): void
    {
        Setting::set('instagram_token', 'OLD_TOKEN');
        Cache::put('instagram_feed_posts', [['image' => 'x']], 3600);

        Http::fake([
            'graph.instagram.com/refresh_access_token*' => Http::response([
                'access_token' => 'NEW_TOKEN',
                'expires_in'   => 60 * 86400,
            ], 200),
        ]);

        $result = $this->service()->refreshToken();

        $this->assertTrue($result['ok']);
        $this->assertSame('NEW_TOKEN', Setting::get('instagram_token'));
        $this->assertFalse(Cache::has('instagram_feed_posts'));

        $meta = $this->service()->getTokenMeta();
        $this->assertSame(0, $meta['failure_count']);
        $this->assertNull($meta['last_error']);
        $this->assertTrue(now()->addDays(58)->lt($meta['expires_at']));
    }

    public function test_failed_refresh_keeps_old_token_and_records_error(): void
    {
        Mail::fake();
        $this->service()->storeToken('OLD_TOKEN', 60 * 86400);
        $originalExpiry = $this->service()->getTokenMeta()['expires_at'];

        Http::fake([
            'graph.instagram.com/*' => Http::response(['error' => ['message' => 'Invalid OAuth token']], 400),
        ]);

        $result = $this->service()->refreshToken();

        $this->assertFalse($result['ok']);
        $this->assertSame('OLD_TOKEN', Setting::get('instagram_token'));

        $meta = $this->service()->getTokenMeta();
        $this->assertSame('Invalid OAuth token', $meta['last_error']);
        $this->assertSame(1, $meta['failure_count']);
        $this->assertSame($originalExpiry, $meta['expires_at']);
    }

    public function test_alert_email_sent_once_per_day_to_admins_and_superadmins_on_near_expiry_failure(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $super = User::factory()->create();
        $super->forceFill(['role' => 'superadmin'])->save();
        User::factory()->create(); // a plain customer — must not receive it

        Setting::set('instagram_token', 'OLD_TOKEN');
        $this->setMeta(['expires_at' => now()->addDays(5)->toIso8601String()]);

        Http::fake([
            'graph.instagram.com/*' => Http::response(['error' => ['message' => 'expired']], 400),
        ]);

        $this->service()->refreshToken();
        $this->service()->refreshToken(); // second failure same day

        Mail::assertSent(InstagramTokenAlertMail::class, 1);
        Mail::assertSent(InstagramTokenAlertMail::class, function (InstagramTokenAlertMail $mail) use ($admin, $super) {
            return $mail->hasTo($admin->email) && $mail->hasTo($super->email);
        });
    }

    public function test_no_alert_when_expiry_is_comfortably_far_away(): void
    {
        Mail::fake();
        Setting::set('instagram_token', 'OLD_TOKEN');
        $this->setMeta(['expires_at' => now()->addDays(40)->toIso8601String()]);

        Http::fake(['graph.instagram.com/*' => Http::response(['error' => ['message' => 'nope']], 400)]);

        $this->service()->refreshToken();

        Mail::assertNothingSent();
    }

    public function test_ensure_token_fresh_skips_when_token_was_refreshed_recently(): void
    {
        Setting::set('instagram_token', 'TOKEN');
        $this->setMeta([
            'refreshed_at' => now()->subHours(3)->toIso8601String(),
            'expires_at'   => now()->addDays(2)->toIso8601String(),
        ]);

        $this->assertFalse($this->service()->ensureTokenFresh());
    }

    public function test_ensure_token_fresh_skips_when_expiry_is_far_away(): void
    {
        Setting::set('instagram_token', 'TOKEN');
        $this->setMeta(['expires_at' => now()->addDays(45)->toIso8601String()]);

        $this->assertFalse($this->service()->ensureTokenFresh());
    }

    public function test_ensure_token_fresh_dispatches_when_expiry_is_near(): void
    {
        Http::fake();
        Setting::set('instagram_token', 'TOKEN');
        $this->setMeta(['expires_at' => now()->addDays(10)->toIso8601String()]);

        $this->assertTrue($this->service()->ensureTokenFresh());
    }

    public function test_ensure_token_fresh_dispatches_when_expiry_is_unknown(): void
    {
        Http::fake();
        Setting::set('instagram_token', 'LEGACY_TOKEN');
        // no meta at all

        $this->assertTrue($this->service()->ensureTokenFresh());
    }

    public function test_ensure_token_fresh_respects_the_check_lock(): void
    {
        Http::fake();
        Setting::set('instagram_token', 'TOKEN');
        $this->setMeta(['expires_at' => now()->addDays(10)->toIso8601String()]);

        $this->assertTrue($this->service()->ensureTokenFresh());
        $this->assertFalse($this->service()->ensureTokenFresh());
    }

    public function test_ensure_token_fresh_does_nothing_without_a_token(): void
    {
        $this->assertFalse($this->service()->ensureTokenFresh());
    }

    public function test_admin_refresh_route_requires_admin(): void
    {
        $this->post(route('admin.design.instagram.refresh'))->assertRedirect(route('login'));

        $customer = User::factory()->create();
        $this->actingAs($customer)->post(route('admin.design.instagram.refresh'))->assertRedirect(route('home'));
    }

    public function test_admin_refresh_route_runs_the_refresh_and_flashes_the_result(): void
    {
        $admin = User::factory()->admin()->create();
        Setting::set('instagram_token', 'OLD_TOKEN');

        Http::fake([
            'graph.instagram.com/refresh_access_token*' => Http::response([
                'access_token' => 'NEW_TOKEN',
                'expires_in'   => 60 * 86400,
            ], 200),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.design.instagram.refresh'))
            ->assertRedirect(route('admin.design.index'))
            ->assertSessionHas('success');

        $this->assertSame('NEW_TOKEN', Setting::get('instagram_token'));
    }

    public function test_design_update_persists_a_pasted_token_but_leaves_it_when_blank(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.design.update'), [
            'type'                   => 'instagram',
            'instagram_handle'       => 'madhavi_stores',
            'instagram_access_token' => 'PASTED_TOKEN',
        ])->assertRedirect(route('admin.design.index'));

        $this->assertSame('PASTED_TOKEN', Setting::get('instagram_token'));
        $this->assertNotEmpty($this->service()->getTokenMeta());

        $this->actingAs($admin)->post(route('admin.design.update'), [
            'type'                   => 'instagram',
            'instagram_handle'       => 'madhavi_stores',
            'instagram_access_token' => '',
        ])->assertRedirect(route('admin.design.index'));

        $this->assertSame('PASTED_TOKEN', Setting::get('instagram_token'));
    }

    public function test_design_manager_page_renders_with_the_instagram_status_block(): void
    {
        $admin = User::factory()->admin()->create();
        $this->service()->storeToken('SOME_TOKEN', 60 * 86400);

        $this->actingAs($admin)->get(route('admin.design.index'))
            ->assertOk()
            ->assertSee('Instagram Feed')
            ->assertSee('Token active', false);
    }

    public function test_console_command_reports_cleanly_with_no_token(): void
    {
        $this->artisan('instagram:refresh-token')
            ->expectsOutputToContain('No Instagram access token configured.')
            ->assertExitCode(1);
    }
}
