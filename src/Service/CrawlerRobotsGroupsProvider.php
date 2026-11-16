<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

use Psr\Log\LoggerInterface;

use function is_array;
use function is_string;
use function preg_match;
use function strlen;
use function strtolower;
use function trim;

/**
 * Default {@see AiRobotsGroupsProviderInterface}: AI crawler robots.txt groups from this bundle's crawler policy.
 *
 * Plain PHP, no SeoKit dependency. The rules are the ones {@see SeoKitRobotsGroupsProvider} applied before 1.7.0
 * (bundle `enabled`, `robots_bridge`, runtime toggle, crawler catalog, deduplicated extra agents).
 *
 * Optional {@see GeoRuntimeConfigInterface} toggles the whole bridge and appends host-provided extra agents.
 * Extra agents are runtime (possibly admin- or DB-provided) values, so each one must be a single robots.txt product
 * token ({@see self::USER_AGENT_TOKEN}): a value with a newline, a control character or any other character outside
 * the token set is dropped with a warning, so it cannot add its own lines (e.g. `Disallow: /`) to robots.txt.
 *
 * @phpstan-type RobotsGroup array{user_agent: string, allow?: list<string>, disallow?: list<string>}
 */
final readonly class CrawlerRobotsGroupsProvider implements AiRobotsGroupsProviderInterface
{
    /**
     * Accepted extra user-agent token: letters, digits, `-`, `_`, `.` and `/`, starting with a letter or digit,
     * at most 128 characters. No whitespace, control characters, `:` or `#`.
     */
    public const USER_AGENT_TOKEN = '/^[A-Za-z0-9][A-Za-z0-9._\/-]*$/D';

    private const USER_AGENT_MAX_LENGTH = 128;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        private array $config,
        private ?GeoRuntimeConfigInterface $runtime = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * AI crawler robots.txt groups (empty when disabled or when the robots bridge is off).
     */
    public function groups(): array
    {
        if (($this->config['enabled'] ?? true) !== true) {
            return [];
        }
        if (($this->config['robots_bridge'] ?? true) !== true) {
            return [];
        }

        $runtime = $this->runtime ?? new ConfigGeoRuntimeConfig($this->config);
        if (!$runtime->isAiRobotsEnabled()) {
            return [];
        }

        $crawlers = $this->config['crawlers'] ?? [];
        if (!is_array($crawlers)) {
            return [];
        }

        $groups = [];
        $owned  = [];
        foreach ($crawlers as $row) {
            if (!is_array($row)) {
                continue;
            }
            $agent = $row['user_agent'] ?? '';
            if (!is_string($agent) || $agent === '') {
                continue;
            }
            $owned[strtolower($agent)] = true;
            $allow                     = $this->stringList($row['allow'] ?? []);
            $disallow                  = $this->stringList($row['disallow'] ?? []);
            $groups[]                  = [
                'user_agent' => $agent,
                'allow'      => $allow,
                'disallow'   => $disallow,
            ];
        }

        foreach ($runtime->extraAiUserAgents() as $extra) {
            // Only blanks are trimmed: a trailing CR, LF or NUL makes the value invalid instead of being cut silently.
            $extra = trim($extra, " \t");
            if ($extra === '') {
                continue;
            }
            if (!self::isValidUserAgentToken($extra)) {
                $this->logger?->warning('nowo_generative_seo_kit: dropped an invalid extra AI user agent from robots.txt (only letters, digits, "-", "_", ".", "/"; at most 128 characters).', [
                    'length' => strlen($extra),
                ]);

                continue;
            }
            $key = strtolower($extra);
            if (isset($owned[$key])) {
                continue;
            }
            $owned[$key] = true;
            $groups[]    = [
                'user_agent' => $extra,
                'allow'      => ['/'],
                'disallow'   => [],
            ];
        }

        return $groups;
    }

    /**
     * Whether a value is a single robots.txt user-agent product token (no line breaks, no control characters).
     */
    public static function isValidUserAgentToken(string $value): bool
    {
        return strlen($value) <= self::USER_AGENT_MAX_LENGTH && preg_match(self::USER_AGENT_TOKEN, $value) === 1;
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $out = [];
        foreach ($value as $item) {
            if (is_string($item) && $item !== '') {
                $out[] = $item;
            }
        }

        return $out;
    }
}
