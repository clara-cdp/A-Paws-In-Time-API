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
        $admin = User::updateOrCreate(
            ['email' => 'adminino@apaws.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt("Pawsword1!")
            ]
        );
        $admin->assignRole(RolesEnum::Admin->value);

        $paw = User::updateOrCreate(
            ['email' => 'paw@apaws.com'],
            [
                'name' => 'Edgar Allan Paw',
                'password' => bcrypt("Pawsword2!")
            ]
        );
        $paw->assignRole(RolesEnum::User->value);

        if (User::count() < 10) {
            User::factory(5)->create()->each(function ($user) {
                $user->assignRole(\App\Enums\RolesEnum::User->value);
            });
        }
    }

}