<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait Trackable
{
    public static function bootTrackable()
    {
        static::creating(function ($model) {
            if (!$model->created_by && Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (!$model->updated_by && Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if (Auth::check() && method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'updated_by');
    }
}
