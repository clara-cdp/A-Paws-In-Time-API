<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /** @use HasFactory<\Database\Factories\ItemFactory> */
    use HasFactory;

    protected $fillable = [
        'css_id',    
        'description',
        'image_url',
        'is_portable',
        'is_visible',
        'room_id',
        'interaction_id',
        'game_id',
    ];

    /*
    public static function starter(): self
    {
        return self::withoutGlobalScopes()
            ->where('css_id', 'fish')
            ->whereNull('game_id')
            ->firstOrFail();
    }

    public function pockets()
    {
        return $this->belongsToMany(Pocket::class, 'pocket_items');
    }

    protected static function booted()
    {
        static::addGlobalScope('game', function (Builder $builder) {

            if (Auth::check()) {
                $gameId = Auth::user()->game?->id;

                if ($gameId) {
                    $builder->where('game_id', $gameId);
                }
            }
        });
    }*/
}
