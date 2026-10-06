<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Service;

use Nowo\GenerativeSeoKitBundle\Service\ConfigCitationSourceProvider;
use Nowo\GenerativeSeoKitBundle\Service\DefaultCrawlerCatalog;
use Nowo\GenerativeSeoKitBundle\Service\GenerativeSeoAuditor;
use Nowo\GenerativeSeoKitBundle\Service\LlmsTxtGenerator;
use Nowo\GenerativeSeoKitBundle\Service\SeoKitRobotsGroupsProvider;
use PHPUnit\Framework\TestCase;

final class GenerativeSeoAuditorTest extends TestCase
{
    public function testReportsAllProblems(): void
    {
        $llms     = new LlmsTxtGenerator(['enabled' => false]);
        $robots   = new SeoKitRobotsGroupsProvider(['enabled' => false]);
        $auditor  = new GenerativeSeoAuditor($llms, $robots);
        $problems = $auditor->problems();
        self::assertContains('llms.txt is disabled', $problems);
        self::assertContains('no citation sources configured', $problems);
        self::assertContains('no AI crawler robots groups (bundle disabled, robots_bridge off, or empty crawlers)', $problems);
    }

    public function testCleanWhenConfigured(): void
    {
        $config = [
            'enabled'       => true,
            'robots_bridge' => true,
            'llms'          => ['enabled' => true],
            'crawlers'      => DefaultCrawlerCatalog::defaults(),
            'citations'     => [['title' => 'Home', 'url' => 'https://example.com/']],
        ];
        $llms    = new LlmsTxtGenerator($config, [new ConfigCitationSourceProvider($config)]);
        $robots  = new SeoKitRobotsGroupsProvider($config);
        $auditor = new GenerativeSeoAuditor($llms, $robots);
        self::assertSame([], $auditor->problems());
    }
}
