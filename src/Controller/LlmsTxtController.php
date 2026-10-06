<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Controller;

use Nowo\GenerativeSeoKitBundle\Service\LlmsTxtGenerator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves llms.txt as text/plain.
 */
final readonly class LlmsTxtController
{
    public function __construct(
        private LlmsTxtGenerator $llms,
    ) {
    }

    public function __invoke(): Response
    {
        if (!$this->llms->isEnabled()) {
            return new Response('Not Found', Response::HTTP_NOT_FOUND, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        return new Response($this->llms->generate(), Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
