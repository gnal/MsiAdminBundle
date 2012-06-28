<?php

namespace Msi\Bundle\AdminBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Routing\RouteCollection;

class AdminLoader implements LoaderInterface
{
    private $loaded = false;
    private $adminIds;
    private $container;

    public function __construct($adminIds, $container)
    {
        $this->adminIds = $adminIds;
        $this->container = $container;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $collection = new RouteCollection();

        foreach ($this->adminIds as $id) {
            $admin = $this->container->get($id);
            $collection->addCollection($admin->getRoutes());
        }

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return 'msi_admin' === $type;
    }

    public function getResolver()
    {
    }

    public function setResolver(LoaderResolver $resolver)
    {
    }
}
