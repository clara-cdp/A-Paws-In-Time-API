<?php

use App\Models\User;
use App\Models\Game;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role; 
use Illuminate\Support\Facades\Artisan;



beforeEach(function () {
    Role::create(['name' => 'User', 'guard_name' => 'api']);
    Role::create(['name' => 'Admin', 'guard_name' => 'api']);

    Artisan::call('passport:client', ['--personal' => true, '--no-interaction' => true]);
});

    it('can register a new user', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    });


    it('can login and receive a passport token', function () {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'user_id' => $user->id,
            'revoked' => false
        ]);
    });


    it('revokes the token on logout', function () {
        $user = User::factory()->create();

        $tokenResult = $user->createToken('TestToken');
        $token = $tokenResult->accessToken;
        $tokenId = $tokenResult->token->id;

        $response = $this->withToken($token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);


        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $tokenId,
            'revoked' => true,
        ]);
    });

    it('cannot access protected user route without a token', function () {
        $this->getJson('/api/me')
            ->assertStatus(401);
    });

    it('requires authentication to access any game routes', function () {
        Auth::logout();

        $this->getJson('/api/games')->assertStatus(401);
        $this->postJson('/api/games', ['avatar' => 'Ghost'])->assertStatus(401);
    });

    