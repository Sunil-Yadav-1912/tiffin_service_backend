<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tiffin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'price', 'description', 'long_description', 'image',
        'meal_type', 'diet_type', 'category', 'items_included', 'is_available', 'featured'
    ];

    protected $casts = [
        'items_included' => 'array',
        'is_available' => 'boolean',
        'featured' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
