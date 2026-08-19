<?php

declare(strict_types=1);

use SiteForgeAI\Services\AI\AIFactory;
use SiteForgeAI\Services\AI\OpenAIService;
use SiteForgeAI\Services\AI\GeminiService;
use SiteForgeAI\Services\AI\AnthropicService;
use SiteForgeAI\Services\AI\GroqService;

test('it creates OpenAI service instance', function () {
    $client = AIFactory::create('openai');
    expect($client)->toBeInstanceOf(OpenAIService::class)
        ->and($client->getProviderName())->toBe('openai');
});

test('it creates Gemini service instance', function () {
    $client = AIFactory::create('gemini');
    expect($client)->toBeInstanceOf(GeminiService::class)
        ->and($client->getProviderName())->toBe('gemini');
});

test('it creates Anthropic service instance', function () {
    $client = AIFactory::create('anthropic');
    expect($client)->toBeInstanceOf(AnthropicService::class)
        ->and($client->getProviderName())->toBe('anthropic');
});

test('it creates Groq service instance', function () {
    $client = AIFactory::create('groq');
    expect($client)->toBeInstanceOf(GroqService::class)
        ->and($client->getProviderName())->toBe('groq');
});

test('it throws exception for unsupported AI provider', function () {
    AIFactory::create('unsupported_provider_xyz');
})->throws(InvalidArgumentException::class, 'Unsupported AI provider');
