<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Service;

use Nowo\GenerativeSeoKitBundle\Service\RouteCitationSourceProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RouteCitationSourceProviderTest extends TestCase
{
    public function testEmptyWithoutUrlGenerator(): void
    {
        $provider = new RouteCitationSourceProvider([
            'citation_routes' => [
                ['route' => 'app_home', 'title' => 'Home'],
            ],
        ]);
        self::assertSame([], $provider->sources());
    }

    public function testGeneratesAbsoluteUrlsAndSkipsInvalidRows(): void
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->expects(self::exactly(6))
            ->method('generate')
            ->willReturnCallback(static function (string $name, array $parameters, int $referenceType): string {
                self::assertSame(UrlGeneratorInterface::ABSOLUTE_URL, $referenceType);
                if ($name === 'app_home') {
                    self::assertSame([], $parameters);

                    return 'https://example.com/';
                }
                if ($name === 'app_docs') {
                    self::assertSame(['slug' => 'geo'], $parameters);

                    return 'https://example.com/docs/geo';
                }
                if ($name === 'app_empty') {
                    return '';
                }
                if ($name === 'app_note') {
                    self::assertSame([], $parameters);

                    return 'https://example.com/note';
                }
                if ($name === 'app_int') {
                    self::assertSame(['slug' => 'ok'], $parameters);

                    return 'https://example.com/int';
                }

                throw new RouteNotFoundException($name);
            });

        $provider = new RouteCitationSourceProvider([
            'citation_routes' => [
                'bad',
                ['route' => '', 'title' => 'Nope'],
                ['route' => 'missing', 'title' => 'Gone'],
                ['route' => 'app_home', 'title' => 'Home', 'notes' => 'Overview'],
                ['route' => 'app_docs', 'title' => 'Docs', 'parameters' => ['slug' => 'geo']],
                ['route' => 'app_empty', 'title' => 'Empty'],
                ['route' => 'app_note', 'title' => 'Note', 'notes' => 1, 'parameters' => 'bad'],
                ['route' => 'app_int', 'title' => 'Int', 'parameters' => [0 => 'x', 'slug' => 'ok']],
            ],
        ], $router);

        self::assertSame(
            [
                ['title' => 'Home', 'url' => 'https://example.com/', 'notes' => 'Overview'],
                ['title' => 'Docs', 'url' => 'https://example.com/docs/geo', 'notes' => ''],
                ['title' => 'Note', 'url' => 'https://example.com/note', 'notes' => ''],
                ['title' => 'Int', 'url' => 'https://example.com/int', 'notes' => ''],
            ],
            $provider->sources(),
        );
    }

    public function testSkipsWhenCitationRoutesNotArray(): void
    {
        $router   = $this->createStub(UrlGeneratorInterface::class);
        $provider = new RouteCitationSourceProvider(['citation_routes' => 'nope'], $router);
        self::assertSame([], $provider->sources());
    }
}
