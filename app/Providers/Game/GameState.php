<?php

namespace App\Providers\Game;

use App\Enums\Verb;

class GameState
{
    public ?Verb $verb = null;
    public ?int $itemId = null;
    public ?int $targetItemId = null;

    public function __construct(?Verb $verb = null, ?int $targetItemId = null, ?int $itemId = null)
    {
        $this->verb = $verb;
        $this->targetItemId = $targetItemId;
        $this->itemId = $itemId;
    }

    public function checkCompletedActions():bool
    {
        //get description
        if($this->verb === Verb::LOOK_AT && $this->targetItemId) return true;

        //unlocking with USE
        if($this->verb === Verb::USE && $this->itemId && $this->targetItemId) return true;

        //others
        return filled($this->verb)&&filled($this->targetItemId);
    }
}
