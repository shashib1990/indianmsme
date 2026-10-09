<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Trackable;

class Brand extends Model
{
    use HasFactory, Trackable;

    protected $table = 'brands';

    protected $fillable = ['name', 'slug', 'logo', 'status', 'created_by', 'updated_by'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->slug) && !empty($model->name)) {
                $baseSlug = \Illuminate\Support\Str::slug($model->name);
                $slug = $baseSlug ?: 'brand-' . \Illuminate\Support\Str::random(5);
                $count = static::where('slug', 'like', "{$slug}%")->where('id', '!=', $model->id ?? 0)->count();
                $model->slug = $count > 0 ? "{$slug}-{$count}" : $slug;
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['1', 'Active']);
    }

    public function getLogoUrlAttribute()
    {
        $logo = trim((string) $this->logo);

        if (!empty($logo)) {
            $logo = str_replace(["\r", "\n", " "], '', $logo);

            if (filter_var($logo, FILTER_VALIDATE_URL)) {
                return $logo;
            }

            if (file_exists(public_path($logo))) {
                return asset($logo);
            }

            if (file_exists(public_path('storage/' . $logo))) {
                return asset('storage/' . $logo);
            }
        }

        return null;
    }
}
