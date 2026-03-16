<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TickerAlert extends Model
{
    protected $fillable = ['message', 'theme_color', 'is_active'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
