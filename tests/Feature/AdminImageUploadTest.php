<?php

namespace Tests\Feature;

use App\Models\Category;
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
}
