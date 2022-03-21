<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle;

use ETSGlobal\LogBundle\DependencyInjection\CompilerPass\HttpClientPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ETSGlobalLogBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new HttpClientPass());
    }
}
