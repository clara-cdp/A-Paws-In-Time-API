<?php

use App\Models\User;
use App\Models\Game;
use App\Models\Room;
use App\Models\Item;
use App\Models\Interaction;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class); 
    $this->user = User::factory()->create();
    Passport::actingAs($this->user);

    // Create a game 
    $this->introRoom = Room::where('name', 'Intro')->first();
    $this->game = Game::factory()->create([
        'user_id' => $this->user->id,
        'room_id' => $this->introRoom->id
    ]);
});

    // ---> Game Start
    it('starts the game in the correct room', function () 
    {

        $service = new \App\Providers\Game\GameStart;
        $game = $service->handle($this->user, 'Pawrio');

        expect($game->room_id)->toBe(Room::startingRoom()->id);
        expect($game->avatar)->toBe('Pawrio');
    });

    it('places cloned items in the correct rooms for a new game', function () 
    {
        $service = new \App\Providers\Game\GameStart();
        $game = $service->handle($this->user, 'Pawgi');

        $introRoomId = $game->room_id;
   
        $itemsInRoom = \App\Models\Item::where('game_id', $game->id)
        ->where('room_id', $introRoomId)
        ->get();

        expect($itemsInRoom)->not->toBeEmpty();

        $startItem = $itemsInRoom->where('name_id', 'start')->first();
        expect($startItem)->not->toBeNull();
        expect($startItem->game_id)->toBe($game->id);
    });

    
    it('adds the starting fish item in the pocket', function()
    {
        $service = new \App\Providers\Game\GameStart();
        $game = $service->handle($this->user, 'Pawdy');

        $playerFish = Item::where('game_id',$game->id)
            ->where('name_id', 'fish')
            ->first();

        expect($playerFish)->not->toBeNull();

        $this->assertDatabaseHas('pocket_items',[
            'pocket_id'=>$game->pocket->id,
            'item_id' => $playerFish->id
        ]);

        expect($playerFish->room_id)->toBeNull();
        expect($playerFish->is_visible)->toBeTruthy();
    });

// ---> ensure room and pocket are visible to the user 
//---> re do this test for a CONTINUED GAME
// --->ensure right VERBS are displayed (?)


    it('returns the list of available interaction verbs', function () {
        $response = $this->getJson('/api/metadata');

        $response->assertStatus(200)
            ->assertJsonFragment(['LOOK AT'])
            ->assertJsonFragment(['PICK UP'])
            ->assertJsonFragment(['GO TO']);
    });

   //['LOOK AT', 'USE', 'PICK UP','GO TO', 'OPEN','RESCUE','PULL','PUSH']);


// -->  interactions ------------------------------------------------------------------

// ---> 'empty' interactions  - not the right verb
// ---> see an item | LOOK AT
// ---> get an items |  PICK UP 
// ---> go to the next unlocked room |  GO TO
// --->  unlocking items with a verb
// --->  unlocking items with an item | USE
// --->  unlocking rooms with an item | USE
