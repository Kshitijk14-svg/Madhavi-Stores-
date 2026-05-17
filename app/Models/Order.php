<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'email', 'first_name', 'last_name', 
        'address', 'city', 'postal_code', 'payment_method', 
        'payment_status', 'order_status', 'subtotal', 'discount', 
        'tax', 'total', 'coupon_code'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
