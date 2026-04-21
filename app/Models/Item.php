<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; 
use Illuminate\Support\Facades\Auth;      

class Item extends Model
{
    /** @use HasFactory<\Database\Factories\ItemFactory> */
    use HasFactory;

    protected $fillable = [
        'name_id',    
        'description',
        'image_url',
        'is_portable',
        'is_visible',
        'room_id',
        'game_id',
    ];

    
    public static function starter(): self
    {
        return self::withoutGlobalScopes()
            ->where('name_id', 'fish')
            ->whereNull('game_id')
            ->firstOrFail();
    }

    public function scopeStarter($query)
    {
        return $query->where('name_id', 'fish')->whereNull('game_id')->first();
    }

    public function pockets()
    {
        return $this->belongsToMany(Pocket::class, 'pocket_items');
    } 
    
    
/*
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
