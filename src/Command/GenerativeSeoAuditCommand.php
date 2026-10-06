<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Command;

use Nowo\GenerativeSeoKitBundle\Service\GenerativeSeoAuditor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function sprintf;

/**
 * CI-friendly GEO audit for the first slice (llms.txt, citations, crawler policy).
 */
#[AsCommand(
    name: 'nowo:generative-seo:audit',
    description: 'Audit llms.txt, citation sources, and AI crawler robots groups',
)]
final class GenerativeSeoAuditCommand extends Command
{
    public function __construct(
        private readonly GenerativeSeoAuditor $auditor,
    ) {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option(description: 'Report findings without failing the command')]
        bool $lenient = false,
    ): int {
        $problems = $this->auditor->problems();

        if ($problems === []) {
            $io->success('Generative SEO audit passed.');

            return Command::SUCCESS;
        }

        $io->listing($problems);
        $summary = sprintf('%d problem(s) found.', count($problems));

        if ($lenient) {
            $io->warning($summary);

            return Command::SUCCESS;
        }

        $io->error($summary);

        return Command::FAILURE;
    }
}
