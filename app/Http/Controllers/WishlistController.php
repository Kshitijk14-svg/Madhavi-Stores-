<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\ResolvesCartOwner;
use App\Models\Product;
use App\Models\WishlistItem;
use App\Models\CartItem;

class WishlistController extends Controller
{
    use ResolvesCartOwner;

    public function index(Request $request)
    {
        $owner = $this->resolveOwner($request);

        $wishlistItems = $owner->isEmpty()
            ? collect()
            : $owner->scope(WishlistItem::with('product'))->get();

        // Count cart items to display count badge
        $cartCount = $owner->isEmpty() ? 0 : $owner->scope(CartItem::query())->sum('quantity');

        return view('pages.wishlist', compact('wishlistItems', 'cartCount'));
    }

    public function toggle(Request $request, $productId)
    {
        // The {product} route param is a raw id (no model binding), so validate it
        // exists before inserting — otherwise a bad/deleted id creates an orphan row.
        \Illuminate\Support\Facades\Validator::make(
            ['product_id' => $productId],
            ['product_id' => 'required|exists:products,id']
        )->validate();

        $owner = $this->resolveOwner($request, creating: true);

        $exists = $owner->scope(WishlistItem::query())
                             ->where('product_id', $productId)
                             ->first();

        if ($exists) {
            $exists->delete();
            $message = 'Product removed from your wishlist.';
            $status = 'info';
        } else {
            WishlistItem::create([
                ...$owner->attributes(),
                'product_id' => $productId,
            ]);
            $message = 'Product added to your wishlist.';
            $status = 'success';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $status,
                'added' => !$exists
            ]);
        }

        return redirect()->back()->with($status, $message);
    }
}
