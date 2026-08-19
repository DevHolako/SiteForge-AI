<?php

declare(strict_types=1);

beforeEach(function () {
    authenticateAsAdmin();
    \SiteForgeAI\Core\Router::register();
});

test('it rejects /ai/enhance with 422 if prompt is missing or too short', function () {
    $request = new WP_REST_Request('POST', '/siteforge_ai/v1/ai/enhance');
    $request->set_header('content-type', 'application/json');
    $request->set_body(json_encode([
        'prompt' => 'a', // too short, min is 3
    ]));

    $response = rest_do_request($request);
    expect($response->get_status())->toBe(422);

    $data = $response->get_data();
    expect($data['ok'])->toBeFalse()
        ->and($data['error'])->toContain('prompt must be at least 3 characters');
});

test('it rejects /ai/blueprint with 422 if prompt is invalid', function () {
    $request = new WP_REST_Request('POST', '/siteforge_ai/v1/ai/blueprint');
    $request->set_header('content-type', 'application/json');
    $request->set_body(json_encode([
        'prompt'      => 'hi', // too short, min is 5
        'temperature' => 'not_a_number',
    ]));

    $response = rest_do_request($request);
    expect($response->get_status())->toBe(422);

    $data = $response->get_data();
    expect($data['ok'])->toBeFalse()
        ->and($data['error'])->toContain('prompt must be at least 5 characters');
});
