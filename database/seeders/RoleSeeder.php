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

        Role::findOrCreate(RolesEnum::User->value, 'api');
        $adminRole = Role::findOrCreate(RolesEnum::Admin->value, 'api');


        Permission::findOrCreate(PermissionsEnum::ViewUsers->value, 'api');
        Permission::findOrCreate(PermissionsEnum::EditUsers->value, 'api');
        Permission::findOrCreate(PermissionsEnum::DeleteUsers->value, 'api');
        Permission::findOrCreate(PermissionsEnum::BlockUsers->value, 'api');

        // ----------------- permissions assingment ------------------

        $adminRole->syncPermissions(Permission::all());

    }
}
