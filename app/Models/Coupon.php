<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_cart_value', 'is_active', 'expires_at',
        'max_uses', 'used_count', 'max_uses_per_user',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the coupon is globally valid (not expired, not exhausted).
     */
    public function isGloballyValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return false;
        return true;
    }

    /**
     * Check if the coupon is valid for a given cart total (and optionally user usage).
     */
    public function isValidFor($cartTotal, $userUsageCount = 0): bool
    {
        if (!$this->isGloballyValid()) return false;
        if ($cartTotal < $this->min_cart_value) return false;
        if ($userUsageCount >= $this->max_uses_per_user) return false;
        return true;
    }

    public function calculateDiscount($cartTotal): float
    {
        if (!$this->isGloballyValid()) return 0;

        if ($this->type === 'percent') {
            return ($this->value / 100) * $cartTotal;
        }

        return min($this->value, $cartTotal);
    }

    /**
     * Increment usage count after a successful order.
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
