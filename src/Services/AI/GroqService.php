<?php

declare(strict_types=1);

namespace SiteForgeAI\Services\AI;

class GroqService extends AbstractAIService
{
    private const DEFAULT_MODEL = 'llama-3.3-70b-versatile';
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    public function getProviderName(): string
    {
        return 'groq';
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $model = $options['model'] ?? ($this->model ?: self::DEFAULT_MODEL);
        $temperature = (float) ($options['temperature'] ?? $this->temperature);
        $maxTokens = (int) ($options['max_tokens'] ?? $this->maxTokens);

        $systemPrompt = $options['system_prompt'] ?? 'You are an expert WordPress site architect and content strategist.';

        $body = [
            'model'       => $model,
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];

        $response = $this->post(self::API_URL, $headers, $body);

        return (string) ($response['choices'][0]['message']['content'] ?? '');
    }

    public function generateJson(string $prompt, array $schema = [], array $options = []): array
    {
        $model = $options['model'] ?? ($this->model ?: self::DEFAULT_MODEL);
        $temperature = (float) ($options['temperature'] ?? $this->temperature);
        $maxTokens = (int) ($options['max_tokens'] ?? $this->maxTokens);

        $systemPrompt = $options['system_prompt'] ?? 'You are an expert WordPress architect. You must respond ONLY with valid JSON conforming to the requested schema.';

        $body = [
            'model'           => $model,
            'messages'        => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature'     => $temperature,
            'max_tokens'      => $maxTokens,
            'response_format' => ['type' => 'json_object'],
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];

        $response = $this->post(self::API_URL, $headers, $body);
        $content = (string) ($response['choices'][0]['message']['content'] ?? '');

        return $this->parseJson($content);
    }
}
