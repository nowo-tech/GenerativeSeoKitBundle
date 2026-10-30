<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Controller;

use Nowo\GenerativeSeoKitBundle\Routing\LlmsTxtRouteLoader;
use Nowo\GenerativeSeoKitBundle\Service\LlmsTxtGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function is_string;

/**
 * Serves llms.txt / optional llms-full.txt as text/plain.
 *
 * The routes are declared with attributes; {@see LlmsTxtRouteLoader}
 * (route type `nowo_generative_seo_kit`) reads them, applies the configured paths and drops the disabled ones.
 * The service is not autoconfigured (no #[AsController]): the routes exist only where the host imports that
 * route type, never through the host's `routing.controllers` resource (REQ-SF-004 rule 7).
 */
#[Route(path: '/llms.txt', name: self::ROUTE_INDEX, defaults: ['_llms_variant' => 'index'], methods: ['GET'])]
#[Route(path: '/.well-known/llms.txt', name: self::ROUTE_INDEX_WELL_KNOWN, defaults: ['_llms_variant' => 'index'], methods: ['GET'])]
#[Route(path: '/llms-full.txt', name: self::ROUTE_FULL, defaults: ['_llms_variant' => 'full'], methods: ['GET'])]
#[Route(path: '/.well-known/llms-full.txt', name: self::ROUTE_FULL_WELL_KNOWN, defaults: ['_llms_variant' => 'full'], methods: ['GET'])]
final readonly class LlmsTxtController
{
    public const ROUTE_INDEX            = 'nowo_generative_seo_kit_llms';
    public const ROUTE_INDEX_WELL_KNOWN = 'nowo_generative_seo_kit_llms_well_known';
    public const ROUTE_FULL             = 'nowo_generative_seo_kit_llms_full';
    public const ROUTE_FULL_WELL_KNOWN  = 'nowo_generative_seo_kit_llms_full_well_known';

    /**
     * Uses the llms.txt generator.
     */
    public function __construct(
        private LlmsTxtGenerator $llms,
    ) {
    }

    /**
     * Serves llms.txt (or llms-full.txt) as plain text with X-Robots-Tag: noindex.
     */
    public function __invoke(?Request $request = null): Response
    {
        $raw     = $request?->attributes->get('_llms_variant', 'index') ?? 'index';
        $variant = is_string($raw) ? $raw : 'index';
        $full    = $variant === 'full';

        if (!$this->llms->isEnabled() || ($full && !$this->llms->isFullEnabled())) {
            return new Response('Not Found', Response::HTTP_NOT_FOUND, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        $body = $full ? $this->llms->generateFull() : $this->llms->generate();

        return new Response($body, Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
