<?php

declare(strict_types=1);

namespace SiteForgeAI\Controllers;

use WP_REST_Request;

class InstallerController extends BaseController
{
    public function install(WP_REST_Request $request): array
    {
        $validated = $this->validate($request, [
            'type' => 'required|string|in:theme,plugin',
            'slug' => 'required|string',
        ]);

        return [
            'status'  => 'success',
            'package' => $validated['slug'],
            'message' => __('Installer stub executed.', 'siteforge-ai'),
        ];
    }
}
