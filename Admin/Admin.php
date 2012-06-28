<?php

namespace Msi\Bundle\AdminBundle\Admin;

use Symfony\Component\Form\FormBuilder;
use Msi\Bundle\AdminBundle\DataTable\DataTableBuilder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class Admin
{
    public $query;

    private $code;

    private $controller;

    private $label = null;

    private $form = null;

    private $dataTable = null;

    private $templates;

    private $parent = null;

    private $child = null;

    private $router = null;

    private $formFactory = null;

    private $modelManager = null;

    private $request = null;

    private $securityContext = null;

    private $templating = null;

    private $translator = null;

    private $bundleName = null;

    private $searchFields = null;

    private $action = null;

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
        $this->controller = 'MsiAdminBundle:CRUD:';
        $this->templates = array(
            'index' => 'MsiAdminBundle:CRUD:index.html.twig',
            'new'   => 'MsiAdminBundle:CRUD:new.html.twig',
            'edit'  => 'MsiAdminBundle:CRUD:edit.html.twig',
        );
        $this->query = new ParameterBag();
    }

    public function configure()
    {
    }

    public function genUrl($route, $parameters = array(), $mergeQuery = true, $absolute = false)
    {
        if (true === $mergeQuery) {
            $parameters = array_merge($this->query->all(), $parameters);
        }

        return $this->getRouter()->generate($this->code.'_'.$route, $parameters, $absolute);
    }

    public function createDataTableBuilder()
    {
        return new DataTableBuilder($this, $this->securityContext);
    }

    public function configureDataTable($builder)
    {
    }

    public function buildDataTable()
    {
        $builder = $this->createDataTableBuilder();

        $this->configureDataTable($builder);

        $this->dataTable = $builder->getDataTable();
    }

    public function getDataTable()
    {
        if (!$this->dataTable) $this->buildDataTable();

        return $this->dataTable;
    }

    public function createFormBuilder($data = null, array $options = array())
    {
        return $this->formFactory->createBuilder('form', $data, $options);
    }

    public function configureForm($builder)
    {
    }

    public function buildForm()
    {
        $builder = $this->createFormBuilder();

        $this->configureForm($builder);

        $this->form = $builder->getForm();
    }

    public function getForm()
    {
        if (!$this->form) $this->buildForm();

        return $this->form;
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

        return $this->translator->transChoice($this->label, $number, array(), $this->bundleName);
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function renderTable()
    {
        return $this->templating->render('MsiAdminBundle:Crud:table.html.twig', array('table' => $this->getDataTable()));
    }

    public function renderBreadcrumb()
    {
        return $this->templating->render('MsiAdminBundle:Crud:breadcrumb.html.twig', array('breadcrumbs' => $this->buildBreadcrumb()));
    }

    public function buildBreadcrumb()
    {
        $request = $this->getRequest();
        $crumbs = array();
        $back = 'Back';
        $edit = $this->translator->trans('Edit', array(), 'MsiAdminBundle');
        $add = $this->translator->trans('New', array(), 'MsiAdminBundle');
        $id = $request->query->get('id');

        if ($this->hasParent()) {
            $parent = $this->getParent();
            $parentId = $request->query->get('parentId');

            $crumbs[] = array('label' => $parent->getLabel(2), 'path' => $parent->genUrl('index'));
            $crumbs[] = array('label' => ucfirst($parent->getModelManager()->findBy(array('a.id' => $parentId), null, 1)->getQuery()->getSingleResult()), 'path' => $parent->genUrl('edit', array('id' => $parentId)));
        }

        $crumbs[] = array('label' => $this->getLabel(2), 'path' => 'index' === $this->action ? $this->genUrl('index') : '');

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

    public function setRouter($router)
    {
        $this->router = $router;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
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

    public function setRequest($request)
    {
        $this->request = $request;
        $this->action = preg_replace(array('#^[a-z]+_[a-z]+_[a-z]+_#'), array(''), $request->attributes->get('_route'));
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function setTemplating($templating)
    {
        $this->templating = $templating;
    }

    public function getTemplating()
    {
        return $this->templating;
    }

    public function setSecurityContext($securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function getSecurityContext()
    {
        return $this->securityContext;
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
