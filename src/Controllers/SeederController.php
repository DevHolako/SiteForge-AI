<?php

declare(strict_types=1);

namespace SiteForgeAI\Controllers;

use SiteForgeAI\Services\RollbackService;
use SiteForgeAI\Services\Seeder\SiteSeederService;
use SiteForgeAI\Support\Response;
use WP_REST_Request;
use WP_REST_Response;

class SeederController extends BaseController
{
    public function __construct(
        private readonly SiteSeederService $seederService,
        private readonly RollbackService $rollbackService
    ) {
    }

    /**
     * Seed site content from blueprint and record rollback snapshot.
     * POST /siteforge_ai/v1/seeder/seed
     */
    public function seed(WP_REST_Request $request): WP_REST_Response
    {
        $blueprint = $request->get_json_params() ?: [];

        if (empty($blueprint) || !is_array($blueprint)) {
            return Response::error(__('Blueprint payload cannot be empty.', 'siteforge-ai'), 422);
        }

        $snapshot = $this->seederService->seed($blueprint);

        // Record snapshot for 1-click rollback
        $this->rollbackService->recordSnapshot($snapshot);

        return Response::ok($snapshot);
    }

    /**
     * Remove ONLY AI-generated mock data (pages, posts, menus).
     * POST /siteforge_ai/v1/seeder/remove-mock
     */
    public function removeMock(WP_REST_Request $request): WP_REST_Response
    {
        $params = (array) $request->get_json_params();
        $batchId = isset($params['batch_id']) ? (string) $params['batch_id'] : null;

        $summary = $this->seederService->removeMockData($batchId);

        return Response::ok($summary);
    }
}
