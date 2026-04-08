<?php

use App\Models\User;
use App\Models\Game;
use App\Models\Room;
use App\Models\Item;
use App\Models\Pocket;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {    
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create();
    Passport::actingAs($this->user);
});

    // ---> GET | index ---------------------------------------------------------------
    it('shows all user games', function () {
        Game::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/games');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    });

    it('only shows the games belonging to the user', function () {
        
        $otherUser = User::factory()->create();
        Game::factory()->create(['user_id' => $otherUser->id, 'avatar' => 'pawstranger']);

        Game::factory()->create(['user_id' => $this->user->id, 'avatar' => 'MyPawsHero']);

        $response = $this->getJson('/api/games');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.avatar', 'MyPawsHero');
    });

    it('shows an empty list for a new user with no games', function () {
    
        $response = $this->getJson('/api/games');

        $response->assertStatus(200)
            ->assertJsonCount(0); 
    });

    // ---> POST | store ---------------------------------------------------------------
    it('can start a new game world from the seeder templates', function () {
        
        $response = $this->postJson('/api/games', [
            'avatar' => 'Pawsito'
        ]);

        $response->assertStatus(201)
        ->assertJsonPath('game.avatar', 'Pawsito');

        $gameId = $response->json('game.id');
        $this->assertDatabaseHas('games', ['id' => $gameId, 'user_id' => $this->user->id]);
    });


    it('fails to create a game with an invalid name', function()
    {
        $response = $this->postJson('api/games', [
            'avatar'=>'x'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['avatar']);
    });

    it('prevents a user from having more than 3 games', function () {
    
        Game::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/games', [
            'avatar' => 'TooManyPaws'
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'You have reached the maximum number of save slots (3).');
    });

    // ---> DELETE | destroy --------------------------------------------------
    it('can delete a game', function () {
        $game = Game::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/games/{$game->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    });

    it('deletes all associated items when a game is destroyed', function () {

        $game = Game::factory()->create(['user_id' => $this->user->id]);
        $gameId = $game->id;

        Item::factory()->create(['game_id' => $gameId]);

        $this->deleteJson("/api/games/{$gameId}");
        $this->assertDatabaseMissing('items', ['game_id' => $gameId]);
    });

    it('prevents a user from deleting someone else\'s game', function () {
    
        $victim = User::factory()->create();
        $victimsGame = Game::factory()->create(['user_id' => $victim->id, 'avatar' => 'Victim']);

        $response = $this->deleteJson("/api/games/{$victimsGame->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('games', ['id' => $victimsGame->id]);
    });

    it('deletes the pocket when the game is destroyed', function () {
        $game = \App\Models\Game::factory()->create(['user_id' => $this->user->id]);
        $pocketId = $game->pocket->id;

        $this->deleteJson("/api/games/{$game->id}")->assertStatus(200);

        $this->assertDatabaseMissing('pockets', ['id' => $pocketId]);
    });


    // --> GET | show() ---------------------------------------------------
    it('returns the correct game state for a continued game', function () {   
        $introRoom = Room::where('name', 'Intro')->first();

        $game = Game::factory()->create([
            'user_id' => $this->user->id,
            'avatar' => 'Pawsome',
            'room_id' => $introRoom->id,
            'progress' => 2
        ]);

        $response = $this->getJson("/api/games/{$game->id}");

        $response->assertStatus(200)
        ->assertJsonPath('game.avatar', 'Pawsome');
    });

    it('can fetch a specific game with inventory', function () {

        $game = Game::factory()->create(['user_id' => $this->user->id]);
        $item = Item::factory()->create([
            'game_id' => $game->id,
            'name_id' => 'fish',
            'room_id' => null 
        ]);

        $game->pocket->items()->attach($item->id);
        $response = $this->getJson("/api/games/{$game->id}");

        $response->assertStatus(200)
            ->assertJsonPath('game.avatar', $game->avatar)
            ->assertJsonPath('game.pocket.0.name_id', 'fish');
    });

    it('returns a 404 when fetching a game that does not exist', function () {
        $this->getJson("/api/games/9999")
            ->assertStatus(404);
    });

   

    // --> PUT | update()

    it('updates the game avatar name', function () {
        $game = Game::factory()->create(['user_id' => $this->user->id, 'avatar' => 'OldPaws']);

        $response = $this->putJson("/api/games/{$game->id}", [
            'avatar' => 'NewPaws'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'avatar' => 'NewPaws'
        ]);
    });

    it('prevents a user from updating someone else\'s game avatar', function () {
        $otherUser = \App\Models\User::factory()->create();
        $otherGame = \App\Models\Game::factory()->create(['user_id' => $otherUser->id, 'avatar' => 'OriginalPaw']);

        $response = $this->putJson("/api/games/{$otherGame->id}", [
            'avatar' => 'Hackedpaw'
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('games', [
            'id' => $otherGame->id,
            'avatar' => 'OriginalPaw'
        ]);
    });

    it('fails to update avatar with an invalid name', function () {
        $game = \App\Models\Game::factory()->create(['user_id' => $this->user->id]);


        $this->putJson("/api/games/{$game->id}", [
            'avatar' => 'pawsypawpawpawpawpawpawpawpawpawpawpawpawpawpawpawpawpawpawpawpawpawpaw',
        ])->assertStatus(422);
    });

