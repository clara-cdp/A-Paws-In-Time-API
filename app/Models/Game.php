<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;

    protected $fillable = [
        'avatar',
        'progress',
        'user_id',
        'room_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pocket()
    {
        return $this->hasOne(Pocket::class);
    }

    public function Records()
    {
        return $this->hasMany(Record::class);
    }
}
