<?php

use App\Models\User;
use App\Models\Game;
use App\Models\Room;
use App\Models\Item;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {

    $room = Room::firstOrCreate(['name' => 'Intro'], [
        'description' => 'The start.',
        'image_url' => 'intro.png'
    ]);


    Item::firstOrCreate(['css_id' => 'fish', 'game_id' => null], [
        'description' => 'A red hering.',
        'is_portable' => true,
        'is_visible' => true,
        'room_id' => $room->null
    ]);
    
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create();
    Passport::actingAs($this->user);
});

// ---> GET | index
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


// ---> POST | store
it('can start a new game world from the seeder templates', function () {
    
$this->withoutExceptionHandling(); 

    $response = $this->postJson('/api/games', [
        'avatar' => 'Pawsito'
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('avatar', 'Pawsito');

    $gameId = $response->json('id');

    $this->assertDatabaseHas('games', ['id' => $gameId, 'user_id' => $this->user->id]);

    $this->assertDatabaseHas('items', [
        'game_id' => $gameId,
        'css_id' => 'fish'
    ]);
});

it('fails to create a game with an invalid name', function()
{
    $response = $this->postJson('api/games', [
        'avatar'=>'x'
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
});

// ---> DELETE | destroy
it('can delete a game', function () {
    $game = Game::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/games/{$game->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('games', ['id' => $game->id]);
});

it('deletes all associated items when a game is destroyed', function () {
    
    $response = $this->postJson('/api/games', ['avatar' => 'Copitina']);
    $gameId = $response->json('id');

    $this->assertDatabaseHas('items', ['game_id' => $gameId]);

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