<?php

use App\Models\User;
use App\models\Game;
use Laravel\Passport\Passport;

    // ---> test GET 
    it('can view users own profile', function () {

        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
                ->assertJsonPath('data.email',$user->email);
    });

    // ---> test PUT
    it('can update users own profile', function() {

        $user = User::factory()->create(['name' => 'Old Name']);
        Passport::actingAs($user);

        $response = $this->putJson('/api/me', [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    });

    it('fails if a user tries to update to an email already taken by someone else', function () {
        $userA = User::factory()->create(['email' => 'userA@example.com']);
        $userB = User::factory()->create(['email' => 'userB@example.com']);

        Passport::actingAs($userA);

        $response = $this->putJson('/api/me', [
            'email' => 'userB@example.com', 
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    it('allows user to save their profile while keeping their own email', function () {

        $user = User::factory()->create(['email' => 'myemail@example.com']);
        Passport::actingAs($user);

        $response = $this->putJson('/api/me', [
            'name' => 'New Name',
            'email' => 'myemail@example.com', 
        ]);

        $response->assertStatus(200);
    });

    it('can update password with valid confirmation', function () {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->putJson('/api/me', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    });

    it('fails to update password if confirmation does not match', function () {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->putJson('/api/me', [
            'password' => 'newpassword123',
            'password_confirmation' => 'wrong-matching',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    // ---> test DELETE
    it('can delete users own profile', function() {

        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->deleteJson('/api/me');

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });

    // test Access
    it('can not access profile without a token', function () {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    });

    it('deletes all associated games and pockets when a user is deleted', function () {

        $user = User::factory()->create();
        $game = Game::factory()->create(['user_id' => $user->id]);
        $pocketId = $game->pocket->id;
        $gameId = $game->id;

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $this->assertDatabaseMissing('games', ['id' => $gameId]);

        $this->assertDatabaseMissing('pockets', ['id' => $pocketId]);
    });


