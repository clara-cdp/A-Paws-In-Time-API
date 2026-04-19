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

        $targetItem = Item::where('game_id', $game->id)->find($state->targetItemId);
        if (!$targetItem) return "I don't see anything...";

        if ($state->itemId) {
            $hasItem = $game->pocket->items()->where('items.id', $state->itemId)->exists();
            if (!$hasItem) {
                abort(422, "You don't have that item."); 
            }
        }

        $isInRoom = $targetItem->room_id === $game->room_id;
        $isInPocket = $game->pocket->items()->where('items.id', $targetItem->id)->exists();

        if (!$isInRoom && !$isInPocket) {
            abort(403, "That is too far away.");
        }

        $pocketItem = $state->itemId ? Item::where('game_id', $game->id)->find($state->itemId) : null;

        if($verb === Verb::LOOK_AT) return $targetItem->description;

        if($verb === Verb::PICK_UP){
            if($targetItem->is_portable && $targetItem->is_visible){
                $targetItem->update(['room_id'=>null]);
                $game->pocket->items()->syncWithoutDetaching([$targetItem->id]);         
                return "Picked up the " . $targetItem->name_id;
            }
            return "I can't pick that up.";
        }
        return $this->processInteraction($verb, $targetItem, $pocketItem, $game);
    }

    private function processInteraction(Verb $verb, Item $targetItem, ?Item $pocketItem, Game $game): ?string
    {
        $masterTarget = Item::withoutGlobalScopes()
            ->whereNull('game_id')
            ->where('name_id', $targetItem->name_id)
            ->first();

        $masterPocketId = $pocketItem ? Item::withoutGlobalScopes()
            ->whereNull('game_id')
            ->where('name_id', $pocketItem->name_id)
            ->value('id') : null;

        $interaction = Interaction::where('verb_trigger', $verb->value)
            ->where('item_id', $masterTarget->id)
            ->where('required_item_id', $masterPocketId)
            ->first();

        if (!$interaction) return "That doesn't seem to do anything...";

        //allows to back and forward
        $isRepeatableTravel =$interaction->verb_trigger === Verb::GO_TO->value &&
            !is_null($interaction->target_room_id) &&
            (int) $interaction->next_step === 0;

        // check event - interactions 
        if ($interaction->next_step > $interaction->step_required && $game->progress >= $interaction->next_step) {
            return "I've already done that!";
        }

        if (!$isRepeatableTravel && $game->Records()->where('interaction_id', $interaction->id)->exists()) {
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
            $game->pocket->items()->detach($pocketItem->id);
            $pocketItem->update(['is_visible' => false, 'room_id' => null]);
        }

        // -- unlocking items  
        if ($interaction->unlocked_item_id) {
            $masterUnlock = Item::withoutGlobalScopes()->find($interaction->unlocked_item_id);
            $gameItem = Item::where('game_id', $game->id)
                ->where('name_id', $masterUnlock->name_id)
                ->first();

            if ($gameItem) {
                $gameItem->update(['is_visible' => true]);

                if (is_null($gameItem->room_id)) {
                    $game->pocket->items()->syncWithoutDetaching([$gameItem->id]);
                } 
            }
        }

        if ($interaction->reward) {
            $messages[] = $interaction->reward;
        }

        return !empty($messages) ? implode(' ', $messages) : "You did something!";
    }
}




