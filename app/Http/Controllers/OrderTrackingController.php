<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(Request $request)
    {
        $order = null;
        $lookup = session()->pull('track_order_lookup');
        $orderNumber = $lookup['order_number'] ?? '';
        $email = $lookup['email'] ?? '';
        $searched = $lookup !== null;

        if ($searched) {
            $order = Order::with('items.product')
                ->where('order_number', trim($orderNumber))
                ->where('email', strtolower(trim($email)))
                ->first();
        }

        return view('pages.track-order', [
            'order'       => $order,
            'orderNumber' => $orderNumber,
            'email'       => $email,
            'searched'    => $searched,
        ]);
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string|max:50',
            'email'        => 'required|email|max:255',
        ]);

        session(['track_order_lookup' => [
            'order_number' => $request->order_number,
            'email'        => $request->email,
        ]]);

        return redirect()->route('track-order');
    }
}
