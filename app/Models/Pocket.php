<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pocket extends Model
{
    protected $fillable = ['player_id'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'pocket_items');
    }

    /** @use HasFactory<\Database\Factories\PocketFactory> */
    use HasFactory;
}
