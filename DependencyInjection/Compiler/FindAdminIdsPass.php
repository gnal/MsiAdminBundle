<?php

namespace Msi\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FindAdminIdsPass implements CompilerPassInterface
{
    function process(ContainerBuilder $container)
    {
        $adminIds = array();

        foreach ($container->findTaggedServiceIds('msi.admin') as $id => $tags) {
            $adminIds[] = $id;
            $admin = $container->getDefinition($id);
            $admin->replaceArgument(0, $id);
        }

        $adminLoader = $container->getDefinition('msi_admin.admin_loader');
        $adminLoader->replaceArgument(0, $adminIds);

        $admin = $container->getDefinition('msi_admin.admin');
        $admin->addMethodCall('setAdminIds', array($adminIds));
    }
}
