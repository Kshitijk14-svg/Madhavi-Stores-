<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'email', 'phone', 'first_name', 'last_name',
        'address', 'city', 'postal_code', 'payment_method',
        'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature',
        'payment_status', 'order_status', 'subtotal', 'discount',
        'tax', 'total', 'coupon_code', 'coupon_id'
    ];

    /** Normalize on write so email matching (guest coupon history, account claims) is never case/whitespace-sensitive. */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? strtolower(trim($value)) : $value,
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
