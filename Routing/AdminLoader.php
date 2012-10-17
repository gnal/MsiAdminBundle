<?php

namespace Msi\Bundle\AdminBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

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
            $collection->addCollection($this->buildRoutes($admin));
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

    public function setResolver(LoaderResolverInterface $resolver)
    {
    }

    protected function buildRoutes($admin)
    {
        $collection = new RouteCollection();

        if ($admin->getOption('url_namespace')) {

        } else {
            $namespace = preg_replace(array('@_admin$@', '@^[a-z]+_[a-z]+_@'), array('', ''), $admin->getAdminId());
            $namespace = preg_replace('@_@', '-', $namespace).'s';
        }

        $prefix = '/{_locale}/admin/'.$namespace.'/';
        $suffix = '';

        $names = array(
            'index',
            'new',
            'edit',
            'delete',
            'change',
            'sort',
            'removeFile',
        );

        foreach ($names as $name) {
            $collection->add(
                $admin->getAdminId().'_'.$name,
                new Route(
                    $prefix.$name.$suffix,
                    array(
                        '_controller' => $admin->getOption('controller').$name,
                        '_admin' => $admin->getAdminId(),
                    )
                )
            );
        }

        $collection->add(
            $admin->getAdminId().'_index',
            new Route(
                $prefix.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'index',
                    '_admin' => $admin->getAdminId(),
                ),
                array(
                    '_method' => 'GET',
                )
            )
        );

        $collection->add(
            $admin->getAdminId().'_new',
            new Route(
                $prefix.'new'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'new',
                    '_admin' => $admin->getAdminId(),
                ),
                array(
                    '_method' => 'GET|POST',
                )
            )
        );

        $collection->add(
            $admin->getAdminId().'_edit',
            new Route(
                $prefix.'{id}'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'edit',
                    '_admin' => $admin->getAdminId(),
                ),
                array(
                    '_method' => 'GET|PUT',
                )
            )
        );

        $collection->add(
            $admin->getAdminId().'_delete',
            new Route(
                $prefix.'{id}'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'delete',
                    '_admin' => $admin->getAdminId(),
                ),
                array(
                    '_method' => 'DELETE',
                )
            )
        );

        return $collection;
    }
}
