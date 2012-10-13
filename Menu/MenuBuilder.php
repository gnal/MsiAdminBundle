<?php

namespace Msi\Bundle\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class MenuBuilder extends ContainerAware
{
    protected $sidebarMenu;

    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $menu = $this->getAdminMenu($factory);

        $menu->setChildrenAttribute('class', 'nav');
        $this->setDropdownMenuAttributes($menu);

        foreach ($menu as $row) {
            $this->checkRole($row);
            if ($row->hasChildren()) {
                $row->setExtra('safe_label', true);
                $row->setLabel($row->getName().' <b class="caret"></b>');
            }
        }

        return $menu;
    }

    public function sidebarMenu(FactoryInterface $factory, array $options)
    {
        $menu = $this->getAdminMenu($factory);
        $this->findCurrent($menu);

        if (!$this->sidebarMenu || $this->sidebarMenu === $menu) {
            return $factory->createItem('default');
        }

        $this->sidebarMenu->setChildrenAttribute('class', 'nav nav-list well');
        $this->setDropdownSubmenuAttributes($this->sidebarMenu);

        foreach ($menu as $row) {
            $this->checkRole($row);
        }

        return $this->sidebarMenu;
    }

    protected function getAdminMenu($factory)
    {
        $root = $this->container->get('msi_menu.menu_root_manager')->findRootByName('admin', $this->container->get('request')->getLocale());

        if (!$root) {
            return $factory->createItem('default');
        }

        $menu = $factory->createFromNode($root);

        return $menu;
    }

    protected function setDropdownMenuAttributes($menuItem)
    {
        foreach ($menuItem->getChildren() as $child) {
            $this->checkRole($child);
            if ($child->hasChildren()) {
                $child->setAttribute('class', 'dropdown');
                $child->setLinkAttribute('class', 'dropdown-toggle');
                $child->setLinkAttribute('data-toggle', 'dropdown');
                $child->setChildrenAttribute('class', 'dropdown-menu');
            }
            $this->setDropdownSubmenuAttributes($child);
        }
    }

    protected function setDropdownSubmenuAttributes($menuItem)
    {
        foreach ($menuItem->getChildren() as $child) {
            $this->checkRole($child);
            if ($child->hasChildren()) {
                $child->setAttribute('class', 'dropdown-submenu');
                $child->setChildrenAttribute('class', 'dropdown-menu');
                $child->setLinkAttribute('tabindex', -1);
            }
        }
    }

    protected function findCurrent($node)
    {
        $requestParts = explode('/', $this->container->get('request')->getRequestUri());

        foreach ($node->getChildren() as $child) {
            $menuParts = explode('/', $child->getUri());
            if (isset($menuParts[4]) && isset($requestParts[4]) && $menuParts[4] === $requestParts[4]) {
                $child->setCurrent(true);
                echo $menuParts[4].'::::::::'.$requestParts[4];
                $this->sidebarMenu = $child->getParent();
            } else {
                $this->findCurrent($child);
            }
        }
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

        if (in_array($route, $this->container->getParameter('msi_admin.super_admin_routes'))) {
            if (!$this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
                $menu->getParent()->removeChild($menu);
            }
        }
    }
}
