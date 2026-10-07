<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Service;

use Nowo\GenerativeSeoKitBundle\Service\ConfigGeoRuntimeConfig;
use PHPUnit\Framework\TestCase;

final class ConfigGeoRuntimeConfigTest extends TestCase
{
    public function testDefaultsWhenGeoNodeMissing(): void
    {
        $config = new ConfigGeoRuntimeConfig();
        self::assertTrue($config->isAiRobotsEnabled());
        self::assertSame([], $config->extraAiUserAgents());
        self::assertSame('', $config->llmsExtraMarkdown());
    }

    public function testReadsGeoNodeAndNormalizesValues(): void
    {
        $config = new ConfigGeoRuntimeConfig(['geo' => [
            'ai_robots_enabled'    => false,
            'extra_ai_user_agents' => [' MyBot ', '', 1, 'Other'],
            'llms_extra_markdown'  => "  Notes \n",
        ]]);
        self::assertFalse($config->isAiRobotsEnabled());
        self::assertSame(['MyBot', 'Other'], $config->extraAiUserAgents());
        self::assertSame('Notes', $config->llmsExtraMarkdown());
    }

    public function testInvalidShapesFallBackToDefaults(): void
    {
        $config = new ConfigGeoRuntimeConfig(['geo' => ['extra_ai_user_agents' => 'x', 'llms_extra_markdown' => []]]);
        self::assertSame([], $config->extraAiUserAgents());
        self::assertSame('', $config->llmsExtraMarkdown());
        self::assertTrue((new ConfigGeoRuntimeConfig(['geo' => 'bad']))->isAiRobotsEnabled());
    }
}
