<?php

namespace Msi\Bundle\AdminBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Common\Collections\ArrayCollection;

class CrudController extends ContainerAware
{
    protected $admin;
    protected $request;
    protected $id;
    protected $parentId;
    protected $entity;
    protected $manager;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->request = $this->container->get('request');
        $this->init();
    }

    public function render($view, array $parameters = array(), Response $response = null)
    {
        $parameters['admin'] = $this->admin;

        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }

    public function indexAction()
    {
        $this->check('read');

        $orderBy = array();
        $parameters = array();
        $joins = array();
        $criteria = array();
        $table = $this->admin->getTable('index');
        $q = trim($this->request->query->get('q'));

        // Sortable
        if (property_exists($this->manager->getClass(), 'position')) {
            $orderBy['a.position'] = 'ASC';
            $table->setSortable(true);
        }

        // Nested
        if ($this->admin->hasParent() && $this->parentId) {
            $joins['a.'.$this->admin->getParentFieldName()] = 'parent';
            $criteria['parent.id'] = $this->parentId;
        }

        // Doctrine
        if (!$q) {
            $qb = $this->manager->findBy($criteria, $joins, $orderBy);
        } else {
            $qb = $this->manager->findByQ($q, $this->admin->getLikeFields(), $criteria);
        }
        $this->configureListQuery($qb);

        // Filters
        $filterFormHandler = $this->container->get('msi_admin.filter.form.handler');
        $filterForm = $this->admin->getForm('filter');
        if ($filterForm) {
            $filterFormHandler->process($filterForm, $qb);
            $parameters['filterForm'] = $filterForm->createView();
        }

        // Pagination
        $paginator = $this->container->get('msi_paginator.paginator.factory')->create();
        $paginator->setLimit($this->container->get('session')->get('limit', 10));
        $paginator->setPage($this->request->query->get('page', 1));
        $paginator->setData($qb);
        $paginator->query->set('parentId', $this->parentId);
        $paginator->query->set('filter', $this->request->query->get('filter'));
        $paginator->query->set('q', $this->request->query->get('q'));

        // Table
        $table->setData($paginator->getResult());
        $table->setPaginator($paginator);

        return $this->render('MsiAdminBundle:Crud:index.html.twig', $parameters);
    }

    public function showAction()
    {
        $this->check('read');

        $table = $this->admin->getTable('show');
        if (!$table) {
            return new RedirectResponse($this->admin->genUrl('edit', array('id' => $this->entity->getId())));
        }

        $table->setData(new ArrayCollection(array($this->entity)));

        return $this->render('MsiAdminBundle:Crud:show.html.twig');
    }

    public function newAction()
    {
        $this->check('create');

        $process = $this->processForm();
        if ($process) return $this->onSuccess();

        return $this->render('MsiAdminBundle:Crud:new.html.twig', array('form' => $this->admin->getForm()->createView()));
    }

    public function editAction()
    {
        $this->check('update');

        $process = $this->processForm();
        if ($process) return $this->onSuccess();

        return $this->render('MsiAdminBundle:Crud:edit.html.twig', array('form' => $this->admin->getForm()->createView(), 'id' => $this->entity->getId()));
    }

    public function deleteAction()
    {
        $this->check('delete');

        $this->manager->delete($this->entity);

        return $this->onSuccess();
    }

    public function changeAction()
    {
        $this->check('update');

        $this->manager->change($this->entity, $this->request->query->get('field'));

        return $this->onSuccess();
    }

    public function sortAction()
    {
        $this->check('update');

        $disposition = $this->request->query->get('disposition');
        $criteria = array();

        if ($this->parentId) {
            $criteria['a.'.lcfirst($this->admin->getParent()->getClassName())] = $this->parentId;
        }
        $orderBy['a.position'] = 'ASC';
        $objects = $this->manager->findBy($criteria, array(), $orderBy)->getQuery()->execute();

        $this->manager->savePosition($objects, $disposition);

        return new Response();
    }

    protected function processForm()
    {
        $form = $this->admin->getForm();
        $formHandler = $this->container->get('msi_admin.crud.form.handler');
        $process = $formHandler->setAdmin($this->admin)->process($form, $this->entity);

        return $process;
    }

    protected function onSuccess()
    {
        $this->container->get('session')->setFlash('success', $this->container->get('translator')->trans('The action was executed successfully!'));

        return new RedirectResponse($this->admin->genUrl('index'));
    }

    protected function check($role)
    {
        if (!$this->admin->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }

    protected function init()
    {
        $this->id = $this->request->query->get('id');
        $this->parentId = $this->request->query->get('parentId');
        $this->admin = $this->container->get($this->request->attributes->get('_admin'));
        $this->manager = $this->admin->getModelManager();
        $this->entity = $this->admin->getEntity();

        $this->admin->query->set('page', $this->request->query->get('page'));
        $this->admin->query->set('q', $this->request->query->get('q'));
        $this->admin->query->set('parentId', $this->parentId);
        $this->admin->query->set('filter', $this->request->query->get('filter'));
    }

    protected function configureListQuery($qb)
    {
    }
}
