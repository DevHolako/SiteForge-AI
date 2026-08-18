<?php

declare(strict_types=1);

use SiteForgeAI\Support\Config;

test('it retrieves top-level configuration arrays', function () {
    $defaults = Config::get('defaults');

    expect($defaults)
        ->toBeArray()
        ->toHaveKey('ai_provider')
        ->toHaveKey('temperature');
});

test('it retrieves nested configuration values via dot notation', function () {
    $model = Config::get('defaults.ai_model');
    expect($model)->toBe('gpt-4o-mini');

    $openaiName = Config::get('ai_models.providers.openai.name');
    expect($openaiName)->toBe('OpenAI');
});

test('it returns fallback default when key does not exist', function () {
    $nonExistent = Config::get('defaults.non_existent_key', 'fallback_val');
    expect($nonExistent)->toBe('fallback_val');

    $missingFile = Config::get('unknown_file.some_key', 'default_return');
    expect($missingFile)->toBe('default_return');
});

test('it loads starter presets correctly', function () {
    $presets = Config::get('presets');

    expect($presets)->toBeArray()->toHaveCount(5);
    expect($presets[0])->toHaveKeys(['id', 'title', 'theme', 'plugins']);
});
