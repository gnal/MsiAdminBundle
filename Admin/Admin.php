<?php

namespace Msi\Bundle\AdminBundle\Admin;

use Symfony\Component\Form\FormBuilder;
use Msi\Bundle\AdminBundle\Table\TableBuilder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Msi\Bundle\AdminBundle\Entity\ModelManager;

abstract class Admin implements AdminInterface
{
    public $query;

    protected $adminId;
    protected $adminIdParts;
    protected $adminIds;
    protected $child;
    protected $parent;
    protected $object;
    protected $label;
    protected $searchFields;

    protected $controller;
    protected $form = null;
    protected $table = null;
    protected $templates;
    protected $container;
    protected $modelManager;

    public function __construct($id, ModelManager $modelManager)
    {
        $this->adminId = $id;
        $this->modelManager = $modelManager;

        $this->init();
        $this->configure();
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

    public function getModelManager()
    {
        return $this->modelManager;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getTemplate($name)
    {
        return (isset($this->templates[$name])) ? $this->templates[$name]: null;
    }

    public function setAdminIds(array $adminIds)
    {
        $this->adminIds = $adminIds;

        return $this;
    }

    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    public function setTemplate($name, $value)
    {
        $this->templates[$name] = $value;
    }

    public function getChild()
    {
        return $this->child;
    }

    public function setChild(AdminInterface $child)
    {
        $this->child = $child;
        if (!$child->hasParent()) $child->setParent($this);
    }

    public function hasChild()
    {
        return $this->child instanceof AdminInterface;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(AdminInterface $parent)
    {
        $this->parent = $parent;
        if (!$parent->hasChild()) $parent->setChild($this);
    }

    public function hasParent()
    {
        return $this->parent instanceof AdminInterface;
    }

    public function createTableBuilder()
    {
        return new TableBuilder($this);
    }

    public function buildTable($builder)
    {
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

    public function createFormBuilder($data = null, array $options = array())
    {
        return $this->container->get('form.factory')->createBuilder('form', $data, $options);
    }

    public function buildForm($builder)
    {
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

    public function isGranted($role)
    {
        return $this->container->get('security.context')->isGranted(strtoupper('ROLE_'.$this->adminId.'_'.$role));
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
        return substr($this->getModelManager()->getClass(), strrpos($this->getModelManager()->getClass(), '\\') + 1);
    }

    public function getLabel($number = 1)
    {
        if (!$this->label) {
            $this->label = substr($this->getModelManager()->getClass(), strrpos($this->getModelManager()->getClass(), '\\') + 1);
        }

        return $this->container->get('translator')->transChoice($this->label, $number, array(), $this->getBundleName());
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

        $prefix = '/{_locale}/admin/'.preg_replace(array('@_admin$@', '@^[a-z]+_[a-z]+_@'), array('', ''), $this->adminId).'/';
        $suffix = '';

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
        $this->query = new ParameterBag();
        $this->adminIdParts = explode('_', $this->adminId);
        $this->controller = 'MsiAdminBundle:Crud:';
        $this->templates = array(
            'index' => 'MsiAdminBundle:Crud:index.html.twig',
            'new'   => 'MsiAdminBundle:Crud:new.html.twig',
            'edit'  => 'MsiAdminBundle:Crud:edit.html.twig',
        );
    }
}
