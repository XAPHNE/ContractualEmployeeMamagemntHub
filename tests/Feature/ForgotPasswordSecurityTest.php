<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Models\Setting;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class ForgotPasswordSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clearResolvedInstances();
    }

    public function test_login_page_shows_forgot_password_link_when_enabled(): void
    {
        Setting::set('enable_forgot_password', true);

        $response = $this->get('/admin/login');

        $response->assertSuccessful();
        $response->assertSee('Forgot password?');
    }

    public function test_login_page_hides_forgot_password_link_when_disabled(): void
    {
        Setting::set('enable_forgot_password', false);

        $response = $this->get('/admin/login');

        $response->assertSuccessful();
        $response->assertDontSee('Forgot password?');
    }

    public function test_request_password_reset_page_returns_404_when_disabled(): void
    {
        Setting::set('enable_forgot_password', false);

        $response = $this->get('/admin/password-reset/request');

        $response->assertNotFound();
    }

    public function test_request_password_reset_page_accessible_when_enabled(): void
    {
        Setting::set('enable_forgot_password', true);

        $response = $this->get('/admin/password-reset/request');

        $response->assertSuccessful();
    }

    public function test_rate_limiter_throttles_by_ip(): void
    {
        Setting::set('enable_forgot_password', true);
        Setting::set('forgot_password_max_attempts', 2);
        Setting::set('forgot_password_throttle_by', 'ip');

        $page1 = new RequestPasswordReset;
        $page1->data = ['email' => 'user1@example.com'];
        $page1->executeRateLimit();

        $page2 = new RequestPasswordReset;
        $page2->data = ['email' => 'user2@example.com'];
        $page2->executeRateLimit();

        $this->expectException(TooManyRequestsException::class);
        $page3 = new RequestPasswordReset;
        $page3->data = ['email' => 'user3@example.com'];
        $page3->executeRateLimit();
    }

    public function test_rate_limiter_throttles_by_email(): void
    {
        Setting::set('enable_forgot_password', true);
        Setting::set('forgot_password_max_attempts', 2);
        Setting::set('forgot_password_throttle_by', 'email');

        $page1 = new RequestPasswordReset;
        $page1->data = ['email' => 'target@example.com'];
        $page1->executeRateLimit();

        $page2 = new RequestPasswordReset;
        $page2->data = ['email' => 'target@example.com'];
        $page2->executeRateLimit();

        // Other email should not be blocked
        $otherPage = new RequestPasswordReset;
        $otherPage->data = ['email' => 'other@example.com'];
        $otherPage->executeRateLimit();

        // 3rd attempt for target should be throttled
        $this->expectException(TooManyRequestsException::class);
        $page3 = new RequestPasswordReset;
        $page3->data = ['email' => 'target@example.com'];
        $page3->executeRateLimit();
    }

    public function test_rate_limiter_throttles_by_email_and_ip(): void
    {
        Setting::set('enable_forgot_password', true);
        Setting::set('forgot_password_max_attempts', 1);
        Setting::set('forgot_password_throttle_by', 'email_and_ip');

        $page1 = new RequestPasswordReset;
        $page1->data = ['email' => 'unique@example.com'];
        $page1->executeRateLimit();

        $this->expectException(TooManyRequestsException::class);
        $page2 = new RequestPasswordReset;
        $page2->data = ['email' => 'unique@example.com'];
        $page2->executeRateLimit();
    }

    public function test_request_triggers_danger_notification_when_rate_limited(): void
    {
        Setting::set('enable_forgot_password', true);
        Setting::set('forgot_password_max_attempts', 1);
        Setting::set('forgot_password_throttle_by', 'ip');

        $user = User::factory()->create(['email' => 'notify@example.com']);

        // Attempt 1
        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => $user->email])
            ->call('request');

        // Attempt 2: throttled, should trigger notification
        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => $user->email])
            ->call('request')
            ->assertNotified();
    }

    public function test_system_settings_keys_can_be_stored_and_retrieved(): void
    {
        Setting::set('enable_forgot_password', false);
        Setting::set('forgot_password_max_attempts', 5);
        Setting::set('forgot_password_throttle_by', 'ip');

        $this->assertFalse((bool) Setting::get('enable_forgot_password', true));
        $this->assertEquals(5, (int) Setting::get('forgot_password_max_attempts'));
        $this->assertEquals('ip', Setting::get('forgot_password_throttle_by'));
    }
}
