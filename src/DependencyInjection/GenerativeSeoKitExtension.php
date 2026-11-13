<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\DependencyInjection;

use Nowo\GenerativeSeoKitBundle\Service\CitationSourceProviderInterface;
use Nowo\GenerativeSeoKitBundle\Service\SeoKitRobotsGroupsProvider;
use Nowo\SeoKitBundle\Service\GeoRobotsGroupsProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use function interface_exists;

/**
 * Loads nowo_generative_seo_kit configuration and services.
 */
final class GenerativeSeoKitExtension extends Extension
{
    /**
     * @param bool|null $seoKitAvailable Whether nowo-tech/seo-kit-bundle is installed; null detects it (tests force it)
     */
    public function __construct(private readonly ?bool $seoKitAvailable = null)
    {
    }

    /**
     * Returns the configuration alias.
     */
    public function getAlias(): string
    {
        return Configuration::ALIAS;
    }

    /**
     * @param array<array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(Configuration::ALIAS . '.config', $config);
        $container->setParameter(Configuration::ALIAS . '.enabled', $config['enabled']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        // SeoKit bridge only when SeoKit is installed; the robots groups port and the audit work without it.
        if (!($this->seoKitAvailable ?? interface_exists(GeoRobotsGroupsProviderInterface::class))) {
            $container->removeDefinition(SeoKitRobotsGroupsProvider::class);
        }

        $container->registerForAutoconfiguration(CitationSourceProviderInterface::class)
            ->addTag('nowo_generative_seo_kit.citation_source_provider');
    }
}
