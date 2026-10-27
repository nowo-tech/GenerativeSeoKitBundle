<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Routing;

use Nowo\GenerativeSeoKitBundle\Controller\LlmsTxtController;
use ReflectionClass;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Attribute\Route as RouteAttribute;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use function in_array;
use function is_array;
use function is_string;

/**
 * Loads the #[Route] attributes of {@see LlmsTxtController} (/llms.txt, optional well-known,
 * opt-in llms-full.txt) and applies the configured paths: attributes declare the routes,
 * configuration only moves or drops them.
 */
final class LlmsTxtRouteLoader extends Loader
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private readonly array $config,
        ?string $env = null,
    ) {
        parent::__construct($env);
    }

    /**
     * Whether the resource is handled by this loader (its dedicated route type).
     */
    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $type === 'nowo_generative_seo_kit';
    }

    /**
     * Builds the llms.txt / llms-full.txt routes from the controller's #[Route] attributes,
     * on the configured paths; disabled or colliding routes are left out.
     */
    public function load(mixed $resource, ?string $type = null): mixed
    {
        $routes = new RouteCollection();
        $paths  = $this->configuredPaths();

        foreach ((new ReflectionClass(LlmsTxtController::class))->getAttributes(RouteAttribute::class) as $attribute) {
            $declared = $attribute->newInstance();
            $name     = (string) $declared->name;
            $path     = $paths[$name] ?? null;

            if ($path === null) {
                continue;
            }

            $routes->add($name, new Route(
                $path,
                ['_controller' => LlmsTxtController::class] + $declared->defaults,
                [],
                [],
                '',
                [],
                $declared->methods,
            ));
        }

        return $routes;
    }

    /**
     * Configured path per route name; a route without an entry is not registered.
     *
     * @return array<string, string>
     */
    private function configuredPaths(): array
    {
        $llms    = is_array($this->config['llms'] ?? null) ? $this->config['llms'] : [];
        $enabled = ($this->config['enabled'] ?? true) === true && ($llms['enabled'] ?? true) === true;

        if (!$enabled) {
            return [];
        }

        $path  = is_string($llms['path'] ?? null) && $llms['path'] !== '' ? $llms['path'] : '/llms.txt';
        $paths = [LlmsTxtController::ROUTE_INDEX => $path];

        $wellKnown = is_string($llms['well_known_path'] ?? null) ? $llms['well_known_path'] : '';
        if ($wellKnown !== '' && $wellKnown !== $path) {
            $paths[LlmsTxtController::ROUTE_INDEX_WELL_KNOWN] = $wellKnown;
        }

        if (($llms['full_enabled'] ?? false) === true) {
            $fullPath = is_string($llms['full_path'] ?? null) && $llms['full_path'] !== '' ? $llms['full_path'] : '/llms-full.txt';
            if ($fullPath !== $path && $fullPath !== $wellKnown) {
                $paths[LlmsTxtController::ROUTE_FULL] = $fullPath;
            }

            $fullWellKnown = is_string($llms['full_well_known_path'] ?? null) ? $llms['full_well_known_path'] : '';
            if (!in_array($fullWellKnown, ['', $fullPath, $path, $wellKnown], true)) {
                $paths[LlmsTxtController::ROUTE_FULL_WELL_KNOWN] = $fullWellKnown;
            }
        }

        return $paths;
    }
}
