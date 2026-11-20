<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Kernel;

use Nowo\GenerativeSeoKitBundle\GenerativeSeoKitBundle;
use Nowo\GenerativeSeoKitBundle\Service\SeoKitRobotsGroupsProvider;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

use function dirname;
use function getmypid;
use function sys_get_temp_dir;

/**
 * Zero-config host kernel without SeoKit in `bundles.php` (2.0: nowo-tech/seo-kit-bundle is only suggested): the
 * real extension decides on its own whether the SeoKit bridge is registered. A compiler pass records that decision in
 * {@see self::BRIDGE_PARAMETER} before unused private services are removed.
 */
final class WithoutSeoKitKernel extends Kernel
{
    use MicroKernelTrait;

    /** Container parameter: the bridge definition existed after the extensions were loaded. */
    public const BRIDGE_PARAMETER = 'test.generative_seo_kit.bridge_registered';

    private const CACHE_NAMESPACE = 'nowo-generative-seo-kit-bundle-without-seo-kit';

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

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RecordBridgePass(self::BRIDGE_PARAMETER, SeoKitRobotsGroupsProvider::class), PassConfig::TYPE_BEFORE_REMOVING);
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
            'llms'      => ['title' => 'Without SeoKit'],
            'citations' => [['title' => 'Home', 'url' => 'https://example.com/']],
        ]);
    }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('.', 'nowo_generative_seo_kit');
    }
}
