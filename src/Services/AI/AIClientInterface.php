<?php

declare(strict_types=1);

namespace SiteForgeAI\Services\AI;

interface AIClientInterface
{
    /**
     * Generate raw text response from the AI model.
     *
     * @param string $prompt
     * @param array<string, mixed> $options Optional parameters (model, temperature, max_tokens, etc.)
     * @return string
     * @throws \RuntimeException If the API request fails or returns an error.
     */
    public function generateText(string $prompt, array $options = []): string;

    /**
     * Generate structured JSON output from the AI model and decode it as an associative array.
     *
     * @param string $prompt
     * @param array<string, mixed> $schema Optional JSON Schema to enforce response structure.
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws \RuntimeException If generation, network, or JSON decoding fails.
     */
    public function generateJson(string $prompt, array $schema = [], array $options = []): array;

    /**
     * Quick health check / ping to verify if the API key and connection are valid.
     *
     * @return bool
     */
    public function testConnection(): bool;

    /**
     * Get the unique provider identifier (e.g. 'openai', 'gemini', 'anthropic', 'groq').
     *
     * @return string
     */
    public function getProviderName(): string;
}
