<?php

declare(strict_types=1);

use SiteForgeAI\Support\Validator;

test('it passes when all required fields and types match rules', function () {
    $data = [
        'prompt'      => 'A luxury hotel in Rome with rooftop bar',
        'provider'    => 'openai',
        'temperature' => 0.7,
        'tokens'      => 4000,
        'is_active'   => true,
        'tags'        => ['hotel', 'luxury'],
    ];

    $rules = [
        'prompt'      => 'required|string|min:10',
        'provider'    => 'required|string|in:openai,gemini,anthropic,groq',
        'temperature' => 'required|numeric|min:0|max:2',
        'tokens'      => 'required|integer|min:100',
        'is_active'   => 'required|boolean',
        'tags'        => 'required|array',
    ];

    $validator = Validator::make($data, $rules);

    expect($validator->passes())->toBeTrue();
    expect($validator->fails())->toBeFalse();
    expect($validator->errors())->toBeEmpty();
});

test('it fails when required fields are missing', function () {
    $validator = Validator::make([], [
        'prompt' => 'required|string',
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->firstError())->toContain('prompt field is required');
});

test('it validates sometimes and nullable rules correctly', function () {
    // Missing 'optional_field' with sometimes should pass
    $validator1 = Validator::make(['prompt' => 'hello world 123'], [
        'prompt'         => 'required|string',
        'optional_field' => 'sometimes|numeric',
    ]);
    expect($validator1->passes())->toBeTrue();

    // Present but bad type should fail
    $validator2 = Validator::make(['prompt' => 'hello world 123', 'optional_field' => 'not_number'], [
        'prompt'         => 'required|string',
        'optional_field' => 'sometimes|numeric',
    ]);
    expect($validator2->fails())->toBeTrue();
});

test('it validates min and max constraints for strings and numbers', function () {
    $short = Validator::make(['prompt' => 'short'], ['prompt' => 'required|string|min:10']);
    expect($short->fails())->toBeTrue();

    $tooHigh = Validator::make(['temperature' => 5], ['temperature' => 'required|numeric|max:2']);
    expect($tooHigh->fails())->toBeTrue();
});
