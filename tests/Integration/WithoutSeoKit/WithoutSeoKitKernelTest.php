<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Integration\WithoutSeoKit;

use Nowo\GenerativeSeoKitBundle\Service\AiRobotsGroupsProviderInterface;
use Nowo\GenerativeSeoKitBundle\Service\CrawlerRobotsGroupsProvider;
use Nowo\GenerativeSeoKitBundle\Tests\Kernel\WithoutSeoKitKernel;
use Nowo\SeoKitBundle\Service\GeoRobotsGroupsProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use function getenv;
use function interface_exists;

/**
 * 2.0 (REQ-ARCH-001 rule 8): nowo-tech/seo-kit-bundle is only suggested. The real extension (no forced
 * `seoKitAvailable`) boots a zero-config host without SeoKit: llms.txt is served, the audit runs on the bundle-owned
 * robots groups port, and the SeoKit bridge is registered only when SeoKit's interface exists.
 *
 * The CI job "Without SeoKit" removes the package and sets GENERATIVE_SEO_KIT_EXPECT_NO_SEO_KIT=1, so this test then
 * also proves that SeoKit is really absent; in the full suite (SeoKit in require-dev) the bridge is registered.
 */
final class WithoutSeoKitKernelTest extends TestCase
{
    private ?WithoutSeoKitKernel $kernel = null;

    protected function tearDown(): void
    {
        $this->kernel?->shutdown();
        $this->kernel = null;
    }

    public function testBootsServesLlmsTxtAndAuditsWithTheBridgeOnlyWhenSeoKitExists(): void
    {
        $seoKit = interface_exists(GeoRobotsGroupsProviderInterface::class);
        if (getenv('GENERATIVE_SEO_KIT_EXPECT_NO_SEO_KIT') === '1') {
            self::assertFalse($seoKit, 'nowo-tech/seo-kit-bundle must not be installed in the "Without SeoKit" job.');
        }

        $this->kernel = new WithoutSeoKitKernel('test', false);
        $this->kernel->boot();
        $container = $this->kernel->getContainer();

        self::assertSame($seoKit, $container->getParameter(WithoutSeoKitKernel::BRIDGE_PARAMETER));

        $testContainer = $container->get('test.service_container');
        self::assertInstanceOf(ContainerInterface::class, $testContainer);
        self::assertInstanceOf(CrawlerRobotsGroupsProvider::class, $testContainer->get(AiRobotsGroupsProviderInterface::class));

        $httpKernel = $container->get('http_kernel');
        self::assertInstanceOf(HttpKernelInterface::class, $httpKernel);
        $response = $httpKernel->handle(Request::create('https://example.com/llms.txt'));
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('# Without SeoKit', (string) $response->getContent());

        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $tester = new CommandTester($application->find('nowo:generative-seo:audit'));
        self::assertSame(Command::SUCCESS, $tester->execute([]), $tester->getDisplay());
        self::assertStringContainsString('passed', $tester->getDisplay());
    }
}
