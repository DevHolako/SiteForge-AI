<?php

declare(strict_types=1);

namespace SiteForgeAI\Core;

use ReflectionClass;
use ReflectionNamedType;
use SiteForgeAI\Support\Response;
use SiteForgeAI\Support\ValidationException;
use Throwable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class Router
{
    /**
     * Discover route files and register them with the WordPress REST API.
     */
    public static function register(): void
    {
        $route_files = glob(SITEFORGE_AI_DIR . 'src/Routes/*.php');

        if (empty($route_files)) {
            return;
        }

        // 1. Load all route definition files (this populates Route::$routes)
        foreach ($route_files as $file) {
            require_once $file;
        }

        // 2. Register every route with WordPress Core
        foreach (Route::all() as $path => $config) {
            register_rest_route(
                SITEFORGE_AI_REST_NAMESPACE,
                '/' . $path,
                [
                    'methods'             => $config['methods'],
                    'permission_callback' => $config['permission_callback'],
                    'callback'            => function (WP_REST_Request $request) use ($config) {
                        return self::dispatch($request, $config['controller'], $config['action']);
                    },
                ]
            );
        }
    }

    /**
     * Dispatch incoming REST request to the target Controller and Action.
     */
    public static function dispatch(WP_REST_Request $request, string $controller_class, string $action): WP_REST_Response
    {
        try {
            if (!class_exists($controller_class)) {
                return Response::error(sprintf(__('Controller [%s] not found.', 'siteforge-ai'), $controller_class), 500);
            }

            // Auto-wire dependencies via Reflection
            $controller = self::resolve($controller_class);

            if (!method_exists($controller, $action)) {
                return Response::error(sprintf(__('Action [%s] not found in Controller [%s].', 'siteforge-ai'), $action, $controller_class), 500);
            }

            $result = $controller->$action($request);

            if ($result instanceof WP_REST_Response) {
                return $result;
            }

            if ($result instanceof WP_Error) {
                return Response::error($result->get_error_message(), 400);
            }

            return Response::success($result);

        } catch (ValidationException $e) {
            return Response::error($e->getMessage(), 422, $e->getErrors());
        } catch (Throwable $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Resolve a class and auto-wire its constructor dependencies using Reflection.
     */
    public static function resolve(string $class): object
    {
        $reflector = new ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new \RuntimeException(sprintf(__('Class [%s] is not instantiable.', 'siteforge-ai'), $class));
        }

        $constructor = $reflector->getConstructor();

        // If no constructor or no parameters, instantiate directly
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return new $class();
        }

        // Auto-wire constructor dependencies from type hints
        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = self::resolve($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException(sprintf(__('Cannot resolve parameter [%s] in [%s].', 'siteforge-ai'), $param->getName(), $class));
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
