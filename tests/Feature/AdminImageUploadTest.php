<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_oversized_image_dimensions_are_clamped_on_upload(): void
    {
        $admin = User::factory()->admin()->create();
        $name  = 'Resize Test ' . uniqid();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name'  => $name,
                'image' => UploadedFile::fake()->image('cover.jpg', 4000, 4000),
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::where('name', $name)->first();
        $this->assertNotNull($category);
        $this->assertNotNull($category->image_url, 'A processed image path should be stored.');

        $path = public_path(ltrim($category->image_url, '/'));
        $this->assertFileExists($path);

        [$w, $h] = getimagesize($path);
        $this->assertLessThanOrEqual(1600, max($w, $h), 'Stored image must be downscaled to the cap.');

        @unlink($path); // the file is written to the real public/ dir; clean it up
    }

    public function test_image_larger_than_5mb_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $name  = 'Too Big ' . uniqid();

        // A valid (small) image whose reported size exceeds the 5 MB validation cap.
        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name'  => $name,
                'image' => UploadedFile::fake()->image('big.jpg', 200, 200)->size(6000),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('categories', ['name' => $name]);
    }

    public function test_product_cover_generates_a_card_thumbnail(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $name     = 'Thumb Test ' . uniqid();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name'        => $name,
                'price'       => 999,
                'category_id' => $category->id,
                'image'       => UploadedFile::fake()->image('cover.jpg', 2000, 2500),
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', $name)->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image_url);

        $thumbRel = Product::thumbUrlFor($product->image_url);
        $this->assertNotNull($thumbRel);

        $thumbPath = public_path(ltrim($thumbRel, '/'));
        $this->assertFileExists($thumbPath, 'A card thumbnail should be generated.');

        [$w, $h] = getimagesize($thumbPath);
        $this->assertLessThanOrEqual(1000, max($w, $h));

        // The accessor used by product cards serves the thumbnail.
        $this->assertSame($thumbRel, $product->thumb_url);

        // The card srcset offers both the thumbnail and the full image with width descriptors.
        $srcset = $product->card_srcset;
        $this->assertNotNull($srcset);
        $this->assertStringContainsString($thumbRel . ' ', $srcset);
        $this->assertStringContainsString($product->image_url . ' ', $srcset);
        $this->assertStringContainsString('w', $srcset);

        @unlink(public_path(ltrim($product->image_url, '/')));
        @unlink($thumbPath);
    }
}
