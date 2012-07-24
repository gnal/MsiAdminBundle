<?php

namespace Msi\Bundle\AdminBundle\Admin;

use Symfony\Component\Form\FormBuilder;
use Msi\Bundle\AdminBundle\Table\TableBuilder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class Admin
{
    public $query;
    protected $serviceId;
    protected $code;
    protected $controller;
    protected $locales = array();
    protected $adminServiceIds = array();
    protected $label = null;
    protected $form = null;
    protected $table = null;
    protected $templates;
    protected $object;
    protected $parent = null;
    protected $child = null;
    protected $container = null;
    protected $modelManager = null;
    protected $bundleName = null;
    protected $searchFields = null;

    public function __construct($id, $bundleName)
    {
        $this->init($id, $bundleName);
        $this->configure();
    }

    public function init($id, $bundleName)
    {
        $pieces = explode('_', $id);

        $this->serviceId = $id;
        $this->code = preg_replace('@_admin$@', '', $id);
        $this->bundleName = $bundleName;
        $this->controller = 'MsiAdminBundle:Crud:';
        $this->templates = array(
            'index' => 'MsiAdminBundle:Crud:index.html.twig',
            'new'   => 'MsiAdminBundle:Crud:new.html.twig',
            'edit'  => 'MsiAdminBundle:Crud:edit.html.twig',
        );
        $this->query = new ParameterBag();
    }

    public function configure()
    {
    }

    public function buildTable($builder)
    {
    }

    public function createTableBuilder()
    {
        return new TableBuilder($this);
    }

    public function getTable()
    {
        if (!$this->table) {
            $builder = $this->createTableBuilder();
            $this->buildTable($builder);
            $this->table = $builder->getTable();
        }

        return $this->table;
    }

    public function buildForm($builder)
    {
    }

    public function createFormBuilder($data = null, array $options = array())
    {
        return $this->container->get('form.factory')->createBuilder('form', $data, $options);
    }

    public function getForm()
    {
        if (!$this->form) {
            $builder = $this->createFormBuilder();
            $this->buildForm($builder);
            $this->form = $builder->getForm();
        }

        return $this->form;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    public function isGranted($role)
    {
        return $this->container->get('security.context')->isGranted(strtoupper('ROLE_'.$this->serviceId.'_'.$role));
    }

    public function genUrl($route, $parameters = array(), $mergeQuery = true, $absolute = false)
    {
        if (true === $mergeQuery) {
            $parameters = array_merge($this->query->all(), $parameters);
        }

        return $this->container->get('router')->generate('admin_'.$this->code.'_'.$route, $parameters, $absolute);
    }

    public function setTemplate($name, $value)
    {
        $this->templates[$name] = $value;
    }

    public function getTemplate($name)
    {
        return (isset($this->templates[$name])) ? $this->templates[$name]: null;
    }

    public function getClassName()
    {
        return substr($this->getModelManager()->getClass(), strrpos($this->getModelManager()->getClass(), '\\') + 1);
    }

    public function getLabel($number = 1)
    {
        if (!$this->label)
            $this->label = substr($this->getModelManager()->getClass(), strrpos($this->getModelManager()->getClass(), '\\') + 1);

        return $this->container->get('translator')->transChoice($this->label, $number, array(), $this->bundleName);
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function renderTable()
    {
        return $this->container->get('templating')->render('MsiAdminBundle:Crud:table.html.twig', array('table' => $this->getTable()));
    }

    public function renderBreadcrumb()
    {
        return $this->container->get('templating')->render('MsiAdminBundle:Crud:breadcrumb.html.twig', array('breadcrumbs' => $this->getBreadcrumb()));
    }

    public function getBreadcrumb()
    {
        $request = $this->container->get('request');
        $action = preg_replace(array('#^[a-z]+_[a-z]+_[a-z]+_[a-z]+_#'), array(''), $request->attributes->get('_route'));
        $crumbs = array();
        $backLabel = $this->container->get('translator')->trans('Back', array(), 'MsiAdminBundle');

        if ($this->hasParent()) {
            $parentAdmin = $this->getParent();
            $parent = $parentAdmin->getModelManager()->findBy(array('a.id' => $request->query->get('parentId')))->getQuery()->getSingleResult();

            $crumbs[] = array('label' => $parentAdmin->getLabel(2), 'path' => $parentAdmin->genUrl('index'));
            $crumbs[] = array('label' => $parent, 'path' => $parentAdmin->genUrl('edit', array('id' => $parent->getId())));
        }

        $crumbs[] = array('label' => $this->getLabel(2), 'path' => 'index' !== $action ? $this->genUrl('index') : '');

        if ($action === 'edit') {
            $object = $this->getModelManager()->findBy(array('a.id' => $request->query->get('id')))->getQuery()->getSingleResult();

            $crumbs[] = array('label' => $object, 'path' => '');
            $crumbs[] = array('label' => $backLabel, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($action === 'new') {
            $crumbs[] = array('label' => $this->container->get('translator')->trans('Add', array(), 'MsiAdminBundle'), 'path' => '');
            $crumbs[] = array('label' => $backLabel, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($this->hasParent() && 'index' === $action) {
            $crumbs[] = array('label' => $backLabel, 'path' => $this->getParent()->genUrl('index'), 'class' => 'pull-right');
        }

        return $crumbs;
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

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Admin $parent)
    {
        $this->parent = $parent;
        if (!$parent->hasChild()) $parent->setChild($this);
    }

    public function hasChild()
    {
        return $this->child instanceof Admin;
    }

    public function hasParent()
    {
        return $this->parent instanceof Admin;
    }

    public function getLocales()
    {
        return $this->locales;
    }

    public function setLocales($locales)
    {
        $this->locales = $locales;

        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    public function setModelManager($modelManager)
    {
        $this->modelManager = $modelManager;
    }

    public function getModelManager()
    {
        return $this->modelManager;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    public function getAdminServiceIds()
    {
        return $this->adminServiceIds;
    }

    public function setAdminServiceIds($adminServiceIds)
    {
        $this->adminServiceIds = $adminServiceIds;

        return $this;
    }

    public function setSearchFields($searchFields)
    {
        $this->searchFields = $searchFields;
    }

    public function getSearchFields()
    {
        if (!$this->searchFields) {
            if (property_exists($this->getModelManager()->getClass(), 'id')) {
                $this->searchFields[] = 'id';
            }
        }

        return $this->searchFields;
    }

    public function getRoutes()
    {
        return $this->buildRoutes();
    }

    public function buildRoutes()
    {
        $collection = new RouteCollection();

        $prefix = '/{_locale}/admin/'.$this->code.'/';
        $suffix = '.html';

        $names = array(
            'index',
            'new',
            'edit',
            'delete',
            'change',
            'sort',
        );

        foreach ($names as $name) {
            $collection->add(
                'admin_'.$this->code.'_'.$name,
                new Route(
                    $prefix.$name.$suffix,
                    array(
                        '_controller' => $this->controller.$name,
                    )
                )
            );
        }

        return $collection;
    }
}
