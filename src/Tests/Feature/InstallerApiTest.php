<?php

declare(strict_types=1);

beforeEach(function () {
    authenticateAsAdmin();
    \SiteForgeAI\Core\Router::register();
});

test('it accepts valid parameters for POST /installer/install', function () {
    $request = new WP_REST_Request('POST', '/siteforge_ai/v1/installer/install');
    $request->set_header('content-type', 'application/json');
    $request->set_body(json_encode([
        'theme'   => 'twentytwentyfour',
        'plugins' => ['Site-Forge-AI'],
    ]));

    $response = rest_do_request($request);
    expect($response->get_status())->toBe(200);

    $data = $response->get_data();
    expect($data['ok'])->toBeTrue()
        ->and($data['data'])->toHaveKeys(['theme', 'plugins', 'errors']);
});

test('it rejects invalid parameters on /installer/install with 422', function () {
    $request = new WP_REST_Request('POST', '/siteforge_ai/v1/installer/install');
    $request->set_header('content-type', 'application/json');
    $request->set_body(json_encode([
        'plugins' => 'not_an_array',
    ]));

    $response = rest_do_request($request);
    expect($response->get_status())->toBe(422);

    $data = $response->get_data();
    expect($data['ok'])->toBeFalse()
        ->and($data['error'])->toContain('plugins must be an array');
});
