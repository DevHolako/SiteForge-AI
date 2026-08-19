<?php

declare(strict_types=1);

namespace SiteForgeAI\Services\AI;

class GeminiService extends AbstractAIService
{
    private const DEFAULT_MODEL = 'gemini-2.0-flash';
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function getProviderName(): string
    {
        return 'gemini';
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $model = $options['model'] ?? ($this->model ?: self::DEFAULT_MODEL);
        $temperature = (float) ($options['temperature'] ?? $this->temperature);
        $maxTokens = (int) ($options['max_tokens'] ?? $this->maxTokens);

        $systemPrompt = $options['system_prompt'] ?? 'You are an expert WordPress site architect and content strategist.';

        $url = sprintf('%s/%s:generateContent?key=%s', self::BASE_URL, rawurlencode($model), rawurlencode($this->apiKey));

        $body = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $prompt]],
                ],
            ],
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'generationConfig' => [
                'temperature'     => $temperature,
                'maxOutputTokens' => $maxTokens,
            ],
        ];

        $response = $this->post($url, [], $body);

        return (string) ($response['candidates'][0]['content']['parts'][0]['text'] ?? '');
    }

    public function generateJson(string $prompt, array $schema = [], array $options = []): array
    {
        $model = $options['model'] ?? ($this->model ?: self::DEFAULT_MODEL);
        $temperature = (float) ($options['temperature'] ?? $this->temperature);
        $maxTokens = (int) ($options['max_tokens'] ?? $this->maxTokens);

        $systemPrompt = $options['system_prompt'] ?? 'You are an expert WordPress architect. You must respond ONLY with valid JSON conforming to the requested schema.';

        $url = sprintf('%s/%s:generateContent?key=%s', self::BASE_URL, rawurlencode($model), rawurlencode($this->apiKey));

        $generationConfig = [
            'temperature'      => $temperature,
            'maxOutputTokens'  => $maxTokens,
            'responseMimeType' => 'application/json',
        ];

        if (!empty($schema)) {
            $generationConfig['responseSchema'] = $schema;
        }

        $body = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $prompt]],
                ],
            ],
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'generationConfig' => $generationConfig,
        ];

        $response = $this->post($url, [], $body);
        $content = (string) ($response['candidates'][0]['content']['parts'][0]['text'] ?? '');

        return $this->parseJson($content);
    }
}
