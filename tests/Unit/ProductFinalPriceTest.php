<?php

namespace Tests\Unit;

use App\Models\Product;
use Tests\TestCase;

class ProductFinalPriceTest extends TestCase
{
    public function test_no_discount_returns_base_price(): void
    {
        $product = new Product(['price' => 1000]);
        $this->assertEquals(1000.0, $product->final_price);
    }

    public function test_percent_discount_is_applied(): void
    {
        $product = new Product(['price' => 1000, 'discount_type' => 'percent', 'discount_value' => 10]);
        $this->assertEquals(900.0, $product->final_price);
    }

    public function test_fixed_discount_is_applied(): void
    {
        $product = new Product(['price' => 1000, 'discount_type' => 'fixed', 'discount_value' => 250]);
        $this->assertEquals(750.0, $product->final_price);
    }

    public function test_fixed_discount_never_goes_below_zero(): void
    {
        $product = new Product(['price' => 100, 'discount_type' => 'fixed', 'discount_value' => 250]);
        $this->assertEquals(0.0, $product->final_price);
    }

    public function test_zero_discount_value_is_ignored(): void
    {
        $product = new Product(['price' => 1000, 'discount_type' => 'percent', 'discount_value' => 0]);
        $this->assertEquals(1000.0, $product->final_price);
    }
}
