<?php

namespace Msi\Bundle\AdminBundle\Admin;

use Symfony\Component\Form\FormBuilder;
use Msi\Bundle\AdminBundle\Table\TableBuilder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Msi\Bundle\AdminBundle\Entity\ModelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class Admin implements AdminInterface
{
    public $query;

    protected $controller;
    protected $templates;
    protected $adminId;
    protected $adminIds;
    protected $child;
    protected $parent;
    protected $object;
    protected $label;
    protected $likeFields;
    protected $container;
    protected $modelManager;
    protected $translator;

    protected $forms;
    protected $tables;

    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;

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

    public function getLikeFields()
    {
        return $this->likeFields;
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

        return $this->container->get('form.factory')->createNamedBuilder('form', $name, $data, $options);
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

        return $this->translator->transChoice($this->label, $number);
    }

    public function buildBreadcrumb()
    {
        $request = $this->container->get('request');
        $action = preg_replace(array('#^[a-z]+_([a-z]+_){1,2}[a-z]+_[a-z]+_#'), array(''), $request->attributes->get('_route'));
        $crumbs = array();
        $backLabel = $this->translator->trans('Back');

        if ($this->hasParent()) {
            $parentAdmin = $this->getParent();
            $parent = $parentAdmin->getModelManager()->findBy(array('a.id' => $request->query->get('parentId')))->getQuery()->getSingleResult();

            $crumbs[] = array('label' => $parentAdmin->getLabel(2), 'path' => $parentAdmin->genUrl('index'));
            $crumbs[] = array('label' => $parent, 'path' => $parentAdmin->genUrl('show', array('id' => $parent->getId())));
        }

        $crumbs[] = array('label' => $this->getLabel(2), 'path' => 'index' !== $action ? $this->genUrl('index') : '');

        if ($action === 'new') {
            $crumbs[] = array('label' => $this->translator->trans('Add'), 'path' => '');
            $crumbs[] = array('label' => $backLabel, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($action === 'edit') {
            $object = $this->getModelManager()->findBy(array('a.id' => $request->query->get('id')))->getQuery()->getSingleResult();
            $crumbs[] = array('label' => $object, 'path' => $this->genUrl('show', array('id' => $this->object->getId())));
            $crumbs[] = array('label' => $this->translator->trans('Edit'), 'path' => '');
            $crumbs[] = array('label' => $backLabel, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($action === 'show') {
            $object = $this->getModelManager()->findBy(array('a.id' => $request->query->get('id')))->getQuery()->getSingleResult();
            $crumbs[] = array('label' => $object, 'path' => '');
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
        $this->forms = array();
        $this->tables = array();
        $this->likeFields = array();
        $this->query = new ParameterBag();
        $this->controller = 'MsiAdminBundle:Crud:';
        $this->templates = array(
            'index' => 'MsiAdminBundle:Crud:index.html.twig',
            'show' => 'MsiAdminBundle:Crud:show.html.twig',
            'new'   => 'MsiAdminBundle:Crud:new.html.twig',
            'edit'  => 'MsiAdminBundle:Crud:edit.html.twig',
        );
    }
}
