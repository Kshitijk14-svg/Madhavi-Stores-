<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\WishlistItem;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wishlistItems = WishlistItem::with('product')->where('user_id', $user->id)->get();
        
        // Count cart items to display count badge
        $cartCount = CartItem::where('user_id', $user->id)->sum('quantity');

        return view('pages.wishlist', compact('wishlistItems', 'cartCount'));
    }

    public function toggle(Request $request, $productId)
    {
        $user = Auth::user();
        
        $exists = WishlistItem::where('user_id', $user->id)
                             ->where('product_id', $productId)
                             ->first();

        if ($exists) {
            $exists->delete();
            $message = 'Product removed from your wishlist.';
            $status = 'info';
        } else {
            WishlistItem::create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
            $message = 'Product added to your wishlist.';
            $status = 'success';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $status
            ]);
        }

        return redirect()->back()->with($status, $message);
    }
}
