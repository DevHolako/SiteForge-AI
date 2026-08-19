<?php

declare(strict_types=1);

namespace SiteForgeAI\Support;

use WP_REST_Response;

class Response
{
    /**
     * Return a standardized success response envelope.
     */
    public static function success(mixed $data = null, int $status = 200): WP_REST_Response
    {
        return new WP_REST_Response(
            [
                'ok' => true,
                'data' => $data,
            ],
            $status
        );
    }

    /**
     * Alias for Response::success().
     */
    public static function ok(mixed $data = null, int $status = 200): WP_REST_Response
    {
        return self::success($data, $status);
    }

    /**
     * Return a standardized error response envelope.
     */
    public static function error(string $message, int $status = 400, mixed $details = null): WP_REST_Response
    {
        $payload = [
            'ok' => false,
            'error' => $message,
        ];

        if ($details !== null) {
            $payload['details'] = $details;
        }

        return new WP_REST_Response($payload, $status);
    }
}
