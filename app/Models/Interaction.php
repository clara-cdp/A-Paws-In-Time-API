<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    protected $fillable = [
        'verb_trigger',
        'step_required',
        'next_step',
        'reward',
        'object_id',
        'required_object_id',
        'unlocked_object_id',
        'target_room_id',      
    ];
       
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function requiredItem()
    {
        return $this->belongsTo(Item::class, 'required_item_id');
    }
    /** @use HasFactory<\Database\Factories\InteractionFactory> */
    use HasFactory;
}
