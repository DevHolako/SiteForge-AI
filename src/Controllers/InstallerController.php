<?php

declare(strict_types=1);

namespace SiteForgeAI\Controllers;

use SiteForgeAI\Services\Installer\PackageInstallerService;
use SiteForgeAI\Support\Response;
use WP_REST_Request;
use WP_REST_Response;

class InstallerController extends BaseController
{
    public function __construct(
        private readonly PackageInstallerService $installerService
    ) {
    }

    /**
     * Batch install theme and plugins from blueprint.
     * POST /siteforge_ai/v1/installer/install
     */
    public function install(WP_REST_Request $request): WP_REST_Response
    {
        $params = $this->validate($request, [
            'theme'   => 'sometimes|string',
            'plugins' => 'sometimes|array',
        ]);

        $results = $this->installerService->installBatch(
            (array) ($params['plugins'] ?? []),
            isset($params['theme']) ? (string) $params['theme'] : null
        );

        return Response::ok($results);
    }
}
