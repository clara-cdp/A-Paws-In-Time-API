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


        $viewUsersPermission = Permission::create([
            'name' => PermissionsEnum::ViewUsers->value,
            'guard_name' => 'api',
        ]);

        $editUsersPermission = Permission::create([
            'name' => PermissionsEnum::EditUsers->value,
            'guard_name' => 'api',
        ]);

        $deleteUserPermission = Permission::create([
            'name' => PermissionsEnum::DeleteUsers->value,
            'guard_name' => 'api',
        ]);

        $blockUserPermission = Permission::create([
            'name' => PermissionsEnum::BlockUsers->value,
            'guard_name' => 'api',
        ]);

        // ----------------- permissions assingment ------------------

        $adminRole->syncPermissions(Permission::all());

    }
}
