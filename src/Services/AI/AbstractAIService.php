<?php

declare(strict_types=1);

namespace SiteForgeAI\Services\AI;

use RuntimeException;
use SiteForgeAI\Security\Crypto;

abstract class AbstractAIService implements AIClientInterface
{
    protected string $apiKey;
    protected string $model;
    protected float $temperature;
    protected int $maxTokens;

    public function __construct(
        string $encryptedApiKey = '',
        string $model = '',
        float $temperature = 0.7,
        int $maxTokens = 4000
    ) {
        // If the key is encrypted with AES-256, decrypt it; otherwise use raw key
        $this->apiKey = str_starts_with($encryptedApiKey, 'eyJ') || str_contains($encryptedApiKey, '=')
            ? (Crypto::decrypt($encryptedApiKey) ?: $encryptedApiKey)
            : $encryptedApiKey;

        $this->model = $model;
        $this->temperature = $temperature;
        $this->maxTokens = $maxTokens;
    }

    /**
     * Send HTTP POST request using WordPress HTTP API (wp_remote_post).
     *
     * @param string $url
     * @param array<string, string> $headers
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    protected function post(string $url, array $headers, array $body): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException(
                sprintf(__('API key for [%s] is not configured. Please add it in SiteForge AI Settings.', 'siteforge-ai'), $this->getProviderName())
            );
        }

        $args = [
            'timeout'     => 60,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking'    => true,
            'headers'     => array_merge([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ], $headers),
            'body'        => wp_json_encode($body),
            'sslverify'   => apply_filters('siteforge_ai_ssl_verify', true),
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            throw new RuntimeException(
                sprintf(__('HTTP request failed: %s', 'siteforge-ai'), $response->get_error_message())
            );
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);
        $rawBody = (string) wp_remote_retrieve_body($response);
        $decoded = json_decode($rawBody, true);

        if ($statusCode < 200 || $statusCode >= 300) {
            $errorMessage = is_array($decoded) && isset($decoded['error'])
                ? (is_array($decoded['error']) ? ($decoded['error']['message'] ?? wp_json_encode($decoded['error'])) : (string) $decoded['error'])
                : sprintf(__('API returned HTTP error code %d.', 'siteforge-ai'), $statusCode);

            throw new RuntimeException(
                sprintf(__('[%s API Error %d]: %s', 'siteforge-ai'), ucfirst($this->getProviderName()), $statusCode, $errorMessage)
            );
        }

        if (!is_array($decoded)) {
            throw new RuntimeException(
                sprintf(__('Invalid JSON response from [%s] API: %s', 'siteforge-ai'), $this->getProviderName(), substr($rawBody, 0, 200))
            );
        }

        return $decoded;
    }

    /**
     * Clean markdown-wrapped JSON code fences from LLM text responses.
     */
    protected function cleanJsonString(string $raw): string
    {
        $cleaned = trim($raw);

        // Remove ```json ... ``` or ``` ... ```
        if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/i', $cleaned, $matches)) {
            $cleaned = trim($matches[1]);
        }

        // If JSON is embedded within explanatory text, extract the outermost { ... } or [ ... ]
        if (!str_starts_with($cleaned, '{') && !str_starts_with($cleaned, '[')) {
            $firstBrace = strpos($cleaned, '{');
            $firstBracket = strpos($cleaned, '[');

            $start = false;
            if ($firstBrace !== false && $firstBracket !== false) {
                $start = min($firstBrace, $firstBracket);
            } elseif ($firstBrace !== false) {
                $start = $firstBrace;
            } elseif ($firstBracket !== false) {
                $start = $firstBracket;
            }

            if ($start !== false) {
                $lastBrace = strrpos($cleaned, '}');
                $lastBracket = strrpos($cleaned, ']');
                $end = max($lastBrace !== false ? $lastBrace : 0, $lastBracket !== false ? $lastBracket : 0);

                if ($end > $start) {
                    $cleaned = substr($cleaned, $start, $end - $start + 1);
                }
            }
        }

        return $cleaned;
    }

    /**
     * Parse raw string into decoded array.
     *
     * @param string $raw
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    protected function parseJson(string $raw): array
    {
        $cleaned = $this->cleanJsonString($raw);
        $decoded = json_decode($cleaned, true);

        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                sprintf(__('Failed to parse structured JSON from AI: %s. Raw output: %s', 'siteforge-ai'), json_last_error_msg(), substr($raw, 0, 300))
            );
        }

        return $decoded;
    }

    /**
     * Default testConnection implementation.
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->generateText('Respond with the single word "CONNECTED".', [
                'max_tokens' => 10,
            ]);

            return !empty($response);
        } catch (\Throwable) {
            return false;
        }
    }
}
