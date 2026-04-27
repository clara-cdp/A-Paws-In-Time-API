<?php

it('responds to preflight requests from allowed frontend origins', function () {
    config([
        'cors.allowed_origins' => ['https://frontend.example.com'],
        'cors.allowed_methods' => ['*'],
        'cors.allowed_headers' => ['*'],
        'cors.supports_credentials' => false,
    ]);

    $this->withHeaders([
        'Origin' => 'https://frontend.example.com',
        'Access-Control-Request-Method' => 'POST',
        'Access-Control-Request-Headers' => 'content-type, authorization',
    ])
        ->options('/api/auth/login')
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'https://frontend.example.com')
        ->assertHeader('Access-Control-Allow-Methods', 'POST')
        ->assertHeader('Access-Control-Allow-Headers', 'content-type, authorization');
});
