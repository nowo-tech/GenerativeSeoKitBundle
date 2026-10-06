<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\DependencyInjection;

use Nowo\GenerativeSeoKitBundle\Service\DefaultCrawlerCatalog;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration tree for nowo_generative_seo_kit.
 */
final class Configuration implements ConfigurationInterface
{
    public const ALIAS = 'nowo_generative_seo_kit';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ALIAS);
        /** @var ArrayNodeDefinition $root */
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->booleanNode('robots_bridge')->defaultTrue()->end()
                ->arrayNode('llms')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('path')->defaultValue('/llms.txt')->end()
                        ->scalarNode('well_known_path')->defaultValue('/.well-known/llms.txt')->end()
                        ->scalarNode('title')->defaultValue('')->end()
                        ->scalarNode('summary')->defaultValue('')->end()
                        ->scalarNode('description')->defaultValue('')->end()
                        ->scalarNode('contact')->defaultValue('')->end()
                    ->end()
                ->end()
                ->arrayNode('crawlers')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('user_agent')->isRequired()->cannotBeEmpty()->end()
                            ->arrayNode('allow')
                                ->scalarPrototype()->end()
                                ->defaultValue([])
                            ->end()
                            ->arrayNode('disallow')
                                ->scalarPrototype()->end()
                                ->defaultValue([])
                            ->end()
                        ->end()
                    ->end()
                    ->defaultValue(DefaultCrawlerCatalog::defaults())
                ->end()
                ->arrayNode('citations')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('title')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('url')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('notes')->defaultValue('')->end()
                        ->end()
                    ->end()
                    ->defaultValue([])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
