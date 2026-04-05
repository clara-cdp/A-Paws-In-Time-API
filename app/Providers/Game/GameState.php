<?php

namespace App\Providers\Game;

use App\Enums\Verb;

class GameState
{
    public string|null $verb = null;
    public int|null $itemId = null;
    public int|null  $targetItemId = null;


   public function __construct(?string $verb=null, ?int $itemId = null, ?int $targetItemId = null, )
   {
        $this->verb = $verb ? verb::tryFrom($verb) : null;
        $this->itemId = $itemId;
        $this->targetItemId = $targetItemId;
    }

    public function checkCompletedActions():bool
    {
        //get dialog
        if($this->verb === Verb::LOOK_AT && $this->targetItemId) return true;

        //unlocking with USE
        if($this->verb === Verb::USE && $this->itemId && $this->targetItemId) return true;

        //others
        return filled($this->verb)&&filled($this->targetItemId);
    }
}
