<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Controller;

use Nowo\GenerativeSeoKitBundle\Controller\LlmsTxtController;
use Nowo\GenerativeSeoKitBundle\Service\LlmsTxtGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LlmsTxtControllerTest extends TestCase
{
    public function testNotFoundWhenDisabled(): void
    {
        $controller = new LlmsTxtController(new LlmsTxtGenerator(['enabled' => false]));
        $response   = $controller();
        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
    }

    public function testOkWhenEnabled(): void
    {
        $controller = new LlmsTxtController(new LlmsTxtGenerator([
            'enabled' => true,
            'llms'    => ['enabled' => true, 'title' => 'Demo'],
        ]));
        $response = $controller();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('# Demo', (string) $response->getContent());
        self::assertSame('noindex', $response->headers->get('X-Robots-Tag'));
    }

    public function testFullNotFoundWhenDisabled(): void
    {
        $request = Request::create('/llms-full.txt');
        $request->attributes->set('_llms_variant', 'full');
        $controller = new LlmsTxtController(new LlmsTxtGenerator([
            'enabled' => true,
            'llms'    => ['enabled' => true, 'full_enabled' => false],
        ]));
        self::assertSame(Response::HTTP_NOT_FOUND, $controller($request)->getStatusCode());
    }

    public function testFullOkWhenEnabled(): void
    {
        $request = Request::create('/llms-full.txt');
        $request->attributes->set('_llms_variant', 'full');
        $controller = new LlmsTxtController(new LlmsTxtGenerator([
            'enabled' => true,
            'llms'    => [
                'enabled'      => true,
                'title'        => 'Demo',
                'full_enabled' => true,
                'full_body'    => 'Expanded notes.',
            ],
        ]));
        $response = $controller($request);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('## Full', (string) $response->getContent());
        self::assertStringContainsString('Expanded notes.', (string) $response->getContent());
    }

    public function testNonStringVariantServesIndex(): void
    {
        $request = Request::create('/llms.txt');
        $request->attributes->set('_llms_variant', 1);
        $controller = new LlmsTxtController(new LlmsTxtGenerator([
            'enabled' => true,
            'llms'    => ['enabled' => true, 'title' => 'Index'],
        ]));
        self::assertStringContainsString('# Index', (string) $controller($request)->getContent());
    }
}
