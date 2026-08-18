<?php

beforeEach(function () {
    authenticateAsAdmin();
    \SiteForgeAI\Core\Router::register();
});

test('it returns settings and masked api keys via GET /settings/get', function () {
    $request = new WP_REST_Request('GET', '/siteforge_ai/v1/settings/get');
    $response = rest_do_request($request);

    expect($response->get_status())->toBe(200);
    
    $data = $response->get_data();
    expect($data)->toHaveKey('ok', true)
        ->and($data['data'])->toHaveKeys(['ai_provider', 'ai_model', 'temperature', 'api_keys']);
});

test('it encrypts and saves settings via POST /settings/save', function () {
    $request = new WP_REST_Request('POST', '/siteforge_ai/v1/settings/save');
    $request->set_header('content-type', 'application/json');
    $request->set_body(json_encode([
        'temperature' => 0.85,
        'ai_provider' => 'gemini',
        'api_keys'    => [
            'gemini' => 'AIzaSyTestSecretApiKey1234567',
        ],
    ]));

    $response = rest_do_request($request);
    expect($response->get_status())->toBe(200);

    $data = $response->get_data();
    expect($data['ok'])->toBeTrue();
    expect($data['data']['temperature'])->toBe(0.85);
    expect($data['data']['ai_provider'])->toBe('gemini');
    // Verify key was masked in response
    expect($data['data']['api_keys']['gemini'])->toBe('AIza...4567');
});

test('it rejects invalid parameters with 422 Unprocessable Entity', function () {
    $request = new WP_REST_Request('POST', '/siteforge_ai/v1/settings/save');
    $request->set_header('content-type', 'application/json');
    $request->set_body(json_encode([
        'temperature' => 'not_a_valid_number',
    ]));

    $response = rest_do_request($request);
    expect($response->get_status())->toBe(422);

    $data = $response->get_data();
    expect($data['ok'])->toBeFalse();
    expect($data['error'])->toContain('temperature must be a number');
});

test('it returns starter presets via GET /presets/list', function () {
    $request = new WP_REST_Request('GET', '/siteforge_ai/v1/presets/list');
    $response = rest_do_request($request);

    expect($response->get_status())->toBe(200);

    $data = $response->get_data();
    expect($data['ok'])->toBeTrue();
    expect($data['data'])->toBeArray()->toHaveCount(5);
});