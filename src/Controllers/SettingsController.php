<?php

declare(strict_types=1);

namespace SiteForgeAI\Controllers;

use SiteForgeAI\Services\SettingsService;
use WP_REST_Request;

class SettingsController extends BaseController
{
    public function __construct(
        private SettingsService $settings_service
    ) {
    }

    public function get(WP_REST_Request $request): array
    {
        return $this->settings_service->get();
    }

    public function save(WP_REST_Request $request): array
    {
        $validated = $this->validate($request, [
            'ai_provider'             => 'sometimes|string|in:openai,gemini,anthropic,groq',
            'ai_model'                => 'sometimes|string',
            'temperature'             => 'sometimes|numeric|min:0|max:2',
            'max_tokens'              => 'sometimes|integer|min:100|max:16000',
            'purge_mock_on_uninstall' => 'sometimes|boolean',
            'api_keys'                => 'sometimes|array',
        ]);

        return $this->settings_service->save($validated);
    }

    public function presets(WP_REST_Request $request): array
    {
        return $this->settings_service->presets();
    }
}
