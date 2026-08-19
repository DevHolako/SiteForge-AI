<?php

declare(strict_types=1);

namespace SiteForgeAI\Controllers;

use SiteForgeAI\Services\BlueprintService;
use SiteForgeAI\Support\Response;
use WP_REST_Request;
use WP_REST_Response;

class AIController extends BaseController
{
    public function __construct(
        private readonly BlueprintService $blueprintService
    ) {
    }

    /**
     * Enhance user prompt with architectural suggestions.
     * POST /siteforge_ai/v1/ai/enhance
     */
    public function enhance(WP_REST_Request $request): WP_REST_Response
    {
        $params = $this->validate($request, [
            'prompt' => 'required|string|min:3',
        ]);

        $enhanced = $this->blueprintService->enhancePrompt((string) $params['prompt']);

        return Response::ok($enhanced);
    }

    /**
     * Generate complete WordPress Site Blueprint.
     * POST /siteforge_ai/v1/ai/blueprint
     */
    public function blueprint(WP_REST_Request $request): WP_REST_Response
    {
        $params = $this->validate($request, [
            'prompt'      => 'required|string|min:5',
            'niche'       => 'sometimes|string',
            'theme'       => 'sometimes|string',
            'temperature' => 'sometimes|numeric|min:0|max:2',
        ]);

        $blueprint = $this->blueprintService->generateBlueprint(
            (string) $params['prompt'],
            $params
        );

        return Response::ok($blueprint);
    }
}
