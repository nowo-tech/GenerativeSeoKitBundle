<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Routing;

use Nowo\GenerativeSeoKitBundle\Controller\LlmsTxtController;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use function is_array;
use function is_string;

/**
 * Registers /llms.txt and optional /.well-known/llms.txt from configuration.
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

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $type === 'nowo_generative_seo_kit';
    }

    public function load(mixed $resource, ?string $type = null): mixed
    {
        $routes  = new RouteCollection();
        $llms    = is_array($this->config['llms'] ?? null) ? $this->config['llms'] : [];
        $enabled = ($this->config['enabled'] ?? true) === true && ($llms['enabled'] ?? true) === true;

        if (!$enabled) {
            return $routes;
        }

        $path = is_string($llms['path'] ?? null) && $llms['path'] !== '' ? $llms['path'] : '/llms.txt';
        $routes->add('nowo_generative_seo_kit_llms', $this->route($path));

        $wellKnown = is_string($llms['well_known_path'] ?? null) ? $llms['well_known_path'] : '';
        if ($wellKnown !== '' && $wellKnown !== $path) {
            $routes->add('nowo_generative_seo_kit_llms_well_known', $this->route($wellKnown));
        }

        return $routes;
    }

    private function route(string $path): Route
    {
        return new Route(
            $path,
            ['_controller' => LlmsTxtController::class],
            [],
            [],
            '',
            [],
            ['GET'],
        );
    }
}
