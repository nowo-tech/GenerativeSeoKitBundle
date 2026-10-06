<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Service;

use Nowo\GenerativeSeoKitBundle\Service\DefaultCrawlerCatalog;
use PHPUnit\Framework\TestCase;

use function array_unique;
use function array_values;
use function in_array;

final class DefaultCrawlerCatalogTest extends TestCase
{
    public function testDefaultsAllowInferenceAndDisallowTraining(): void
    {
        $defaults = DefaultCrawlerCatalog::defaults();
        $agents   = [];
        foreach ($defaults as $group) {
            $agents[] = $group['user_agent'];
            if (in_array($group['user_agent'], ['GPTBot', 'ChatGPT-User', 'OAI-SearchBot', 'ClaudeBot', 'Claude-User', 'Claude-SearchBot', 'PerplexityBot'], true)) {
                self::assertSame(['/'], $group['allow']);
                self::assertSame([], $group['disallow']);
            }
            if (in_array($group['user_agent'], ['Google-Extended', 'Applebot-Extended', 'CCBot', 'Bytespider', 'anthropic-ai'], true)) {
                self::assertSame([], $group['allow']);
                self::assertSame(['/'], $group['disallow']);
            }
        }

        self::assertSame($agents, array_values(array_unique($agents)));
        self::assertContains('ChatGPT-User', $agents);
        self::assertContains('Claude-SearchBot', $agents);
        self::assertContains('Bytespider', $agents);
    }
}
