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

    protected $code;

    protected $controller;

    protected $locales = array();

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

    protected $action = null;

    public function __construct($id, $bundleName)
    {
        $this->init($id, $bundleName);
        $this->configure();
    }

    public function init($id, $bundleName)
    {
        $pieces = explode('_', $id);

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

    public function genUrl($route, $parameters = array(), $mergeQuery = true, $absolute = false)
    {
        if (true === $mergeQuery) {
            $parameters = array_merge($this->query->all(), $parameters);
        }

        return $this->container->get('router')->generate($this->code.'_'.$route, $parameters, $absolute);
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
        return $this->container->get('templating')->render('MsiAdminBundle:Crud:breadcrumb.html.twig', array('breadcrumbs' => $this->buildBreadcrumb()));
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

    public function buildBreadcrumb()
    {
        $request = $this->container->get('request');
        $this->action = preg_replace(array('#^[a-z]+_[a-z]+_[a-z]+_#'), array(''), $request->attributes->get('_route'));
        $crumbs = array();
        $back = 'Back';
        $edit = $this->container->get('translator')->trans('Edit', array(), 'MsiAdminBundle');
        $add = $this->container->get('translator')->trans('New', array(), 'MsiAdminBundle');
        $id = $request->query->get('id');

        if ($this->hasParent()) {
            $parent = $this->getParent();
            $parentId = $request->query->get('parentId');
            $parentObject = $parent->getModelManager()->findBy(array('a.id' => $parentId), array(), array(), 1)->getQuery()->getSingleResult();

            $crumbs[] = array('label' => $parent->getLabel(2), 'path' => $parent->genUrl('index'));
            $crumbs[] = array('label' => ucfirst($parentObject), 'path' => $parent->genUrl('edit', array('id' => $parentId)));
        }

        $crumbs[] = array('label' => $this->getLabel(2), 'path' => 'index' !== $this->action ? $this->genUrl('index') : '');

        if ($this->action === 'edit') {
            $crumbs[] = array('label' => $edit.' '.$this->getLabel(), 'path' => '');
            $crumbs[] = array('label' => $back, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($this->action === 'new') {
            $crumbs[] = array('label' => $add.' '.$this->getLabel(), 'path' => '');
            $crumbs[] = array('label' => $back, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($this->hasParent() && 'index' === $this->action) {
            $crumbs[] = array('label' => $back, 'path' => $this->getParent()->genUrl('index'), 'class' => 'pull-right');
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

    public function setParent(Admin $parent)
    {
        $this->parent = $parent;
        if (!$parent->hasChild()) $parent->setChild($this);
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function hasChild()
    {
        return $this->child instanceof Admin;
    }

    public function hasParent()
    {
        return $this->parent instanceof Admin;
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

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    public function setSearchFields($searchFields)
    {
        $this->searchFields = $searchFields;
    }

    public function getSearchFields()
    {
        if (null === $this->searchFields) {
            if (method_exists($this->getModelManager()->getClass(), 'getName')) {
                $this->searchFields[] = 'name';
            } else if (method_exists($this->getModelManager()->getClass(), 'getTitle')) {
                $this->searchFields[] = 'title';
            } else {
                die('Please specify one or more search fields in your Admin class.');
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

        $prefix = '/admin/'.$this->code.'/';
        $suffix = '.html';

        $routes = array(
            'index' => 'index',
            'new' => 'new',
            'edit' => 'edit',
            'delete' => 'delete',
            'change' => 'change',
            'sort' => 'sort',
        );

        foreach ($routes as $key => $val) {
            $collection->add(
                $this->code.'_'.$key,
                new Route(
                    $prefix.$val.$suffix,
                    array(
                        '_controller' => $this->controller.$key,
                    )
                )
            );
        }

        return $collection;
    }
}
