<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'User',
            'email' => 'user@apaws.com',
        ])->assignRole(RolesEnum::User->value);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@apaws.com',
        ])->assignRole(RolesEnum::Admin->value);

        User::factory(10)->create()->each(function ($user) {
            $user->assignRole(\App\Enums\RolesEnum::User->value);
        });
    }

}