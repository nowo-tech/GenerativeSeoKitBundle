<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\DependencyInjection;

use Nowo\GenerativeSeoKitBundle\Service\CitationSourceProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Loads nowo_generative_seo_kit configuration and services.
 */
final class GenerativeSeoKitExtension extends Extension
{
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

        $container->registerForAutoconfiguration(CitationSourceProviderInterface::class)
            ->addTag('nowo_generative_seo_kit.citation_source_provider');
    }
}
