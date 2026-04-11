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
            'name' => 'Admin',
            'email' => 'adminino@apaws.com',
            'password' => "Pawsword1!"
        ])->assignRole(RolesEnum::Admin->value);

        User::factory()->create([
            'name' => 'Edgar Allan Paw',
            'email' => 'paw@apaws.com',
            'password' => "Pawsword2!"
        ])->assignRole(RolesEnum::User->value);

        User::factory(5)->create()->each(function ($user) {
            $user->assignRole(\App\Enums\RolesEnum::User->value);
        });
    }

}