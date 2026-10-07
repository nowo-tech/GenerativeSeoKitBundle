<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Integration;

use Nowo\GenerativeSeoKitBundle\DependencyInjection\GenerativeSeoKitExtension;
use Nowo\GenerativeSeoKitBundle\Service\ConfigGeoRuntimeConfig;
use Nowo\GenerativeSeoKitBundle\Service\GeoRuntimeConfigInterface;
use Nowo\GenerativeSeoKitBundle\Service\LlmsTxtGenerator;
use Nowo\GenerativeSeoKitBundle\Service\RouteCitationSourceProvider;
use Nowo\GenerativeSeoKitBundle\Service\SeoKitRobotsGroupsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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

    public function testGeoRuntimeConfigDefaultsToYamlAndIsOverridable(): void
    {
        $container = new ContainerBuilder();
        (new GenerativeSeoKitExtension())->load([[
            'geo' => ['extra_ai_user_agents' => ['YamlBot'], 'llms_extra_markdown' => 'From YAML'],
        ]], $container);

        self::assertTrue($container->hasAlias(GeoRuntimeConfigInterface::class));
        $container->getAlias(GeoRuntimeConfigInterface::class)->setPublic(true);
        $container->getDefinition(SeoKitRobotsGroupsProvider::class)->setPublic(true);
        $container->getDefinition(LlmsTxtGenerator::class)->setPublic(true);
        $container->compile();

        self::assertInstanceOf(ConfigGeoRuntimeConfig::class, $container->get(GeoRuntimeConfigInterface::class));
        $robots = $container->get(SeoKitRobotsGroupsProvider::class);
        self::assertInstanceOf(SeoKitRobotsGroupsProvider::class, $robots);
        self::assertContains('YamlBot', array_column($robots->groups(), 'user_agent'));
        $llms = $container->get(LlmsTxtGenerator::class);
        self::assertInstanceOf(LlmsTxtGenerator::class, $llms);
        self::assertStringContainsString('From YAML', $llms->generate());
    }

    public function testHostCanOverrideGeoRuntimeAlias(): void
    {
        $container = new ContainerBuilder();
        (new GenerativeSeoKitExtension())->load([[]], $container);

        $host = new Definition(HostGeoRuntime::class);
        $container->setDefinition(HostGeoRuntime::class, $host);
        $container->setAlias(GeoRuntimeConfigInterface::class, HostGeoRuntime::class)->setPublic(true);
        $container->getDefinition(SeoKitRobotsGroupsProvider::class)->setPublic(true);
        $container->getDefinition(LlmsTxtGenerator::class)->setPublic(true);
        $container->compile();

        $robots = $container->get(SeoKitRobotsGroupsProvider::class);
        self::assertInstanceOf(SeoKitRobotsGroupsProvider::class, $robots);
        self::assertContains('HostBot', array_column($robots->groups(), 'user_agent'));
        $llms = $container->get(LlmsTxtGenerator::class);
        self::assertInstanceOf(LlmsTxtGenerator::class, $llms);
        self::assertStringContainsString('Host notes', $llms->generate());
    }
}

/**
 * Host-style SPI implementation used to verify alias override.
 */
final class HostGeoRuntime implements GeoRuntimeConfigInterface
{
    public function isAiRobotsEnabled(): bool
    {
        return true;
    }

    public function extraAiUserAgents(): array
    {
        return ['HostBot'];
    }

    public function llmsExtraMarkdown(): string
    {
        return 'Host notes';
    }
}
