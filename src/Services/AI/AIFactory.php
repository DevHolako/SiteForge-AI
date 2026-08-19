<?php

declare(strict_types=1);

namespace SiteForgeAI\Services\AI;

use InvalidArgumentException;
use SiteForgeAI\Services\SettingsService;

class AIFactory
{
    /**
     * Create an AI client instance for the given or configured provider.
     *
     * @param string|null $provider 'openai', 'gemini', 'anthropic', 'groq', or null for active setting
     * @param array<string, mixed> $overrides Custom options (api_key, model, temperature, max_tokens)
     * @return AIClientInterface
     * @throws InvalidArgumentException If provider is unsupported.
     */
    public static function create(?string $provider = null, array $overrides = []): AIClientInterface
    {
        $settings = (array) get_option(SettingsService::SETTINGS_OPTION, []);
        $encryptedKeys = (array) get_option(SettingsService::API_KEYS_OPTION, []);

        $activeProvider = $provider ?: (string) ($settings['ai_provider'] ?? siteforge_config('defaults.ai_provider', 'openai'));
        $model = (string) ($overrides['model'] ?? ($settings['ai_model'] ?? siteforge_config('defaults.ai_model', 'gpt-4o-mini')));
        $temperature = (float) ($overrides['temperature'] ?? ($settings['temperature'] ?? siteforge_config('defaults.temperature', 0.7)));
        $maxTokens = (int) ($overrides['max_tokens'] ?? ($settings['max_tokens'] ?? siteforge_config('defaults.max_tokens', 4000)));

        $encryptedKey = (string) ($overrides['api_key'] ?? ($encryptedKeys[$activeProvider] ?? ''));

        return match (strtolower($activeProvider)) {
            'openai'    => new OpenAIService($encryptedKey, $model, $temperature, $maxTokens),
            'gemini'    => new GeminiService($encryptedKey, $model, $temperature, $maxTokens),
            'anthropic' => new AnthropicService($encryptedKey, $model, $temperature, $maxTokens),
            'groq'      => new GroqService($encryptedKey, $model, $temperature, $maxTokens),
            default     => throw new InvalidArgumentException(
                sprintf(__('Unsupported AI provider: [%s]', 'siteforge-ai'), $activeProvider)
            ),
        };
    }
}
