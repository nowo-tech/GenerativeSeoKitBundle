<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Kernel;

use Nowo\GenerativeSeoKitBundle\Controller\LlmsTxtController;
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
 * Host-like kernel for the kept-alive worker test: the `nowo_generative_seo_kit` route type is imported and the
 * llms.txt page lists a citation generated from a route as an absolute URL (it depends on the request host).
 */
final class WorkerKeptAliveKernel extends Kernel
{
    use MicroKernelTrait;

    private const CACHE_NAMESPACE = 'nowo-generative-seo-kit-bundle-worker';

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
        return sys_get_temp_dir() . '/' . self::CACHE_NAMESPACE . '/' . getmypid() . '/' . $this->environment;
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
        $container->extension('nowo_generative_seo_kit', [
            'llms'            => ['title' => 'Kept alive'],
            'citation_routes' => [['route' => LlmsTxtController::ROUTE_INDEX, 'title' => 'Home llms']],
        ]);
    }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('.', 'nowo_generative_seo_kit');
    }
}
