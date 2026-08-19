<?php

declare(strict_types=1);

namespace SiteForgeAI\Controllers;

use SiteForgeAI\Services\RollbackService;
use SiteForgeAI\Support\Response;
use WP_REST_Request;
use WP_REST_Response;

class RollbackController extends BaseController
{
    public function __construct(
        private readonly RollbackService $rollbackService
    ) {
    }

    /**
     * List all recorded snapshots.
     * GET /siteforge_ai/v1/rollback/list
     */
    public function list(WP_REST_Request $request): WP_REST_Response
    {
        $snapshots = $this->rollbackService->getSnapshots();

        return Response::ok(array_values($snapshots));
    }

    /**
     * Roll back a specific or the latest generation batch.
     * POST /siteforge_ai/v1/rollback/revert
     */
    public function revert(WP_REST_Request $request): WP_REST_Response
    {
        $params = (array) $request->get_json_params();
        $batchId = isset($params['batch_id']) ? (string) $params['batch_id'] : null;

        $result = $this->rollbackService->rollback($batchId);

        return Response::ok($result);
    }
}
