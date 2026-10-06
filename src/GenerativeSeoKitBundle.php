<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle;

use Nowo\GenerativeSeoKitBundle\DependencyInjection\GenerativeSeoKitExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Generative Engine Optimization kit: AI crawler policy, llms.txt, citation index, audit.
 */
final class GenerativeSeoKitBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new GenerativeSeoKitExtension();
    }
}
