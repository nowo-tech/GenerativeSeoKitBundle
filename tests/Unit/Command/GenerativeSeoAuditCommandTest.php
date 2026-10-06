<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Command;

use Nowo\GenerativeSeoKitBundle\Command\GenerativeSeoAuditCommand;
use Nowo\GenerativeSeoKitBundle\Service\ConfigCitationSourceProvider;
use Nowo\GenerativeSeoKitBundle\Service\DefaultCrawlerCatalog;
use Nowo\GenerativeSeoKitBundle\Service\GenerativeSeoAuditor;
use Nowo\GenerativeSeoKitBundle\Service\LlmsTxtGenerator;
use Nowo\GenerativeSeoKitBundle\Service\SeoKitRobotsGroupsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class GenerativeSeoAuditCommandTest extends TestCase
{
    public function testFailsWhenProblems(): void
    {
        $tester = $this->tester(['enabled' => false]);
        self::assertSame(Command::FAILURE, $tester->execute([]));
        self::assertStringContainsString('problem', $tester->getDisplay());
    }

    public function testLenientSucceedsWithWarning(): void
    {
        $tester = $this->tester(['enabled' => false]);
        self::assertSame(Command::SUCCESS, $tester->execute(['--lenient' => true]));
        self::assertStringContainsString('problem', $tester->getDisplay());
    }

    public function testSuccessWhenClean(): void
    {
        $config = [
            'enabled'       => true,
            'robots_bridge' => true,
            'llms'          => ['enabled' => true],
            'crawlers'      => DefaultCrawlerCatalog::defaults(),
            'citations'     => [['title' => 'Home', 'url' => 'https://example.com/']],
        ];
        $tester = $this->tester($config);
        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('passed', $tester->getDisplay());
    }

    /**
     * @param array<string, mixed> $config
     */
    private function tester(array $config): CommandTester
    {
        $llms    = new LlmsTxtGenerator($config, [new ConfigCitationSourceProvider($config)]);
        $robots  = new SeoKitRobotsGroupsProvider($config);
        $command = new GenerativeSeoAuditCommand(new GenerativeSeoAuditor($llms, $robots));
        $app     = new Application();
        $app->addCommand($command);

        return new CommandTester($app->find('nowo:generative-seo:audit'));
    }
}
