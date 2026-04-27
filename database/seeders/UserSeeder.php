<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'adminino@apaws.com'],
            [
                'name' => 'Admin',
                'password' => 'Pawsword1!',
            ]
        );

        $admin->assignRole(RolesEnum::Admin->value);

        $player = User::firstOrCreate(
            ['email' => 'paw@apaws.com'],
            [
                'name' => 'Edgar Allan Paw',
                'password' => 'Pawsword2!',
            ]
        );

        $player->assignRole(RolesEnum::User->value);

        $missingDemoUsers = max(0, 7 - User::count());

        User::factory($missingDemoUsers)->create()->each(function ($user) {
            $user->assignRole(RolesEnum::User->value);
        });
    }

}
