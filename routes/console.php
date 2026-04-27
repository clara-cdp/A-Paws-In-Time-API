<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\ClientRepository;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('passport:ensure-personal-client {--name=A Paws In Time Personal Access Client} {--provider=users}', function () {
    $clients = app(ClientRepository::class);
    $provider = (string) ($this->option('provider') ?: config('auth.guards.api.provider', 'users'));

    try {
        $client = $clients->personalAccessClient($provider);

        $this->info("Personal access client already exists: {$client->getKey()}");

        return 0;
    } catch (\RuntimeException) {
        $clients->createPersonalAccessGrantClient((string) $this->option('name'), $provider);

        $this->info('Personal access client created.');

        return 0;
    }
})->purpose('Create the Passport personal access client if it is missing');
