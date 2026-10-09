<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Trackable;

class SubCategory extends Model
{
    use HasFactory, Trackable;

    protected $table = 'sub_categories';

    protected $fillable = ['category_id', 'name', 'slug', 'image', 'status'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function childCategories()
    {
        return $this->hasMany(ChildCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'sub_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }

    public function getImageUrlAttribute()
    {
        $img = trim((string) $this->image);

        if (!empty($img)) {
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
