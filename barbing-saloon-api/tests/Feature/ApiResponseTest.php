<?php

use App\Http\Responses\ApiResponse;
use Illuminate\Pagination\LengthAwarePaginator;

it('formats successful API responses', function () {
    $response = ApiResponse::success(['id' => 1], 'Created', 201);

    expect($response->getStatusCode())->toBe(201)
        ->and($response->getData(true))->toMatchArray([
            'success' => true,
            'message' => 'Created',
            'data' => ['id' => 1],
        ]);
});

it('formats API errors with field details', function () {
    $response = ApiResponse::error('Invalid input', ['email' => ['Required']], 422);
    $body = $response->getData(true);

    expect($response->getStatusCode())->toBe(422)
        ->and($body['success'])->toBeFalse()
        ->and($body['message'])->toBe('Invalid input')
        ->and($body['error']['code'])->toBe('VALIDATION_FAILED')
        ->and($body['error']['details']['field_errors'])->toBe(['email' => ['Required']]);
});

it('formats paginated API responses with metadata', function () {
    $page = new LengthAwarePaginator([['id' => 1]], 1, 15, 1);
    $response = ApiResponse::paginated($page, 'Records');

    expect($response->getData(true))->toMatchArray([
        'success' => true,
        'message' => 'Records',
        'data' => [['id' => 1]],
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => 1,
        ],
    ]);
});
