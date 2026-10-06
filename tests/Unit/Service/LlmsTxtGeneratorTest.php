<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Service;

use Nowo\GenerativeSeoKitBundle\Service\CitationSourceProviderInterface;
use Nowo\GenerativeSeoKitBundle\Service\ConfigCitationSourceProvider;
use Nowo\GenerativeSeoKitBundle\Service\LlmsTxtGenerator;
use PHPUnit\Framework\TestCase;

final class LlmsTxtGeneratorTest extends TestCase
{
    public function testDisabledWhenMasterSwitchOff(): void
    {
        $gen = new LlmsTxtGenerator(['enabled' => false, 'llms' => ['enabled' => true]]);
        self::assertFalse($gen->isEnabled());
    }

    public function testDisabledWhenLlmsSwitchOff(): void
    {
        $gen = new LlmsTxtGenerator(['enabled' => true, 'llms' => ['enabled' => false]]);
        self::assertFalse($gen->isEnabled());
    }

    public function testGenerateIncludesTitleSummaryContactAndCitations(): void
    {
        $config = [
            'enabled' => true,
            'llms'    => [
                'enabled'     => true,
                'title'       => 'Acme',
                'summary'     => 'Docs for models',
                'description' => 'Plain-language site map.',
                'contact'     => 'docs@example.com',
            ],
            'citations' => [
                ['title' => 'Home', 'url' => 'https://example.com/', 'notes' => 'Start here'],
                ['title' => 'Skip', 'url' => ''],
            ],
        ];
        $gen = new LlmsTxtGenerator($config, [new ConfigCitationSourceProvider($config)]);
        $txt = $gen->generate();
        self::assertStringContainsString('# Acme', $txt);
        self::assertStringContainsString('> Docs for models', $txt);
        self::assertStringContainsString('Plain-language site map.', $txt);
        self::assertStringContainsString('Contact: docs@example.com', $txt);
        self::assertStringContainsString('[Home](https://example.com/): Start here', $txt);
        self::assertCount(1, $gen->citations());
    }

    public function testDeduplicatesCitationUrlsAndSkipsInvalidRows(): void
    {
        $host = $this->createStub(CitationSourceProviderInterface::class);
        $host->method('sources')->willReturn([
            ['title' => 'A', 'url' => 'https://example.com/a'],
            ['title' => 'A-dup', 'url' => 'https://example.com/a'],
        ]);
        $config = [
            'citations' => [
                'bad',
                ['title' => 'A', 'url' => 'https://example.com/a'],
                ['title' => '', 'url' => 'https://example.com/x'],
            ],
        ];
        $gen = new LlmsTxtGenerator($config, [new ConfigCitationSourceProvider($config), $host]);
        self::assertSame(
            [['title' => 'A', 'url' => 'https://example.com/a', 'notes' => '']],
            $gen->citations(),
        );
        self::assertStringContainsString('# llms.txt', $gen->generate());
    }

    public function testConfigCitationsIgnoredWhenNotArray(): void
    {
        $gen = new LlmsTxtGenerator(['citations' => 'nope'], [new ConfigCitationSourceProvider(['citations' => 'nope'])]);
        self::assertSame([], $gen->citations());
        self::assertTrue($gen->isEnabled());
    }
}
