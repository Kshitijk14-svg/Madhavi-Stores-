<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/coupons')->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_admin(): void
    {
        $user = User::factory()->create(); // role = customer
        $this->actingAs($user)
            ->get('/admin/coupons')
            ->assertRedirect(route('home'));
    }

    public function test_admin_can_access_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get('/admin/coupons')
            ->assertOk();
    }
}
