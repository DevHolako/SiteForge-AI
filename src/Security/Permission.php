<?php

declare(strict_types=1);

namespace SiteForgeAI\Security;

use WP_Error;
use WP_REST_Request;

class Permission
{
    public static function checkAdmin(WP_REST_Request $request): bool|WP_Error
    {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have sufficient permissions to access this endpoint.', 'siteforge-ai'),
                ['status' => 403]
            );
        }

        return true;
    }
}
