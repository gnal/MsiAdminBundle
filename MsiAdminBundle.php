<?php

namespace Msi\Bundle\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Msi\Bundle\AdminBundle\DependencyInjection\Compiler\FindAdminIdsPass;

class MsiAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FindAdminIdsPass());
    }
}
