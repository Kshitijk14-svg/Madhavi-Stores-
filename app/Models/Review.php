<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment',
    ];

    /**
     * Keep the denormalised products.rating / products.review_count in sync.
     * Recompute on every create/update AND delete (the delete case is what was
     * previously missing, causing permanent drift when a review/user was removed).
     */
    protected static function booted(): void
    {
        static::saved(fn (Review $review) => self::recountProduct($review->product_id));
        static::deleted(fn (Review $review) => self::recountProduct($review->product_id));
    }

    public static function recountProduct($productId): void
    {
        if (!$productId) {
            return;
        }

        $agg = self::where('product_id', $productId)
            ->selectRaw('COUNT(*) as cnt, COALESCE(AVG(rating), 0) as avg_rating')
            ->first();

        // Use the query builder (not a guarded mass-assign) so these derived
        // columns can stay out of Product::$fillable.
        Product::where('id', $productId)->update([
            'review_count' => (int) $agg->cnt,
            'rating'       => round((float) $agg->avg_rating, 1),
        ]);
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
