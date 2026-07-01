<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    protected $fillable = [
        'user_id', 'guest_token', 'product_id'
    ];

    protected static function booted(): void
    {
        static::creating(function (self $item) {
            if (!$item->user_id && !$item->guest_token) {
                throw new \RuntimeException('WishlistItem requires either a user_id or a guest_token.');
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
