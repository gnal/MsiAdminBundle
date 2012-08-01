<?php

namespace Msi\Bundle\AdminBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CrudController extends ContainerAware
{
    protected $admin;
    protected $request;
    protected $id;
    protected $parentId;
    protected $object;
    protected $manager;
    protected $translator;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->request = $this->container->get('request');
        $this->translator = $this->container->get('translator');
        $this->id = $this->request->query->get('id');
        $this->parentId = $this->request->query->get('parentId');

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

        $table = $this->admin->getTable();
        $filterFormHandler = $this->container->get('msi_admin.filter.form.handler');
        $filterForm = $this->admin->getFilterForm();
        // if sortable
        $orderBy = array();
        if (property_exists($this->manager->getClass(), 'position')) {
            $orderBy['a.position'] = 'ASC';
            $table->setSortable(true);
        }
        // if has parent
        $criteria = array();
        if ($this->admin->hasParent() && $this->parentId) {
            $criteria['a.'.strtolower($this->admin->getParent()->getClassName())] = $this->parentId;
        }

        if (!$this->request->query->get('q')) {
            $qb = $this->manager->findBy($criteria, array(), $orderBy);
        } else {
            $qb = $this->manager->findByQ($this->request->query->get('q'), $this->admin->getLikeFields(), $criteria);
        }
        $this->configureListQuery($qb);
        $filterFormHandler->process($filterForm, $qb);

        $paginator = $this->container->get('msi_paginator.paginator.factory')->create();
        $paginator->setLimit($this->container->get('session')->get('limit', 10));
        $paginator->setPage($this->request->query->get('page', 1));
        $paginator->setData($qb);
        $paginator->query->set('parentId', $this->parentId);
        $paginator->query->set('filter', $this->request->query->get('filter'));
        $paginator->query->set('q', $this->request->query->get('q'));

        $table->setData($paginator->getResult());
        $table->setPaginator($paginator);

        return $this->render($this->admin->getTemplate('index'), array('filterForm' => $filterForm->createView()));
    }

    public function newAction()
    {
        $this->check('create');

        if ($this->manager->isTranslatable()) {
            $object = $this->manager->create($this->container->getParameter('msi_admin.translation_locales'));
        } else {
            $object = $this->manager->create();
        }
        $this->admin->setObject($object);

        $form = $this->admin->getForm();
        $formHandler = $this->container->get('msi_admin.crud.form.handler');

        $formHandler->setAdmin($this->admin);
        $process = $formHandler->process($form, $object);
        if ($process) {
            $this->container->get('session')->setFlash('success', $this->translator->trans('The changes have been saved successfully'));

            return new RedirectResponse($this->admin->genUrl('index'));
        }

        return $this->render($this->admin->getTemplate('new'), array('form' => $form->createView()));
    }

    public function editAction()
    {
        $this->check('update');

        if ($this->manager->isTranslatable()) {
            $this->object->createTranslations($this->container->getParameter('msi_admin.translation_locales'));
        }

        $form = $this->admin->getForm();
        $formHandler = $this->container->get('msi_admin.crud.form.handler');

        $formHandler->setAdmin($this->admin);
        $process = $formHandler->process($form, $this->object);
        if ($process) {
            $this->container->get('session')->setFlash('success', $this->translator->trans('The changes have been saved successfully'));

            return new RedirectResponse($this->admin->genUrl('index'));
        }

        return $this->render($this->admin->getTemplate('edit'), array('form' => $form->createView(), 'id' => $this->id));
    }

    public function deleteAction()
    {
        $this->check('delete');

        $this->manager->delete($this->object);

        $this->container->get('session')->setFlash('success', $this->translator->trans('The removal was performed successfully'));

        return new RedirectResponse($this->admin->genUrl('index'));
    }

    public function changeAction()
    {
        $this->manager->change($this->object, $this->request->query->get('field'));

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
        $objects = $this->manager->findBy($criteria, array(), $orderBy)->getQuery()->execute();

        $this->manager->savePosition($objects, $disposition);

        return new Response();
    }

    protected function init()
    {
        $adminId = $this->request->attributes->get('_admin');
        if (!$this->container->has($adminId)) {
            throw new NotFoundHttpException('The service "'.$adminId.'" does not exist.');
        }

        $this->admin = $this->container->get($adminId);
        $this->manager = $this->admin->getModelManager();

        $this->admin->query->set('page', $this->request->query->get('page'));
        $this->admin->query->set('q', $this->request->query->get('q'));
        $this->admin->query->set('parentId', $this->parentId);
        $this->admin->query->set('filter', $this->request->query->get('filter'));

        if ($this->id) {
            $this->object = $this->manager->findBy(array('a.id' => $this->id), array(), array(), null, null, false)->getQuery()->getOneOrNullResult();
            if (!$this->object) {
                throw new NotFoundHttpException();
            }
            $this->admin->setObject($this->object);
        }
    }

    protected function check($role)
    {
        if (!$this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !$this->admin->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }

    protected function configureListQuery($qb)
    {
    }
}
