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

    $service = new \App\Providers\Game\GameStart();
    $this->game = $service->handle($this->user, 'Pawrio');
    $this->introRoom = Room::where('name', 'Intro')->first();
    
});

    // ---> Game Start
    it('starts the game in the correct room', function () {
        $service = new \App\Providers\Game\GameStart;
        $game = $service->handle($this->user, 'Pawrio');

        expect($game->room_id)->toBe(Room::startingRoom()->id);
        expect($game->avatar)->toBe('Pawrio');
    });

    it('places the items in the correct rooms for a new game', function () {
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
 
    it('adds the starting fish item in the pocket', function(){
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

    //---> continue game
    it('includes the items currently in the pocket in the continue game', function () {
        $service = new \App\Providers\Game\GameStart();
        $game = $service->handle($this->user, 'Amélie Pwalain');

        $response = $this->getJson("/api/games/{$game->id}");
        $response->assertStatus(200);
        $inventory = $response->json('inventory');

        expect($inventory)->toBeArray();
        expect(collect($inventory)->pluck('name_id'))->toContain('fish');
    });

    it('get the right room and pocket for a continued game', function() {
        $service = new \App\Providers\Game\GameStart;
        $game = $service->handle($this->user, 'Catniss Aberdeen');

        $oldLibrary = Room::where('name', "Old Library")->first();
        $game->update(['room_id' =>$oldLibrary->id]);

        $response = $this->getJson("api/games/{$game->id}");

        $response->assertStatus(200)
            ->assertjsonPath('room_id', $oldLibrary->id)
            ->assertJsonStructure([

                    'id',
                    'avatar',
                    'room_id',
                    'story_step',
                    'inventory' 
                ]);
        $response->assertJsonFragment(['name_id' => 'fish']);
    });
        
    // --->ensure right VERBS are displayed
    it('returns the list of available interaction verbs', function () {
            $response = $this->getJson('/api/metadata');

            $response->assertStatus(200)
                ->assertJsonFragment(['LOOK AT'])
                ->assertJsonFragment(['PICK UP'])
                ->assertJsonFragment(['GO TO']);
    });
  
    // ---> 'empty' interactions - not the right verb 
    it('returns a default message when a verb has no defined interaction with an specific item', function () {
        $startItem = Item::where('game_id', $this->game->id)
            ->where('name_id', 'start')
            ->firstOrFail();

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'PUSH',
            'target_id' => $startItem->id
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', "That doesn't seem to do anything...");
    });

    // edge case
    it('prevents interacting with items that are not in the current room', function () {
        $gardenItem = Item::where('game_id', $this->game->id)
            ->where('name_id', 'tree') 
            ->firstOrFail();

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'PUSH',
            'target_id' => $gardenItem->id
        ]);

        $response->assertStatus(403);
    });

    // ---> check an item | LOOK AT
    it('returns the item description when using LOOK AT on a room item', function () {
        $blinkItem = Item::where('game_id', $this->game->id)
            ->where('name_id', 'blink') 
            ->firstOrFail();

        $this->game->update(['room_id' => $blinkItem->room_id]);

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'LOOK AT',
            'target_id' => $blinkItem->id
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', $blinkItem->description);
    });

    it('returns the item description when using LOOK AT on a pocket item', function () {
        $fish = Item::where('game_id', $this->game->id)
            ->where('name_id', 'fish')
            ->firstOrFail();

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'LOOK AT',
            'target_id' => $fish->id
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', $fish->description);
    });

    // ---> get an items |  PICK UP 
    it('allows picking up a portable item from the room', function () {
        
        $garden = Room::where('name', "Mansion's Garden")->firstOrFail();
        $this->game->update(['room_id' => $garden->id]);

        $crowbar = Item::where('game_id', $this->game->id)
            ->where('name_id', 'crowbar')
            ->firstOrFail();

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'PICK UP',
            'target_id' => $crowbar->id
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', "Picked up the crowbar");

        $crowbar->refresh();
        expect($crowbar->room_id)->toBeNull();

        $this->assertDatabaseHas('pocket_items', [
            'pocket_id' => $this->game->pocket->id,
            'item_id'   => $crowbar->id
        ]);
    });

    it('prevents picking up non-portable items', function () {
        $tree = Item::where('game_id', $this->game->id)
            ->where('name_id', 'tree')
            ->firstOrFail();

        $this->game->update(['room_id' => $tree->room_id]);

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'PICK UP',
            'target_id' => $tree->id
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', "I can't pick that up.");
    });

    // ---> go to the next unlocked room |  GO TO
    it('moves the player to the the target room when using GO TO', function () {
        $startItem = Item::where('game_id', $this->game->id)
            ->where('name_id', 'start')
            ->firstOrFail();

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'GO TO',
            'target_id' => $startItem->id
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', "You step through the doorway. Here we Go!");

        $this->game->refresh();
        $garden = Room::where('name', "Mansion's Garden")->firstOrFail();

        expect($this->game->room_id)->toBe($garden->id);
        expect($this->game->progress)->toBe(1); 
    });

    // edege cases
    it('fails when using an item that is not in the inventory', function () {
        $dog = Item::where('game_id', $this->game->id)->where('name_id', 'dog')->firstOrFail();

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'USE',
            'target_id' => $dog->id,
            'item_id' => 999
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', "You don't have that item.");
    });

    it('returns a failure message when the target item ID does not exist', function () {
        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'LOOK AT',
            'target_id' => 9999 
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', "I don't see anything...");
    });

    // --->  unlocking items with verb 
    it('unlocks an item when using the right verb_trigger on targeted item', function () {

        $garden = Room::where('name', "Mansion's Garden")->firstOrFail();
        $this->game->update(['room_id' => $garden->id]);

        $ball = Item::where('game_id', $this->game->id)
            ->where('name_id', 'tennis ball')
            ->firstOrFail();
        expect($ball->is_visible)->toBeFalsy();

        $tree = Item::where('game_id', $this->game->id)
            ->where('name_id', 'tree')
            ->firstOrFail();

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'PUSH',
            'target_id' => $tree->id
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', "If I only had time to play now...");

        $ball->refresh();
        expect($ball->is_visible)->toBeTruthy();
    });

    // --->  unlocking items with an item | USE 
    it('unlocks a new item on the room when USE and a required_item on a targeted_item', function () {
        $library = Room::where('name', "Old Library")->firstOrFail();
        $this->game->update(['room_id' => $library->id]);

        $magnet = Item::where('game_id', $this->game->id)->where('name_id', 'magnet')->firstOrFail();
        $this->game->pocket->items()->syncWithoutDetaching([$magnet->id]);

        $yarn = Item::where('game_id', $this->game->id)->where('name_id', 'yarn')->firstOrFail();
        $rod = Item::where('game_id', $this->game->id)->where('name_id', 'rod')->firstOrFail();

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'USE',
            'target_id' => $yarn->id,
            'item_id' => $magnet->id
        ]);

        $this->assertDatabaseMissing('pocket_items', [
            'item_id' => $magnet->id
        ]);

        $rod->refresh();
        expect($rod->is_visible)->toBeTruthy();
        expect($rod->room_id)->toBe($library->id);

        $this->assertDatabaseMissing('pocket_items', [
            'item_id' => $rod->id
        ]);

        $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'PICK UP',
            'target_id' => $rod->id
        ])->assertStatus(200);

        $this->assertDatabaseHas('pocket_items', [
            'pocket_id' => $this->game->pocket->id,
            'item_id'   => $rod->id
        ]);
    });

     // --->  unlocking rooms with an item 
    it('allows the player to switch rooms when using a pocket_item to a targeted_item', function () {
        $modernLibrary = Room::where('name', "A Library")->firstOrFail();
        $this->game->update([
            'room_id' => $modernLibrary->id,
            'progress' => 3
        ]);

        $cuckoo = Item::where('game_id', $this->game->id)
            ->where('name_id', 'cuckoo')
            ->firstOrFail();

        $cuckoo->update(['room_id' => null]);

        $this->game->pocket->items()->syncWithoutDetaching([$cuckoo->id]);

        $clock = Item::where('game_id', $this->game->id)
            ->where('name_id', "grandfather's clock")
            ->firstOrFail();
        $pastLibrary = Room::where('name', "Old Library")->firstOrFail();

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'USE',
            'target_id' => $clock->id,
            'item_id' => $cuckoo->id
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', "You step through the doorway. WOOO!!");

        $this->game->refresh();
        expect($this->game->room_id)->toBe($pastLibrary->id);
        expect($this->game->progress)->toBe(4);

        $this->assertDatabaseMissing('pocket_items', [
            'item_id' => $cuckoo->id
        ]);
    });

    // ---> progress
    it('prevents trying an interactiong if the step_required is missing', function () {
        $door = Item::where('game_id', $this->game->id)->where('name_id', "mansion's door")->firstOrFail();
        $key = Item::where('game_id', $this->game->id)->where('name_id', 'key')->firstOrFail();

        $this->game->update([
            'room_id' => $door->room_id,
            'progress' => 1
        ]);
        $this->game->pocket->items()->syncWithoutDetaching([$key->id]);

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'USE',
            'target_id' => $door->id,
            'item_id' => $key->id
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', "I'll try again later");

        expect($this->game->refresh()->progress)->toBe(1);
    });

    it('updates progress when a puzzle is solved', function () {
        $dog = Item::where('game_id', $this->game->id)->where('name_id', 'dog')->firstOrFail();
        $ball = Item::where('game_id', $this->game->id)->where('name_id', 'tennis ball')->firstOrFail();

        $this->game->update([
            'room_id' => $dog->room_id,
            'progress' => 1
        ]);
        $this->game->pocket->items()->syncWithoutDetaching([$ball->id]);

        $response = $this->postJson("/api/games/{$this->game->id}/actions", [
            'verb' => 'USE',
            'target_id' => $dog->id,
            'item_id' => $ball->id
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', "Go play! GOOD BOY!! ");

        $this->game->refresh();
        expect($this->game->progress)->toBe(2); 
    });
    
    