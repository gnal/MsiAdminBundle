<?php

namespace Msi\Bundle\AdminBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class CrudController extends ContainerAware
{
    protected $admin;
    protected $request;
    protected $id;
    protected $parentId;
    protected $object;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->init();
    }

    public function render($view, array $parameters = array(), Response $response = null)
    {
        $parameters['admin'] = $this->admin;

        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }

    public function indexAction()
    {
        if (!$this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !$this->admin->isGranted('list')) {
                throw new AccessDeniedException();
        }

        $criteria = array();
        $orderBy = array();
        $table = $this->admin->getTable();

        if (property_exists($this->admin->getModelManager()->getClass(), 'position')) {
            $orderBy['a.position'] = 'ASC';
            $table->setSortable(true);
        }

        if ($this->parentId) {
            $criteria['a.'.strtolower($this->admin->getParent()->getClassName())] = $this->parentId;
        }

        if (!$this->request->query->get('q')) {
            $qb = $this->admin->getModelManager()->findBy($criteria, array(), $orderBy);
        } else {
            $qb = $this->admin->getModelManager()->findByQ($this->request->query->get('q'), $this->admin->getSearchFields(), $criteria);
        }

        $this->configureIndexQuery($qb);

        $paginator = $this->container->get('msi_paginator.paginator.factory')->create();

        $paginator->setLimit($this->container->get('session')->get('limit', 10));
        $paginator->setPage($this->request->query->get('page', 1));
        $paginator->setData($qb);
        if ($this->parentId) {
            $paginator->setParameters(array('parentId' => $this->parentId));
        }

        $table->setData($paginator->getResult());
        $table->setPaginator($paginator);

        return $this->render($this->admin->getTemplate('index'), array());
    }

    public function newAction()
    {
        if (!$this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !$this->admin->isGranted('create')) {
                throw new AccessDeniedException();
        }

        $object = $this->admin->getModelManager()->create();
        $this->admin->setObject($object);

        if (property_exists($object, 'translations')) {
            foreach ($this->admin->getLocales() ?: array('fr', 'en') as $locale) {
                $translationClassName = get_class($object).'Translation';
                $translation = new $translationClassName();
                $translation->setLocale($locale);
                $object->addTranslation($translation);
            }
        }

        $form = $this->admin->getForm();
        $formHandler = $this->container->get('msi_admin.crud.form.handler');

        $formHandler->setAdmin($this->admin);
        $process = $formHandler->process($form, $object);

        if ($process) {
            $this->container->get('session')->setFlash('success', 'The '.strtolower($this->admin->getLabel()).' has been added successfully.');

            return new RedirectResponse($this->admin->genUrl('index'));
        }

        return $this->render($this->admin->getTemplate('new'), array('form' => $form->createView()));
    }

    public function editAction()
    {
        if (!$this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !$this->admin->isGranted('update')) {
                throw new AccessDeniedException();
        }

        $form = $this->admin->getForm();
        $formHandler = $this->container->get('msi_admin.crud.form.handler');

        $formHandler->setAdmin($this->admin);
        $process = $formHandler->process($form, $this->object);

        if ($process) {
            $this->container->get('session')->setFlash('success', 'The changes have been saved successfully.');

            return new RedirectResponse($this->admin->genUrl('index'));
        }

        return $this->render($this->admin->getTemplate('edit'), array('form' => $form->createView(), 'id' => $this->id));
    }

    public function deleteAction()
    {
        if (!$this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !$this->admin->isGranted('delete')) {
                throw new AccessDeniedException();
        }

        $this->admin->getModelManager()->delete($this->object);

        $this->container->get('session')->setFlash('success', 'The removal was performed successfully.');

        return new RedirectResponse($this->admin->genUrl('index'));
    }

    public function changeAction()
    {
        $this->admin->getModelManager()->change($this->object, $this->request->query->get('field'));

        return new RedirectResponse($this->admin->genUrl('index'));
    }

    public function sortAction()
    {
        $disposition = $this->request->query->get('disposition');
        $criteria = array();

        if ($this->parentId) {
            $criteria['a.'.strtolower($this->admin->getParent()->getClassName())] = $this->parentId;
        }
        $orderBy['a.position'] = 'ASC';
        $objects = $this->admin->getModelManager()->findBy($criteria, array(), $orderBy)->getQuery()->execute();

        $this->admin->getModelManager()->savePosition($objects, $disposition);

        return new Response();
    }

    protected function init()
    {
        $this->request = $this->container->get('request');
        $this->parentId = $this->request->query->get('parentId');
        $this->id = $this->request->query->get('id');

        preg_match('@msi_[a-z]+_[a-z]+@', $this->request->getPathInfo(), $matches);
        $this->admin = $this->container->get($matches[0].'_admin');

        $this->admin->query->set('page', $this->request->query->get('page'));
        $this->admin->query->set('q', $this->request->query->get('q'));
        $this->admin->query->set('parentId', $this->parentId);

        if ($this->id) {
            $qb = $this->admin->getModelManager()->findBy(array('a.id' => $this->id), array(), array(), null, null, false);
            $this->configureShowQuery($qb);
            $this->object = $qb->getQuery()->getSingleResult();
            $this->admin->setObject($this->object);
        }
    }

    protected function configureIndexQuery($qb)
    {
    }

    protected function configureShowQuery($qb)
    {
    }
}
