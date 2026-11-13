<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Integration;

use Nowo\GenerativeSeoKitBundle\DependencyInjection\GenerativeSeoKitExtension;
use Nowo\GenerativeSeoKitBundle\Service\AiRobotsGroupsProviderInterface;
use Nowo\GenerativeSeoKitBundle\Service\ConfigGeoRuntimeConfig;
use Nowo\GenerativeSeoKitBundle\Service\CrawlerRobotsGroupsProvider;
use Nowo\GenerativeSeoKitBundle\Service\GenerativeSeoAuditor;
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

    public function testWithoutSeoKitTheBridgeIsNotRegisteredAndTheAuditStillWorks(): void
    {
        $container = new ContainerBuilder();
        (new GenerativeSeoKitExtension(seoKitAvailable: false))->load([[
            'citations' => [['title' => 'Home', 'url' => 'https://example.com/']],
        ]], $container);

        self::assertFalse($container->hasDefinition(SeoKitRobotsGroupsProvider::class));
        $container->getDefinition(GenerativeSeoAuditor::class)->setPublic(true);
        $container->getAlias(AiRobotsGroupsProviderInterface::class)->setPublic(true);
        $container->compile();

        self::assertInstanceOf(CrawlerRobotsGroupsProvider::class, $container->get(AiRobotsGroupsProviderInterface::class));
        $auditor = $container->get(GenerativeSeoAuditor::class);
        self::assertInstanceOf(GenerativeSeoAuditor::class, $auditor);
        self::assertSame([], $auditor->problems());
    }

    public function testWithSeoKitTheBridgeForwardsTheDefaultPort(): void
    {
        $container = new ContainerBuilder();
        (new GenerativeSeoKitExtension(seoKitAvailable: true))->load([[]], $container);

        $container->getDefinition(SeoKitRobotsGroupsProvider::class)->setPublic(true);
        $container->getAlias(AiRobotsGroupsProviderInterface::class)->setPublic(true);
        $container->compile();

        $bridge = $container->get(SeoKitRobotsGroupsProvider::class);
        $port   = $container->get(AiRobotsGroupsProviderInterface::class);
        self::assertInstanceOf(SeoKitRobotsGroupsProvider::class, $bridge);
        self::assertInstanceOf(AiRobotsGroupsProviderInterface::class, $port);
        self::assertNotSame([], $port->groups());
        self::assertSame($port->groups(), $bridge->groups());
    }

    public function testHostCanOverrideTheRobotsGroupsPortAlias(): void
    {
        $container = new ContainerBuilder();
        (new GenerativeSeoKitExtension())->load([[]], $container);

        $container->setDefinition(HostRobotsGroups::class, new Definition(HostRobotsGroups::class));
        $container->setAlias(AiRobotsGroupsProviderInterface::class, HostRobotsGroups::class);
        $container->getDefinition(SeoKitRobotsGroupsProvider::class)->setPublic(true);
        $container->getDefinition(GenerativeSeoAuditor::class)->setPublic(true);
        $container->compile();

        $bridge = $container->get(SeoKitRobotsGroupsProvider::class);
        self::assertInstanceOf(SeoKitRobotsGroupsProvider::class, $bridge);
        self::assertSame([['user_agent' => 'HostBot', 'allow' => ['/'], 'disallow' => []]], $bridge->groups());
        $auditor = $container->get(GenerativeSeoAuditor::class);
        self::assertInstanceOf(GenerativeSeoAuditor::class, $auditor);
        self::assertNotContains('no AI crawler robots groups (bundle disabled, robots_bridge off, or empty crawlers)', $auditor->problems());
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

/**
 * Host-style robots groups port used to verify alias override.
 */
final class HostRobotsGroups implements AiRobotsGroupsProviderInterface
{
    public function groups(): array
    {
        return [['user_agent' => 'HostBot', 'allow' => ['/'], 'disallow' => []]];
    }
}
