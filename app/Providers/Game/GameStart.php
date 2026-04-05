<?php

namespace App\Providers\Game;

use Illuminate\Support\Facades\DB;

use App\Models\Game;
use App\Models\User;
use App\Models\Room;
use App\Models\Pocket;
use App\Models\Pocket_item;
use App\Models\Item;

class GameStart 
{
    public function handle(User $user, string $avatar): Game 
    {
        return DB::transaction(function () use ($user, $avatar) {

        $startingRoom = Room::where('name', "Intro")->firstOrFail();

        $game = Game::create([
            'user_id' => $user->id,
            'avatar'  => $avatar, 
            'room_id' => $startingRoom->id,
            'progress' => 0, 
        ]);
            $this->createGameWorld($game);
            $pocket = Pocket::create(['game_id' => $game->id]);
            $this->giveStarterItem($pocket, $game);

            return $game;
        });
    }

    protected function giveStarterItem(Pocket $pocket, Game $game): void
    {
        $masterStarter = Item::starter();

        $playerItem = Item::withoutGlobalScopes()
            ->where('game_id', $game->id) 
            ->where('css_id', $masterStarter->css_id)
            ->firstOrFail();

        Pocket_item::create([
            'pocket_id' => $pocket->id,
            'item_id'   => $playerItem->id
        ]);
    }

    protected function createGameWorld(Game $game): void
    {
        $masterItems = Item::withoutGlobalScopes()
            ->whereNull('game_id') 
            ->get();

        foreach ($masterItems as $item) {
            Item::create([
                'game_id'     => $game->id,
                'css_id'      => $item->css_id,
                'description' => $item->description,
                'image_url'   => $item->image_url,
                'is_portable' => $item->is_portable,
                'is_visible'  => $item->is_visible,
                'room_id'     => $item->room_id,
                'interaction_id'  => $item->interaction_id
            ]);
        }
    }
}