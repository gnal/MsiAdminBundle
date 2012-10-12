<?php

namespace Msi\Bundle\AdminBundle\Admin;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Msi\Bundle\AdminBundle\Table\TableBuilder;
use Msi\Bundle\AdminBundle\Entity\BaseManager;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class Admin
{
    public $query;

    protected $options = array();

    protected $adminId;
    protected $adminIds;
    protected $child;
    protected $parent;
    protected $entity;
    protected $parentEntity;
    protected $label;
    protected $container;
    protected $objectManager;
    protected $forms;
    protected $tables;

    public function __construct(BaseManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getAdminId()
    {
        return $this->adminId;
    }

    public function getAdminIds()
    {
        return $this->adminIds;
    }

    public function getAction()
    {
        return preg_replace(array('#^[a-z]+_([a-z]+_){1,2}[a-z]+_[a-z]+_#'), array(''), $this->container->get('request')->attributes->get('_route'));
    }

    public function getBundleName()
    {
        $parts = explode('_', $this->adminId);

        return ucfirst($parts[0]).ucfirst($parts[1]).'Bundle';
    }

    public function isSortable()
    {
        return property_exists($this->getObjectManager()->getClass(), 'position');
    }

    public function isTranslatable()
    {
        return is_subclass_of($this->getObjectManager()->getClass(), 'Msi\Bundle\AdminBundle\Entity\Translatable');
    }

    public function isTranslationField($field)
    {
        if ($this->isTranslatable()) {
            return property_exists($this->getObject()->getTranslation(), $field);
        }
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

    public function getContainer()
    {
        return $this->container;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }

    public function getObject()
    {
        if (!$this->object) {
            $this->object = $this->objectManager->findOneOrCreate($this->container->get('request')->query->get('id'));
        }

        return $this->object;
    }

    public function getParentObject()
    {
        if (!$this->parentObject) {
            $this->parentObject = $this->getParent()->objectManager->findOneOrCreate($this->container->get('request')->query->get('parentId'));
        }

        return $this->parentObject;
    }

    public function getOption($key, $default = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getAppLocales()
    {
        return $this->container->getParameter('msi_admin.app_locales');
    }

    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;

        $this->configure();
        $this->init();

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

        return $this->container->get('form.factory')->createNamedBuilder($name, 'form', $data, $options);
    }

    public function getForm($name = '')
    {
        if (!isset($this->forms[$name])) {
            $method = 'build'.ucfirst($name).'Form';

            if (!method_exists($this, $method)) return false;

            $builder = $this->createFormBuilder($name, $name ? null : $this->getObject(), array('cascade_validation' => true));
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

    public function buildBreadcrumb()
    {
        $request = $this->container->get('request');
        $action = $this->getAction();
        $crumbs = array();
        $backLabel = $this->translator->trans('Back');

        if ($this->hasParent()) {
            $crumbs[] = array('label' => $this->getParent()->getLabel(2), 'path' => $this->getParent()->genUrl('index'));
            $crumbs[] = array('label' => $this->getParentObject(), 'path' => $this->getParent()->genUrl('show', array('id' => $this->getParentObject()->getId())));
        }

        $crumbs[] = array('label' => $this->getLabel(2), 'path' => 'index' !== $action ? $this->genUrl('index') : '', 'class' => 'index' !== $action ? '' : 'active');

        if ($action === 'new') {
            $crumbs[] = array('label' => $this->translator->trans('Add'), 'path' => '', 'class' => 'active');
            $crumbs[] = array('label' => $backLabel, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($action === 'edit') {
            // $crumbs[] = array('label' => $this->getObject(), 'path' => $this->genUrl('show', array('id' => $this->getObject()->getId())));
            $crumbs[] = array('label' => $this->translator->trans('Edit'), 'path' => '', 'class' => 'active');
            $crumbs[] = array('label' => $backLabel, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($action === 'show') {
            $crumbs[] = array('label' => $this->getObject(), 'path' => '', 'class' => 'active');
            $crumbs[] = array('label' => $backLabel, 'path' => $this->genUrl('index'), 'class' => 'pull-right');
        }

        if ($this->hasParent() && 'index' === $action) {
            $crumbs[] = array('label' => $backLabel, 'path' => $this->getParent()->genUrl('index'), 'class' => 'pull-right');
        }

        return $crumbs;
    }

    public function prePersist($entity)
    {
    }

    public function postPersist($entity)
    {
    }

    public function preUpdate($entity)
    {
    }

    public function postUpdate($entity)
    {
    }

    protected function configure()
    {
    }

    protected function init()
    {
        $this->object = null;
        $this->parentObject = null;
        $this->forms = array();
        $this->tables = array();
        $this->query = new ParameterBag();

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($this->options);
    }

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'controller' => 'MsiAdminBundle:Admin:',
            'form_template' => 'MsiAdminBundle:Admin:form.html.twig',
            'search_fields' => array('a.id'),
            'index_template' => 'MsiAdminBundle:Admin:index.html.twig',
            'new_template' => 'MsiAdminBundle:Admin:new.html.twig',
            'edit_template' => 'MsiAdminBundle:Admin:edit.html.twig',
            // 'sidebar_nav' => $this->getBundleName().'::sidebar_nav.html.twig',
            'sidebar_nav' => false,
        ));
    }
}
