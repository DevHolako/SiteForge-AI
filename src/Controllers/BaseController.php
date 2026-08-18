<?php

declare(strict_types=1);

namespace SiteForgeAI\Controllers;

use SiteForgeAI\Support\ValidationException;
use SiteForgeAI\Support\Validator;
use WP_REST_Request;

abstract class BaseController
{
    /**
     * Get a single parameter from the request.
     */
    protected function getParam(WP_REST_Request $request, string $key, mixed $default = null): mixed
    {
        return $request->get_param($key) ?? $default;
    }

    /**
     * Get all parameters from the request.
     */
    protected function getParams(WP_REST_Request $request): array
    {
        return (array) $request->get_params();
    }

    /**
     * Get JSON body parameters.
     */
    protected function getJsonParams(WP_REST_Request $request): array
    {
        return (array) $request->get_json_params();
    }

    /**
     * Validate the request parameters against rules.
     * Throws ValidationException (422) if validation fails.
     * Returns the validated params array on success.
     */
    protected function validate(WP_REST_Request $request, array $rules): array
    {
        $params    = $this->getParams($request);
        $validator = Validator::make($params, $rules);

        if ($validator->fails()) {
            throw new ValidationException(
                $validator->firstError() ?? __('Validation failed.', 'siteforge-ai'),
                $validator->errors()
            );
        }

        return $params;
    }
}
