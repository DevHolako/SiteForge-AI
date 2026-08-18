<?php

declare(strict_types=1);

namespace SiteForgeAI\Controllers;

use WP_REST_Request;

class RollbackController extends BaseController
{
    public function listBatches(WP_REST_Request $request): array
    {
        return [
            'batches' => (array) get_option('siteforge_ai_batches', []),
        ];
    }

    public function rollback(WP_REST_Request $request): array
    {
        $validated = $this->validate($request, [
            'batch_uuid' => 'required|string',
        ]);

        return [
            'status'     => 'success',
            'batch_uuid' => $validated['batch_uuid'],
            'message'    => __('Rollback stub executed.', 'siteforge-ai'),
        ];
    }
}
