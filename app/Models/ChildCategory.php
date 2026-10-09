<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Trackable;

class ChildCategory extends Model
{
    use HasFactory, Trackable;

    protected $table = 'child_categories';

    protected $fillable = ['category_id', 'sub_category_id', 'name', 'slug', 'status'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'child_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }
}
