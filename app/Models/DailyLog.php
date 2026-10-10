<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'tiffin_id', 'date', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tiffin()
    {
        return $this->belongsTo(Tiffin::class);
    }
}
