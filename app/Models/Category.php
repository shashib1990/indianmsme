<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Trackable;

class Category extends Model
{
    use HasFactory, Trackable;

    protected $table = 'categories';

    protected $fillable = ['name', 'slug', 'image', 'status'];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function childCategories()
    {
        return $this->hasMany(ChildCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }

    public function getImageUrlAttribute()
    {
        $img = trim((string) $this->image);

        if (!empty($img)) {
            // Strip any accidental line breaks or carriage returns from URL strings
            $img = str_replace(["\r", "\n", " "], '', $img);
            
            if (filter_var($img, FILTER_VALIDATE_URL)) {
                return $img;
            }

            if (file_exists(public_path($img))) {
                return asset($img);
            }

            if (file_exists(public_path('storage/' . $img))) {
                return asset('storage/' . $img);
            }
        }

        return asset('admin-assets/images/IndianMSME.png');
    }
}
