<?php

declare(strict_types=1);

namespace SiteForgeAI\Services;

use SiteForgeAI\Security\Crypto;

class SettingsService
{
    public const SETTINGS_OPTION = 'siteforge_ai_settings';
    public const KEYS_OPTION     = 'siteforge_ai_api_keys';
    public const API_KEYS_OPTION = 'siteforge_ai_api_keys';

    /**
     * Get all plugin settings with masked API keys.
     */
    public function get(): array
    {
        $defaults       = (array) siteforge_config('defaults', []);
        $saved_settings = (array) get_option(self::SETTINGS_OPTION, []);
        $settings       = wp_parse_args($saved_settings, $defaults);

        $encrypted_keys = (array) get_option(self::KEYS_OPTION, []);
        $masked_keys    = [];

        foreach (['openai', 'gemini', 'anthropic', 'groq'] as $provider) {
            $raw_encrypted = $encrypted_keys[$provider] ?? '';

            if (!empty($raw_encrypted)) {
                $decrypted              = Crypto::decrypt($raw_encrypted);
                $masked_keys[$provider] = $this->maskKey($decrypted);
            } else {
                $masked_keys[$provider] = '';
            }
        }

        $settings['api_keys'] = $masked_keys;

        return $settings;
    }

    /**
     * Save plugin settings and encrypt raw API keys.
     */
    public function save(array $data): array
    {
        $current_settings = (array) get_option(self::SETTINGS_OPTION, []);

        // Update general settings
        $fields = ['ai_provider', 'ai_model', 'temperature', 'max_tokens', 'purge_mock_on_uninstall'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $current_settings[$field] = $data[$field];
            }
        }
        update_option(self::SETTINGS_OPTION, $current_settings);

        // Encrypt and update API keys if provided
        if (isset($data['api_keys']) && is_array($data['api_keys'])) {
            $existing_keys = (array) get_option(self::KEYS_OPTION, []);

            foreach ($data['api_keys'] as $provider => $raw_key) {
                $raw_key = trim((string) $raw_key);

                // If user cleared the field
                if (empty($raw_key)) {
                    unset($existing_keys[$provider]);
                    continue;
                }

                // If raw key was updated (not already masked)
                if (!str_contains($raw_key, '...')) {
                    $existing_keys[$provider] = Crypto::encrypt($raw_key);
                }
            }

            update_option(self::KEYS_OPTION, $existing_keys);
        }

        return $this->get();
    }

    /**
     * Retrieve decrypted API key for internal use by AI services.
     */
    public function getApiKey(string $provider): string
    {
        $encrypted_keys = (array) get_option(self::KEYS_OPTION, []);
        $encrypted      = $encrypted_keys[$provider] ?? '';

        if (empty($encrypted)) {
            return '';
        }

        return Crypto::decrypt($encrypted);
    }

    /**
     * Return starter niche presets from config.
     */
    public function presets(): array
    {
        return (array) siteforge_config('presets', []);
    }

    /**
     * Mask API key to prevent exposing raw key in browser responses.
     */
    private function maskKey(string $key): string
    {
        $len = strlen($key);
        if ($len <= 8) {
            return str_repeat('*', $len);
        }

        return substr($key, 0, 4) . '...' . substr($key, -4);
    }
}
