<?php

namespace Tests\Unit;

use App\Models\Coupon;
use Tests\TestCase;

class CouponTest extends TestCase
{
    private function coupon(array $overrides = []): Coupon
    {
        return new Coupon(array_merge([
            'type'              => 'percent',
            'value'             => 10,
            'min_cart_value'    => 0,
            'is_active'         => true,
            'max_uses'          => null,
            'used_count'        => 0,
            'max_uses_per_user' => 1,
        ], $overrides));
    }

    public function test_valid_percent_coupon(): void
    {
        $c = $this->coupon();
        $this->assertTrue($c->isValidFor(1000, 0));
        $this->assertEquals(100.0, $c->calculateDiscount(1000));
    }

    public function test_fixed_coupon_is_capped_at_cart_total(): void
    {
        $c = $this->coupon(['type' => 'fixed', 'value' => 5000]);
        $this->assertEquals(1000.0, $c->calculateDiscount(1000));
    }

    public function test_inactive_coupon_is_invalid(): void
    {
        $this->assertFalse($this->coupon(['is_active' => false])->isGloballyValid());
    }

    public function test_expired_coupon_is_invalid(): void
    {
        $this->assertFalse($this->coupon(['expires_at' => now()->subDay()])->isGloballyValid());
    }

    public function test_exhausted_max_uses_is_invalid(): void
    {
        $this->assertFalse($this->coupon(['max_uses' => 5, 'used_count' => 5])->isGloballyValid());
    }

    public function test_min_cart_value_enforced(): void
    {
        $c = $this->coupon(['min_cart_value' => 2000]);
        $this->assertFalse($c->isValidFor(1000, 0));
        $this->assertTrue($c->isValidFor(2500, 0));
    }

    public function test_per_user_limit_enforced(): void
    {
        $c = $this->coupon(['max_uses_per_user' => 1]);
        $this->assertTrue($c->isValidFor(1000, 0));
        $this->assertFalse($c->isValidFor(1000, 1));
    }
}
