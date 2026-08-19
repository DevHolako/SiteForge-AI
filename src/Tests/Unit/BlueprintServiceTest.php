<?php

declare(strict_types=1);

use SiteForgeAI\Services\BlueprintService;
use SiteForgeAI\Services\AI\AIClientInterface;

test('it enhances prompt using AI client response', function () {
    $fakeAi = new class () implements AIClientInterface {
        public function generateText(string $prompt, array $options = []): string
        {
            return '';
        }
        public function generateJson(string $prompt, array $schema = [], array $options = []): array
        {
            return [
                'enhanced_prompt'   => 'A luxury boutique hotel with rooftop infinity pool and Michelin dining',
                'target_audience'   => 'High-net-worth leisure travelers',
                'brand_vibe'        => 'Luxury, Elegance, Modern',
                'suggested_pages'   => ['Home', 'Rooms', 'Dining', 'Spa', 'Contact'],
                'recommended_theme' => 'astra',
            ];
        }
        public function testConnection(): bool
        {
            return true;
        }
        public function getProviderName(): string
        {
            return 'fake';
        }
    };

    $service = new BlueprintService($fakeAi);
    $result = $service->enhancePrompt('A luxury hotel in Rome');

    expect($result)->toBeArray()
        ->toHaveKey('original_prompt', 'A luxury hotel in Rome')
        ->toHaveKey('enhanced_prompt')
        ->toHaveKey('brand_vibe')
        ->toHaveKey('recommended_theme', 'astra');
});

test('it generates and sanitizes a complete WordPress site blueprint', function () {
    $fakeAi = new class () implements AIClientInterface {
        public function generateText(string $prompt, array $options = []): string
        {
            return '';
        }
        public function generateJson(string $prompt, array $schema = [], array $options = []): array
        {
            return [
                'site' => [
                    'title'   => 'Villa Roma Luxury Hotel',
                    'tagline' => 'Timeless Roman Hospitality',
                    'niche'   => 'hotel',
                ],
                'theme' => [
                    'slug' => 'astra',
                    'name' => 'Astra',
                ],
                'plugins' => [
                    ['slug' => 'elementor', 'name' => 'Elementor', 'source' => 'wporg', 'required' => true],
                ],
                'pages' => [
                    [
                        'title'         => 'Home',
                        'slug'          => 'home',
                        'is_front_page' => true,
                        'sections'      => [
                            ['type' => 'hero', 'heading' => 'Welcome to Villa Roma', 'content' => 'Luxury awaits.'],
                        ],
                    ],
                ],
                'customizer' => [
                    'colors' => ['primary' => '#D4AF37'],
                ],
            ];
        }
        public function testConnection(): bool
        {
            return true;
        }
        public function getProviderName(): string
        {
            return 'fake';
        }
    };

    $service = new BlueprintService($fakeAi);
    $blueprint = $service->generateBlueprint('Luxury hotel in Rome');

    expect($blueprint)->toBeArray()
        ->toHaveKeys(['site', 'theme', 'plugins', 'pages', 'posts', 'customizer', 'navigation'])
        ->and($blueprint['site']['title'])->toBe('Villa Roma Luxury Hotel')
        ->and($blueprint['theme']['slug'])->toBe('astra')
        ->and($blueprint['pages'])->toHaveCount(1);
});
