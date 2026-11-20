<?php

declare(strict_types=1);

namespace Nowo\GenerativeSeoKitBundle\Tests\Kernel;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test-only pass: stores whether a service definition exists before the container removes unused private services.
 */
final readonly class RecordBridgePass implements CompilerPassInterface
{
    public function __construct(
        private string $parameter,
        private string $serviceId,
    ) {
    }

    public function process(ContainerBuilder $container): void
    {
        $container->setParameter($this->parameter, $container->hasDefinition($this->serviceId));
    }
}
