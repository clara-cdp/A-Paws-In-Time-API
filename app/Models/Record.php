<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    /** @use HasFactory<\Database\Factories\RecordFactory> */
    use HasFactory;

    protected $fillable = [
        'game_id',
        'interaction_id'
    ];

    public function interaction()
    {
        return $this->belongsTo(Interaction::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
