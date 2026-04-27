<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Client;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('can run deployment setup repeatedly without duplicating required records', function () {
    Artisan::call('db:seed', ['--force' => true]);
    Artisan::call('db:seed', ['--force' => true]);

    expect(User::where('email', 'adminino@apaws.com')->count())->toBe(1);
    expect(User::where('email', 'paw@apaws.com')->count())->toBe(1);
    expect(Role::where('guard_name', 'api')->count())->toBe(2);
    expect(Permission::where('guard_name', 'api')->count())->toBe(4);

    Artisan::call('passport:ensure-personal-client', ['--provider' => 'users']);
    Artisan::call('passport:ensure-personal-client', ['--provider' => 'users']);

    $personalClients = Client::query()
        ->get()
        ->filter(fn (Client $client): bool => $client->provider === 'users'
            && ! $client->revoked
            && $client->hasGrantType('personal_access'));

    expect($personalClients)->toHaveCount(1);
});
