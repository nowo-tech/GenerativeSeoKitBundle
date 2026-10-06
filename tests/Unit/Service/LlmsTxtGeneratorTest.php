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
        self::assertFalse($gen->isFullEnabled());
    }

    public function testSectionsOptionalAndRelatedFullDocument(): void
    {
        $config = [
            'enabled' => true,
            'llms'    => [
                'enabled'      => true,
                'title'        => 'Acme',
                'full_enabled' => true,
                'full_path'    => '/llms-full.txt',
                'full_body'    => 'Longer context for models.',
                'sections'     => [
                    'bad',
                    ['heading' => '', 'body' => 'skip'],
                    ['heading' => 'Docs', 'body' => '', 'links' => []],
                    ['heading' => 'Docs', 'body' => 'Read the handbook.', 'links' => [
                        'bad',
                        ['title' => 'Handbook', 'url' => 'https://example.com/docs', 'notes' => 'Start'],
                    ]],
                    ['heading' => 'EmptyLinks', 'body' => 'Only body.', 'links' => 'nope'],
                ],
                'optional_links' => [
                    'bad',
                    ['title' => '', 'url' => 'https://example.com/x'],
                    ['title' => 'Changelog', 'url' => 'https://example.com/changelog', 'notes' => 1],
                ],
            ],
        ];
        $gen = new LlmsTxtGenerator($config);
        self::assertTrue($gen->isFullEnabled());
        $index = $gen->generate();
        self::assertStringContainsString('## Docs', $index);
        self::assertStringContainsString('Read the handbook.', $index);
        self::assertStringContainsString('[Handbook](https://example.com/docs): Start', $index);
        self::assertStringContainsString('## EmptyLinks', $index);
        self::assertStringContainsString('Only body.', $index);
        self::assertStringContainsString('## Optional', $index);
        self::assertStringContainsString('[Changelog](https://example.com/changelog)', $index);
        self::assertStringContainsString('## Related', $index);
        self::assertStringContainsString('[llms-full.txt](/llms-full.txt)', $index);
        self::assertStringNotContainsString('## Full', $index);

        $full = $gen->generateFull();
        self::assertStringContainsString('## Full', $full);
        self::assertStringContainsString('Longer context for models.', $full);
        self::assertStringNotContainsString('## Related', $full);
    }

    public function testFullDisabledWhenMasterOffAndIgnoresInvalidSections(): void
    {
        $off = new LlmsTxtGenerator(['enabled' => false, 'llms' => ['full_enabled' => true]]);
        self::assertFalse($off->isFullEnabled());

        $gen = new LlmsTxtGenerator([
            'enabled' => true,
            'llms'    => [
                'enabled'        => true,
                'sections'       => 'nope',
                'optional_links' => 'nope',
                'full_enabled'   => true,
                'full_path'      => '',
                'full_body'      => '',
            ],
        ]);
        $txt = $gen->generate();
        self::assertStringContainsString('[llms-full.txt](/llms-full.txt)', $txt);
        self::assertStringNotContainsString('## Optional', $txt);
        $full = $gen->generateFull();
        self::assertStringNotContainsString('## Full', $full);
        self::assertStringNotContainsString('## Related', $full);
    }
}
