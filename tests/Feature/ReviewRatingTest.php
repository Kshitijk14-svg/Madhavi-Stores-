<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_rating_and_count_recompute_on_save(): void
    {
        $product = Product::factory()->create();
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        Review::create(['user_id' => $u1->id, 'product_id' => $product->id, 'rating' => 4]);
        Review::create(['user_id' => $u2->id, 'product_id' => $product->id, 'rating' => 2]);

        $product->refresh();
        $this->assertEquals(2, $product->review_count);
        $this->assertEquals(3.0, $product->rating);
    }

    public function test_rating_recomputes_on_delete(): void
    {
        $product = Product::factory()->create();
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $r1 = Review::create(['user_id' => $u1->id, 'product_id' => $product->id, 'rating' => 4]);
        Review::create(['user_id' => $u2->id, 'product_id' => $product->id, 'rating' => 2]);

        $r1->delete();

        $product->refresh();
        $this->assertEquals(1, $product->review_count, 'Count must drop when a review is deleted.');
        $this->assertEquals(2.0, $product->rating);
    }
}
