<?php
declare(strict_types=1);

namespace SiteForgeAI\Controllers;

use WP_REST_Request;

class SeederController extends BaseController
{
    public function run(WP_REST_Request $request): array
    {
        $validated = $this->validate($request, [
            'blueprint' => 'required|array',
        ]);

        return [
            'status'     => 'success',
            'batch_uuid' => 'batch_' . uniqid(),
            'message'    => __('Seeder stub executed.', 'siteforge-ai'),
        ];
    }
}
