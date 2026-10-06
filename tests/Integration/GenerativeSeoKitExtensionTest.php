<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Integration;

use Nowo\GenerativeSeoKitBundle\DependencyInjection\GenerativeSeoKitExtension;
use Nowo\GenerativeSeoKitBundle\Service\LlmsTxtGenerator;
use Nowo\GenerativeSeoKitBundle\Service\RouteCitationSourceProvider;
use Nowo\GenerativeSeoKitBundle\Service\SeoKitRobotsGroupsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class GenerativeSeoKitExtensionTest extends TestCase
{
    public function testLoadRegistersServices(): void
    {
        $container = new ContainerBuilder();
        $extension = new GenerativeSeoKitExtension();
        self::assertSame('nowo_generative_seo_kit', $extension->getAlias());
        $extension->load([[
            'llms' => [
                'title' => 'Loaded',
            ],
            'citations' => [
                ['title' => 'Home', 'url' => 'https://example.com/'],
            ],
        ]], $container);

        self::assertTrue($container->hasParameter('nowo_generative_seo_kit.enabled'));
        self::assertTrue($container->hasDefinition(LlmsTxtGenerator::class));
        self::assertTrue($container->hasDefinition(SeoKitRobotsGroupsProvider::class));
        self::assertTrue($container->hasDefinition(RouteCitationSourceProvider::class));
        $container->getDefinition(LlmsTxtGenerator::class)->setPublic(true);
        $container->compile();
        $llms = $container->get(LlmsTxtGenerator::class);
        self::assertInstanceOf(LlmsTxtGenerator::class, $llms);
        self::assertStringContainsString('# Loaded', $llms->generate());
    }
}
