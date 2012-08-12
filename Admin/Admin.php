<?php

namespace Msi\Bundle\AdminBundle\Admin;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Msi\Bundle\AdminBundle\Table\TableBuilder;
use Msi\Bundle\AdminBundle\Entity\ObjectManager;

abstract class Admin
{
    public $query;

    protected $controller;
    protected $adminId;
    protected $adminIds;
    protected $child;
    protected $parent;
    protected $entity;
    protected $parentEntity;
    protected $label;
    protected $searchFields;
    protected $container;
    protected $objectManager;
    protected $forms;
    protected $tables;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;

        $this->init();
        $this->configure();
    }

    public function getAdminId()
    {
        return $this->adminId;
    }

    public function getAdminIds()
    {
        return $this->adminIds;
    }

    public function getBundleName()
    {
        return ucfirst($this->adminIdParts[0]).ucfirst($this->adminIdParts[1]).'Bundle';
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getSearchFields()
    {
        return $this->searchFields;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }

    public function getObject()
    {
        if (!$this->object) {
            $this->object = $this->getObjectManager()->getAdminObject($this->container->get('request')->query->get('id'), $this->container->getParameter('msi_admin.locales'));
        }

        return $this->object;
    }

    public function getParentObject()
    {
        if (!$this->parentObject) {
            $this->parentObject = $this->getParent()->getObjectManager()->getAdminObject($this->container->get('request')->query->get('parentId'), $this->container->getParameter('msi_admin.locales'));
        }

        return $this->parentObject;
    }

    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;

        return $this;
    }

    public function setAdminIds(array $adminIds)
    {
        $this->adminIds = $adminIds;

        return $this;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator');

        return $this;
    }

    public function getChild()
    {
        return $this->child;
    }

    public function setChild(Admin $child)
    {
        $this->child = $child;
        if (!$child->hasParent()) $child->setParent($this);
    }

    public function hasChild()
    {
        return $this->child instanceof Admin;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Admin $parent)
    {
        $this->parent = $parent;
        if (!$parent->hasChild()) $parent->setChild($this);
    }

    public function hasParent()
    {
        return $this->parent instanceof Admin;
    }

    public function createTableBuilder()
    {
        return new TableBuilder();
    }

    public function getTable($name = '')
    {
        if (!isset($this->dataTables[$name])) {
            $method = 'build'.ucfirst($name).'Table';

            if (!method_exists($this, $method)) return false;

            $builder = $this->createTableBuilder();
            $this->$method($builder);
            $this->dataTables[$name] = $builder->getTable();
        }

        return $this->dataTables[$name];
    }

    public function createFormBuilder($name, $data = null, array $options = array())
    {
        if (!$name) $name = $this->adminId;

        return $this->container->get('form.factory')->createNamedBuilder($name, 'form', $this->getObject(), $options);
    }

    public function getForm($name = '')
    {
        if (!isset($this->forms[$name])) {
            $method = 'build'.ucfirst($name).'Form';

            if (!method_exists($this, $method)) return false;

            $builder = $this->createFormBuilder($name);
            $this->$method($builder);
            $this->forms[$name] = $builder->getForm();
        }

        return $this->forms[$name];
    }

    public function isGranted($role)
    {
        if (!$this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !$this->container->get('security.context')->isGranted(strtoupper('ROLE_'.$this->adminId.'_'.$role))) {

            return false;
        } else {
            if (!$this->container->get('security.context')->getToken()->getUser()->isSuperAdmin() && is_a($this->getObject(), 'FOS\UserBundle\Model\UserInterface')) {
                if ($this->getObject()->isSuperAdmin()) {

                    return false;
                }
                if ($this->getObject()->hasRole('ROLE_ADMIN') && $this->container->get('security.context')->getToken()->getUser()->getId() !== $this->getObject()->getId()) {

                    return false;
                }
            }

            return true;
        }
    }

    public function genUrl($route, $parameters = array(), $mergeQuery = true, $absolute = false)
    {
        if (true === $mergeQuery) {
            $parameters = array_merge($this->query->all(), $parameters);
        }

        return $this->container->get('router')->generate($this->adminId.'_'.$route, $parameters, $absolute);
    }

    public function getClassName()
    {
        return substr($this->getObjectManager()->getClass(), strrpos($this->getObjectManager()->getClass(), '\\') + 1);
    }

    public function getLabel($number = 1)
    {
        if (!$this->label) {
            $this->label = $this->getClassName();
        }

        return $this->translator->transChoice('entity.'.$this->label, $number);
    }

    public function buildBreadcrumb()
    {
        $request = $this->container->get('request');
        $action = preg_replace(array('#^[a-z]+_([a-z]+_){1,2}[a-z]+_[a-z]+_#'), array(''), $request->attributes->get('_route'));
        $crumbs = array();
        $backLabel = $this->translator->trans('Back');

        if ($this->hasParent()) {
            $crumbs[] = array('label' => $this->getParent()->getLabel(2), 'path' => $this->getParent()->genUrl('index'));
            $crumbs[] = array('label' => $this->getParentObject(), 'path' => $this->getParent()->genUrl('show', array('id' => $this->getParentObject()->getId())));
        }

        $crumbs[] = array('label' => $this->getLabel(2), 'path' => 'index' !== $action ? $this->genUrl('index') : '');

        if ($action === 'new') {
            $crumbs[] = array('label' => $this->translator->trans('Add'), 'path' => '');
            $crumbs[] = array('label' => $backLabel, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($action === 'edit') {
            $crumbs[] = array('label' => $this->getObject(), 'path' => $this->genUrl('show', array('id' => $this->getObject()->getId())));
            $crumbs[] = array('label' => $this->translator->trans('Edit'), 'path' => '');
            $crumbs[] = array('label' => $backLabel, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($action === 'show') {
            $crumbs[] = array('label' => $this->getObject(), 'path' => '');
            $crumbs[] = array('label' => $backLabel, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($this->hasParent() && 'index' === $action) {
            $crumbs[] = array('label' => $backLabel, 'path' => $this->getParent()->genUrl('index'), 'class' => 'pull-right');
        }

        return $crumbs;
    }

    public function buildRoutes()
    {
        $collection = new RouteCollection();

        $prefix = '/{_locale}/admin/'.preg_replace(array('@_admin$@', '@^[a-z]+_[a-z]+_@'), array('', ''), $this->adminId).'/';
        $suffix = '';

        $names = array(
            'index',
            'show',
            'new',
            'edit',
            'delete',
            'change',
            'sort',
        );

        foreach ($names as $name) {
            $collection->add(
                $this->adminId.'_'.$name,
                new Route(
                    $prefix.$name.$suffix,
                    array(
                        '_controller' => $this->controller.$name,
                        '_admin' => $this->adminId,
                    )
                )
            );
        }

        return $collection;
    }

    protected function configure()
    {
    }

    private function init()
    {
        $this->object = null;
        $this->parentObject = null;
        $this->forms = array();
        $this->tables = array();
        $this->searchFields = array();
        $this->query = new ParameterBag();
        $this->controller = 'MsiAdminBundle:Crud:';
    }
}
