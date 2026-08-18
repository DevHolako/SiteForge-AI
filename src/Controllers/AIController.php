<?php
declare(strict_types=1);

namespace SiteForgeAI\Controllers;

use WP_REST_Request;

class AIController extends BaseController
{
    public function enhance(WP_REST_Request $request): array
    {
        $validated = $this->validate($request, [
            'prompt' => 'required|string|min:5',
        ]);

        return [
            'enhanced_prompt' => $validated['prompt'],
            'message'         => __('AI Controller enhance stub.', 'siteforge-ai'),
        ];
    }

    public function blueprint(WP_REST_Request $request): array
    {
        $validated = $this->validate($request, [
            'prompt'   => 'required|string|min:5',
            'provider' => 'sometimes|string|in:openai,gemini,anthropic,groq',
            'model'    => 'sometimes|string',
        ]);

        return [
            'blueprint' => [],
            'message'   => __('AI Controller blueprint stub.', 'siteforge-ai'),
        ];
    }
}
