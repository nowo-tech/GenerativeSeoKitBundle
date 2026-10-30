<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Kernel;

use Nowo\GenerativeSeoKitBundle\GenerativeSeoKitBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

use function dirname;
use function getmypid;
use function sys_get_temp_dir;

/**
 * Test kernel whose routing is only the Symfony default `controllers: { resource: routing.controllers }`
 * With $importBundleRoutes it also imports the `nowo_generative_seo_kit` route type, as hosts do
 * (REQ-SF-004 rule 7: the bundle routes exist only through that import).
 */
final class HostControllersRoutesKernel extends Kernel
{
    use MicroKernelTrait;

    private const CACHE_NAMESPACE = 'nowo-generative-seo-kit-bundle-routes';

    public function __construct(string $environment, bool $debug, private readonly bool $importBundleRoutes = false)
    {
        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new GenerativeSeoKitBundle();
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__, 2);
    }

    public function getCacheDir(): string
    {
        // One cache per PHPUnit process and routing variant (never reused across runs with other service definitions).
        return sys_get_temp_dir() . '/' . self::CACHE_NAMESPACE . '/' . getmypid() . '/host-controllers-' . ($this->importBundleRoutes ? 'imported-' : '') . $this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/' . self::CACHE_NAMESPACE . '/' . getmypid() . '/log';
    }

    private function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret'                => 'test-secret',
            'test'                  => true,
            'http_method_override'  => false,
            'handle_all_throwables' => true,
            'php_errors'            => ['log' => true],
            'router'                => ['utf8' => true],
            'property_info'         => ['enabled' => false],
        ]);
    }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('routing.controllers');

        if ($this->importBundleRoutes) {
            $routes->import('.', 'nowo_generative_seo_kit');
        }
    }
}
