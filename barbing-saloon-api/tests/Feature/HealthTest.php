<?php

it('returns the versioned API health envelope', function () {
    $response = $this->getJson('/api/v1/health');

    $response
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'status',
                'timestamp',
                'services' => [
                    'database',
                    'storage',
                    'api_version',
                    'flagship',
                ],
            ],
        ]);
});
