<?php

namespace Msi\Bundle\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class MenuBuilder extends ContainerAware
{
    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $root = $this->container->get('msi_menu.menu_root_manager')->findRootByName('admin', 'en');

        if (!$root) {
            return $factory->createItem('default');
        }

        $root->setOption('childrenAttributes', array('class' => 'nav'));

        $menu = $factory->createFromNode($root);

        foreach ($menu as $row) {
            $this->checkRole($row);
            if ($row->hasChildren()) {
                $row->setExtra('safe_label', true);
                $row->setLabel($row->getName().' <b class="caret"></b>');
            }
        }

        foreach ($menu->getChildren() as $child) {
            $this->checkRole($child);
            if ($child->hasChildren()) {
                $child->setAttribute('class', 'dropdown');
                $child->setLinkAttribute('class', 'dropdown-toggle');
                $child->setLinkAttribute('data-toggle', 'dropdown');
                $child->setChildrenAttribute('class', 'dropdown-menu');
            }
            foreach ($child->getChildren() as $row) {
                $this->checkRole($row);
                if ($row->hasChildren()) {
                    $row->setAttribute('class', 'dropdown-submenu');
                    $row->setChildrenAttribute('class', 'dropdown-menu');
                    $row->setLinkAttribute('tabindex', -1);
                }
            }
        }

        $this->setCurrent($menu);

        return $menu;
    }

    protected function checkRole($menu)
    {
        if (!is_array($foo = $menu->getExtra('routes'))) {
            return;
        }

        $route = array_shift($foo);
        if (preg_match('@_admin_index$@', $route)) {
            $admin = $this->container->get(substr($route, 0, -6));
            if (!$admin->isGranted('read')) {
                $menu->getParent()->removeChild($menu);
            }
        }
    }

    protected function setCurrent($menu)
    {
        $requestParts = explode('/', $this->container->get('request')->getRequestUri());
        foreach ($menu as $child) {
            $menuParts = explode('/', $child->getUri());
            if (isset($menuParts[4]) && isset($requestParts[4]) && $menuParts[4] === $requestParts[4]) {
                $child->setAttribute('class', 'active');
            }
        }
    }
}
