<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Service;

/**
 * Static checks for the first GEO slice (policy + llms.txt + citations).
 */
final readonly class GenerativeSeoAuditor
{
    public function __construct(
        private LlmsTxtGenerator $llms,
        private SeoKitRobotsGroupsProvider $robots,
    ) {
    }

    /**
     * @return list<string>
     */
    public function problems(): array
    {
        $problems = [];

        if (!$this->llms->isEnabled()) {
            $problems[] = 'llms.txt is disabled';
        }

        if ($this->llms->citations() === []) {
            $problems[] = 'no citation sources configured';
        }

        if ($this->robots->groups() === []) {
            $problems[] = 'no AI crawler robots groups (bundle disabled, robots_bridge off, or empty crawlers)';
        }

        return $problems;
    }
}
