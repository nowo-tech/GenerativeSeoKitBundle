<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Unit\Service;

use Nowo\GenerativeSeoKitBundle\Routing\LlmsTxtRouteLoader;
use Nowo\GenerativeSeoKitBundle\Service\CitationSourceProviderInterface;
use Nowo\GenerativeSeoKitBundle\Service\ConfigCitationSourceProvider;
use Nowo\GenerativeSeoKitBundle\Service\GeoRuntimeConfigInterface;
use Nowo\GenerativeSeoKitBundle\Service\LlmsHeaderRuntimeInterface;
use Nowo\GenerativeSeoKitBundle\Service\LlmsTxtGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testRuntimeExtraMarkdownIsRenderedBeforeCitations(): void
    {
        $runtime = new class implements GeoRuntimeConfigInterface {
            public function isAiRobotsEnabled(): bool
            {
                return true;
            }

            public function extraAiUserAgents(): array
            {
                return [];
            }

            public function llmsExtraMarkdown(): string
            {
                return "  ## Clinic notes\n\nOpen Monday to Friday.  ";
            }
        };
        $config = [
            'enabled'   => true,
            'llms'      => ['title' => 'Site', 'contact' => 'a@b.c', 'full_body' => 'More'],
            'citations' => [['title' => 'Home', 'url' => 'https://example.com/']],
        ];
        $gen = new LlmsTxtGenerator($config, [new ConfigCitationSourceProvider($config)], $runtime);

        $out = $gen->generate();
        self::assertStringContainsString("Contact: a@b.c\n\n## Clinic notes\n\nOpen Monday to Friday.\n\n## Citations", $out);
        self::assertStringContainsString('Open Monday to Friday.', $gen->generateFull());
    }

    public function testYamlGeoExtraMarkdownIsDefaultSource(): void
    {
        $gen = new LlmsTxtGenerator([
            'enabled' => true,
            'llms'    => ['title' => 'Site'],
            'geo'     => ['llms_extra_markdown' => 'Extra from YAML'],
        ]);
        self::assertStringContainsString("# Site\n\nExtra from YAML\n", $gen->generate());
    }

    public function testNoExtraMarkdownKeepsOutputUnchanged(): void
    {
        $gen = new LlmsTxtGenerator(['enabled' => true, 'llms' => ['title' => 'Site']]);
        self::assertSame("# Site\n", $gen->generate());
    }

    public function testRuntimeHeaderOverridesConfiguredTitleAndSummary(): void
    {
        $config  = ['enabled' => true, 'llms' => ['enabled' => true, 'title' => 'YAML title', 'summary' => 'YAML summary']];
        $runtime = new class implements GeoRuntimeConfigInterface, LlmsHeaderRuntimeInterface {
            public function isAiRobotsEnabled(): bool
            {
                return true;
            }

            public function extraAiUserAgents(): array
            {
                return [];
            }

            public function llmsExtraMarkdown(): string
            {
                return '';
            }

            public function llmsTitle(): string
            {
                return 'Clinic from DB';
            }

            public function llmsSummary(): string
            {
                return '  ';
            }
        };

        $text = (new LlmsTxtGenerator($config, [], $runtime))->generate();

        self::assertStringStartsWith('# Clinic from DB', $text);
        self::assertStringContainsString('> YAML summary', $text, 'Blank runtime value keeps the configured one.');
        self::assertStringNotContainsString('YAML title', $text);
    }

    public function testRuntimeTitleAndSummaryCannotInjectMarkdownSections(): void
    {
        $runtime = new class implements GeoRuntimeConfigInterface, LlmsHeaderRuntimeInterface {
            public function isAiRobotsEnabled(): bool
            {
                return true;
            }

            public function extraAiUserAgents(): array
            {
                return [];
            }

            public function llmsExtraMarkdown(): string
            {
                return '';
            }

            public function llmsTitle(): string
            {
                return "Clinic\n\n## Injected\n- [evil](https://evil.example)";
            }

            public function llmsSummary(): string
            {
                return "Line one\r\n# Fake\tsummary";
            }
        };

        $text = (new LlmsTxtGenerator(['enabled' => true, 'llms' => ['enabled' => true]], [], $runtime))->generate();

        self::assertSame("# Clinic ## Injected - [evil](https://evil.example)\n\n> Line one # Fake summary\n", $text);
    }

    public function testConfiguredTitleAndSummaryAreSingleLines(): void
    {
        $gen = new LlmsTxtGenerator(['enabled' => true, 'llms' => ['title' => "Acme\nCorp", 'summary' => " Café  à\n la carte "]]);

        self::assertSame("# Acme Corp\n\n> Café à la carte\n", $gen->generate());
    }

    /**
     * @return iterable<string, array{array<string, mixed>, ?string}>
     */
    public static function relatedLinkProvider(): iterable
    {
        yield 'distinct full path' => [['full_path' => '/llms-full.txt'], '/llms-full.txt'];
        yield 'full path collides with path' => [['path' => '/ai.txt', 'full_path' => '/ai.txt'], null];
        yield 'full path collides with well-known path' => [['well_known_path' => '/.well-known/llms.txt', 'full_path' => '/.well-known/llms.txt'], null];
        yield 'collision falls back to full well-known path' => [['full_path' => '/llms.txt', 'full_well_known_path' => '/.well-known/llms-full.txt'], '/.well-known/llms-full.txt'];
        yield 'full well-known path also collides' => [['full_path' => '/llms.txt', 'well_known_path' => '/wk.txt', 'full_well_known_path' => '/wk.txt'], null];
    }

    /**
     * @param array<string, mixed> $llms
     */
    #[DataProvider('relatedLinkProvider')]
    public function testRelatedLinkOnlyPointsToARegisteredFullRoute(array $llms, ?string $expected): void
    {
        $config = ['enabled' => true, 'llms' => ['enabled' => true, 'full_enabled' => true] + $llms];
        $index  = (new LlmsTxtGenerator($config))->generate();

        $routes    = (new LlmsTxtRouteLoader($config))->load('.', 'nowo_generative_seo_kit');
        $fullPaths = [];
        foreach ($routes as $route) {
            if ($route->getDefault('_llms_variant') === 'full') {
                $fullPaths[] = $route->getPath();
            }
        }

        if ($expected === null) {
            self::assertStringNotContainsString('## Related', $index);
            self::assertSame([], $fullPaths);

            return;
        }

        self::assertStringContainsString('- [llms-full.txt](' . $expected . ')', $index);
        self::assertContains($expected, $fullPaths);
    }
}
