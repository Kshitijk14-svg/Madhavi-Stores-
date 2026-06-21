<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id'    => null,
            'name'           => ucwords($name),
            'slug'           => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 999999),
            'price'          => fake()->numberBetween(500, 5000),
            'original_price' => null,
            'stock'          => 100,
            'has_sizes'      => false,
            'image_url'      => 'images/products/sample.webp',
            'is_bestseller'  => false,
            'is_new_arrival' => false,
        ];
    }

    /** A product with a percentage discount. */
    public function percentDiscount(float $value): static
    {
        return $this->state(fn () => ['discount_type' => 'percent', 'discount_value' => $value]);
    }

    /** A product with a fixed-amount discount. */
    public function fixedDiscount(float $value): static
    {
        return $this->state(fn () => ['discount_type' => 'fixed', 'discount_value' => $value]);
    }

    /** A product whose stock is tracked per size. */
    public function withSizes(): static
    {
        return $this->state(fn () => ['has_sizes' => true, 'stock' => 0]);
    }
}
