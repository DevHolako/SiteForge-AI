<?php

declare(strict_types=1);

namespace SiteForgeAI\Services\AI;

class AnthropicService extends AbstractAIService
{
    private const DEFAULT_MODEL = 'claude-3-5-sonnet-latest';
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    public function getProviderName(): string
    {
        return 'anthropic';
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $model = $options['model'] ?? ($this->model ?: self::DEFAULT_MODEL);
        $temperature = (float) ($options['temperature'] ?? $this->temperature);
        $maxTokens = (int) ($options['max_tokens'] ?? $this->maxTokens);

        $systemPrompt = $options['system_prompt'] ?? 'You are an expert WordPress site architect and content strategist.';

        $body = [
            'model'       => $model,
            'max_tokens'  => $maxTokens,
            'temperature' => $temperature,
            'system'      => $systemPrompt,
            'messages'    => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        $headers = [
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
        ];

        $response = $this->post(self::API_URL, $headers, $body);

        return (string) ($response['content'][0]['text'] ?? '');
    }

    public function generateJson(string $prompt, array $schema = [], array $options = []): array
    {
        $model = $options['model'] ?? ($this->model ?: self::DEFAULT_MODEL);
        $temperature = (float) ($options['temperature'] ?? $this->temperature);
        $maxTokens = (int) ($options['max_tokens'] ?? $this->maxTokens);

        $systemPrompt = $options['system_prompt'] ?? 'You are an expert WordPress architect. You must respond ONLY with valid JSON conforming to the requested structure without any markdown code fences or conversational text.';

        $body = [
            'model'       => $model,
            'max_tokens'  => $maxTokens,
            'temperature' => $temperature,
            'system'      => $systemPrompt,
            'messages'    => [
                ['role' => 'user', 'content' => $prompt . "\n\nOutput only pure raw JSON:"],
            ],
        ];

        $headers = [
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
        ];

        $response = $this->post(self::API_URL, $headers, $body);
        $content = (string) ($response['content'][0]['text'] ?? '');

        return $this->parseJson($content);
    }
}
