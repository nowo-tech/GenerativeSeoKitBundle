<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Integration\Worker;

use Nowo\GenerativeSeoKitBundle\Tests\Kernel\WorkerKeptAliveKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

use function gc_collect_cycles;
use function memory_get_usage;
use function sprintf;
use function str_contains;

/**
 * REQ-RUNTIME-002: ONE kernel, `services_resetter` / `kernel.reset` never called (FrankenPHP worker, kernel reset
 * false). N iterations alternate two sites (hosts) through `http_kernel` handle() + terminate(): the llms.txt page
 * lists a route citation as an absolute URL, so each response must carry its own host and nothing of the other
 * one, and memory must stay flat.
 */
final class WorkerKeptAliveKernelTest extends TestCase
{
    private const ITERATIONS = 1200;
    private const WARM_UP    = 100;
    private const MARGIN     = 4 * 1024 * 1024;

    public function testAlternatingSitesInOneKernelLeakNothingAndKeepMemoryFlat(): void
    {
        $kernel = new WorkerKeptAliveKernel('test', false);
        $kernel->boot();

        try {
            $httpKernel = $kernel->getContainer()->get('http_kernel');
            self::assertInstanceOf(HttpKernelInterface::class, $httpKernel);
            self::assertInstanceOf(TerminableInterface::class, $httpKernel);

            $baseline = 0;
            for ($i = 0; $i < self::ITERATIONS; ++$i) {
                if ($i === self::WARM_UP) {
                    gc_collect_cycles();
                    $baseline = memory_get_usage();
                }

                [$mine, $other] = $i % 2 === 0 ? ['alpha.example', 'beta.example'] : ['beta.example', 'alpha.example'];
                $path           = $i % 4 < 2 ? '/llms.txt' : '/.well-known/llms.txt';

                $request  = Request::create('https://' . $mine . $path);
                $response = $httpKernel->handle($request);
                $content  = (string) $response->getContent();

                self::assertSame(200, $response->getStatusCode(), sprintf('iteration %d', $i));
                self::assertTrue(str_contains($content, 'Kept alive'), sprintf('iteration %d', $i));
                self::assertTrue(str_contains($content, 'https://' . $mine . '/llms.txt'), sprintf('iteration %d: own host missing', $i));
                self::assertFalse(str_contains($content, $other), sprintf('iteration %d: other site data present', $i));
                $httpKernel->terminate($request, $response);
            }

            gc_collect_cycles();
            $growth = memory_get_usage() - $baseline;
            self::assertLessThan(self::MARGIN, $growth, sprintf('memory grew by %d bytes over %d iterations', $growth, self::ITERATIONS - self::WARM_UP));
        } finally {
            $kernel->shutdown();
        }
    }
}
