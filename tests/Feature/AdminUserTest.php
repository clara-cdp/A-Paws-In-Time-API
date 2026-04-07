
<?php

use App\Models\User;
use App\Enums\RolesEnum;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;

    beforeEach(function () {
        Role::create(['name' => 'User', 'guard_name' => 'api']);
        Role::create(['name' => 'Admin', 'guard_name' => 'api']);

        Artisan::call('passport:client', ['--personal' => true, '--name' => 'TestClient', '--no-interaction' => true]);
    });

    // --> GET | index() 
    it('allows an admin to view all users', function()
        {
            $admin = User::factory()->create();
            $admin->assignRole(RolesEnum::Admin->value);

            User::factory()->count(3)->create(); 

            Passport::actingAs($admin);

            $response = $this->getJson('/api/admin/users');

            $response->assertStatus(200)
                ->assertJsonCount(4, 'data'); 
        });


    it('allows an admin to view the paginated user list', function () 
        {
            $admin = User::factory()->create();
            $admin->assignRole(RolesEnum::Admin->value);

            User::factory()->count(5)->create(); 

            Passport::actingAs($admin);

            $response = $this->getJson('/api/admin/users');

            $response->assertStatus(200)
                ->assertJsonStructure(['data', 'links']) 
                ->assertJsonCount(6, 'data');
        });

    // --> GET | show()
    it('allows an admin to view a specific user profile', function ()
        {
            $admin = User::factory()->create();
            $admin->assignRole(RolesEnum::Admin->value);

            $player = User::factory()->create();

            Passport::actingAs($admin);

            $response = $this->getJson("/api/admin/users/{$player->id}");

            $response->assertStatus(200)
                ->assertJsonPath('data.id', $player->id);
                });

    // --->  PUT | update() 
    it('allows an admin to update a regular user profile', function () 
        {
            $admin = User::factory()->create();
            $admin->assignRole(RolesEnum::Admin->value);

            $player = User::factory()->create(['name' => 'Old Player Name']);

            Passport::actingAs($admin);

            $response = $this->putJson("/api/admin/users/{$player->id}", [
                'name' => 'New Player Name',
            ]);

            $response->assertStatus(200);
            $this->assertDatabaseHas('users', [
                'id' => $player->id,
                'name' => 'New Player Name'
            ]);
        });

    // --->  PUT |  block()
    it('allows an admin to block or unblock a player', function () 
        {
            $admin = User::factory()->create();
            $admin->assignRole(RolesEnum::Admin->value);

            $player = User::factory()->create(['is_active' => true]);

            Passport::actingAs($admin);

            //  Block them
            $this->putJson("/api/admin/users/{$player->id}/block")->assertStatus(200);
            $this->assertDatabaseHas('users', ['id' => $player->id, 'is_active' => 0]); 

            //  Unblock them
            $this->putJson("/api/admin/users/{$player->id}/block")->assertStatus(200);
            $this->assertDatabaseHas('users', ['id' => $player->id, 'is_active' => 1]);

            $this->putJson("/api/admin/users/{$player->id}/block")->assertStatus(200);
            $this->assertEquals(0, $player->fresh()->is_active);
        });

    // --->  DELETE |  destroy()
    it('allows an Admin to delete a regular user',function() 
        {
            $admin = User::factory()->create();
            $admin->assignRole(RolesEnum::Admin->value);

            $user = User::factory()->create();

            Passport::actingAs($admin);

            $response = $this->deleteJson("/api/admin/users/{$user->id}");

            $response->assertStatus(200);

            $this->assertDatabaseMissing('users',['id'=>$user->id]);

        });

    // --->  protectAdmin()
    it('prevents an admin from deleting or blocking another admin', function () 
        {
            $admin1 = User::factory()->create();
            $admin1->assignRole(RolesEnum::Admin->value);

            $admin2 = User::factory()->create();
            $admin2->assignRole(RolesEnum::Admin->value);

            Passport::actingAs($admin1);

            // Try to delete admin
            $this->deleteJson("/api/admin/users/{$admin2->id}")->assertStatus(403);

            // Try to block admin
            $this->putJson("/api/admin/users/{$admin2->id}/block")->assertStatus(403);

            $this->assertDatabaseHas('users', ['id' => $admin2->id]); 
        });

    it('prevents an admin from deleting themselves', function () 
        {
            $admin = User::factory()->create();
            $admin->assignRole(RolesEnum::Admin->value);

            Passport::actingAs($admin);

            $response = $this->deleteJson("/api/admin/users/{$admin->id}");

            $response->assertStatus(403);
            $this->assertDatabaseHas('users', ['id' => $admin->id]);
        });

    // ---> route Access 
    it('denies a regular user access to any admin route', function () 
        {
            $player = User::factory()->create();
            $player->assignRole(RolesEnum::User->value);

            Passport::actingAs($player);

            $this->getJson('/api/admin/users')->assertStatus(403);
            $this->deleteJson("/api/admin/users/1")->assertStatus(403);
        });

    // ---> safety admin checks

    it('prevents last admin to delete itseld', function () 
        {
            $lastAdmin = User::factory()->create();
            $lastAdmin->assignRole(RolesEnum::Admin->value);

            Passport::actingAs($lastAdmin);

            $response = $this->deleteJson('/api/me');

           
            $response->assertStatus(403)
                ->assertJsonPath('message', 'Action denied: You are the last Admin. Promote another user before deleting your account.');

            $this->assertDatabaseHas('users', ['id' => $lastAdmin->id]);
        });

    it('allows an admin to delete itself if other exists', function () 
        {
            
            $admin1 = User::factory()->create();
            $admin1->assignRole(RolesEnum::Admin->value);

            $admin2 = User::factory()->create();
            $admin2->assignRole(RolesEnum::Admin->value);

            Passport::actingAs($admin1);

            $response = $this->deleteJson('/api/me');

            $response->assertStatus(200);
            $this->assertDatabaseMissing('users', ['id' => $admin1->id]);
            $this->assertDatabaseHas('users', ['id' => $admin2->id]);
        });
