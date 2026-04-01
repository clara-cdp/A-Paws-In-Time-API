<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $userRole = Role::findOrCreate(RolesEnum::User->value, 'api');
        $adminRole = Role::findOrCreate(RolesEnum::Admin->value, 'api');

        $createPlayersPermission = Permission::create([
            'name' => PermissionsEnum::CreatePlayers->value,
            'guard_name' => 'api',
        ]);

        $seeUsersPermission = Permission::create([
            'name' => PermissionsEnum::SeeUsers->value,
            'guard_name' => 'api',
        ]);

        $editUserPermission = Permission::create([
            'name' => PermissionsEnum::EditUser->value,
            'guard_name' => 'api',
        ]);

        $deleteUserPermission = Permission::create([
            'name' => PermissionsEnum::DeleteUser->value,
            'guard_name' => 'api',
        ]);

        $blockUserPermission = Permission::create([
            'name' => PermissionsEnum::BlockUser->value,
            'guard_name' => 'api',
        ]);

        // ----------------- permissions assingment ------------------
        $userRole->syncPermissions([$createPlayersPermission]);

        $adminRole->syncPermissions(Permission::all());

    }
}
