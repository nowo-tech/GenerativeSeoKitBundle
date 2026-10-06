<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Controller;

use Nowo\GenerativeSeoKitBundle\Service\LlmsTxtGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function is_string;

/**
 * Serves llms.txt / optional llms-full.txt as text/plain.
 */
final readonly class LlmsTxtController
{
    public function __construct(
        private LlmsTxtGenerator $llms,
    ) {
    }

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
