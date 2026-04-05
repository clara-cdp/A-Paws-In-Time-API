<?php

namespace App\Providers\Game;

use App\Enums\Verb;
use App\Models\Item;
use App\Models\Interaction;
use App\Models\Game;

use App\Providers\Game\GameState;

class GameEngine 
{
    public function resolve(GameState $state, Game $game): ?string
    {
        /** @var \App\Enums\Verb $verb */
        $verb = $state->verb; 

        $targetItem = Item::where('game_id', $game->id)
        ->find($state->targetItemId);

        $pocketItem = $state->itemId ? Item::where('game_id', $game->id)->find($state->itemId):null;

        if (!$targetItem) return "I don't see anything..."; 

        if($verb === Verb::LOOK_AT) return $targetItem->description;

        if($verb === Verb::PICK_UP){
            if($targetItem->is_portable && $targetItem->is_visible){
                $targetItem->update(['room_id'=>null]);
                $game->pocket->items()->syncWithoutDetaching([$targetItem->id]);
            
                return "Picked up the " . $targetItem->css_id;
            } else return "I can't pick that up.";

        }
        return $this->processInteraction($verb, $targetItem, $pocketItem, $game);
    }

    private function processInteraction(Verb $verb, Item $targetItem, ?Item $pocketItem, Game $game): ?string
    {
        $masterTarget = Item::withoutGlobalScopes()
            ->whereNull('game_id')
            ->where('css_id', $targetItem->css_id)
            ->first();

        $masterPocketId = $pocketItem ? Item::withoutGlobalScopes()
            ->whereNull('game_id')
            ->where('css_id', $pocketItem->css_id)
            ->value('id') : null;

        $interaction = Interaction::where('verb_trigger', $verb->value)
            ->where('item_id', $masterTarget->id)
            ->where('required_item_id', $masterPocketId)
            ->first();

        if (!$interaction) return "That doesn't to do anything...";

        // check event - interactions 
        
        if ($interaction->next_step > $interaction->step_required && $game->progress >= $interaction->next_step) {
            return "I've already done that!";
        }
       
        if ($game->Records()->where('interaction_id', $interaction->id)->exists()) {
            return "I've already done that!";
        }

        // check progress
        if ($game->progress < $interaction->step_required) {
            return "I'll try again later";
        }

        $messages = [];
        
        // -- Room Transition --
        if ($interaction->target_room_id) {
            $game->update(['room_id' => $interaction->target_room_id]);
            $messages[] = "You step through the doorway.";  
        }

        // -- log as completed
        $game->Records()->firstOrCreate(['interaction_id' => $interaction->id]);

        // -- update progress
        if ($interaction->next_step > 0 && $interaction->next_step > $game->progress) {
            $game->update(['progress' => $interaction->next_step]);
        }

        // -- remove always pocket items
        if ($pocketItem) {
            $pocketItem->update(['is_visible'=>false]);
        }

        // -- unlocking items  
        if ($interaction->unlocked_item_id) {
            $masterUnlock = Item::withoutGlobalScopes()->find($interaction->unlocked_item_id);
  
            $gameItem = Item::where('game_id', $game->id)
                ->where('css_id', $masterUnlock->css_id)
                ->first();

            if ($gameItem) {
                $gameItem->update(['is_visible' => true]);

                if (is_null($gameItem->room_id)) {
                    $game->pocket->items()->syncWithoutDetaching([$gameItem->id]);
                }
            }
            $messages[] = $interaction->reward;
        }

        return !empty($messages) ? implode(' ', $messages) : "You did something!";
    }
}




