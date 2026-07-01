<?php

namespace App\Models;

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
