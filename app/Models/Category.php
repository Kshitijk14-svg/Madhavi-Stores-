<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'image_url', 'size_chart_image'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
