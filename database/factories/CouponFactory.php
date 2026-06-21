<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code'              => strtoupper(fake()->unique()->bothify('SAVE####')),
            'type'              => 'percent',
            'value'             => 10,
            'min_cart_value'    => 0,
            'is_active'         => true,
            'expires_at'        => null,
            'max_uses'          => null,
            'used_count'        => 0,
            'max_uses_per_user' => 1,
        ];
    }

    public function fixed(float $value): static
    {
        return $this->state(fn () => ['type' => 'fixed', 'value' => $value]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }
}
