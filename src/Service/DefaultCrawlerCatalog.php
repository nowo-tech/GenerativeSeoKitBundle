<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

/**
 * Default AI crawler policy: allow inference/search bots, disallow training-oriented agents.
 *
 * @phpstan-type CrawlerGroup array{user_agent: string, allow: list<string>, disallow: list<string>}
 */
final class DefaultCrawlerCatalog
{
    /**
     * @return list<CrawlerGroup>
     */
    public static function defaults(): array
    {
        return [
            ['user_agent' => 'GPTBot', 'allow' => ['/'], 'disallow' => []],
            ['user_agent' => 'ChatGPT-User', 'allow' => ['/'], 'disallow' => []],
            ['user_agent' => 'OAI-SearchBot', 'allow' => ['/'], 'disallow' => []],
            ['user_agent' => 'ClaudeBot', 'allow' => ['/'], 'disallow' => []],
            ['user_agent' => 'Claude-User', 'allow' => ['/'], 'disallow' => []],
            ['user_agent' => 'Claude-SearchBot', 'allow' => ['/'], 'disallow' => []],
            ['user_agent' => 'PerplexityBot', 'allow' => ['/'], 'disallow' => []],
            ['user_agent' => 'Google-Extended', 'allow' => [], 'disallow' => ['/']],
            ['user_agent' => 'Applebot-Extended', 'allow' => [], 'disallow' => ['/']],
            ['user_agent' => 'CCBot', 'allow' => [], 'disallow' => ['/']],
            ['user_agent' => 'Bytespider', 'allow' => [], 'disallow' => ['/']],
            ['user_agent' => 'anthropic-ai', 'allow' => [], 'disallow' => ['/']],
        ];
    }
}
