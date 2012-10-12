<?php

namespace Msi\Bundle\AdminBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;

class AdminController extends ContainerAware
{
    protected $admin;
    protected $request;

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

        $qb = $this->getIndexQueryBuilder($this->request, $this->admin);

        // Filters
        $parameters = array();
        $filterForm = $this->admin->getForm('filter');

        if ($filterForm) {
            $this->getFilterFormHandler()->process($filterForm, $this->admin->getObject(), $qb);
            $parameters['filterForm'] = $filterForm->createView();
        }

        // Pagination
        $paginator = $this->container->get('msi_paginator.paginator.factory')->create(array('attr' => array('class' => 'pull-right')));
        $paginator->paginate($qb, $this->request->query->get('page', 1), $this->container->get('session')->get('limit', 10));

        // Table
        $table = $this->admin->getTable('index');
        if (property_exists($this->admin->getObjectManager()->getClass(), 'position')) {
            $table->setSortable(true);
        }
        $table->setData($paginator->getResult());
        $table->setPaginator($paginator);

        $parameters['paginator'] = $paginator;

        return $this->render('MsiAdminBundle:Admin:index.html.twig', $parameters);
    }

    public function showAction()
    {
        $this->check('read');

        $table = $this->admin->getTable('show');
        if (!$table) {
            return new RedirectResponse($this->admin->genUrl('edit', array('id' => $this->admin->getObject()->getId())));
        }

        $table->setData(new ArrayCollection(array($this->admin->getObject())));

        return $this->render('MsiAdminBundle:Admin:show.html.twig');
    }

    public function newAction()
    {
        $this->check('create');

        if ($this->processForm()) {
            return $this->onSuccess();
        }

        return $this->render('MsiAdminBundle:Admin:new.html.twig', array('form' => $this->admin->getForm()->createView()));
    }

    public function editAction()
    {
        $this->check('update');

        if ($this->processForm()) {
            return $this->onSuccess();
        }

        return $this->render('MsiAdminBundle:Admin:edit.html.twig', array('form' => $this->admin->getForm()->createView(), 'id' => $this->admin->getObject()->getId()));
    }

    public function deleteAction()
    {
        $this->check('delete');

        $this->admin->getObjectManager()->delete($this->admin->getObject());

        return $this->onSuccess();
    }

    public function removeFileAction()
    {
        $this->check('update');

        $file = $this->admin->getObject()->getPath().'/'.$this->admin->getObject()->getFilename();
        if (is_file($file)) unlink($file);

        return $this->onSuccess();
    }

    public function changeAction()
    {
        $this->check('update');

        $this->admin->getObjectManager()->change($this->admin->getObject(), $this->request);

        return $this->onSuccess();
    }

    public function sortAction()
    {
        $this->check('update');

        $disposition = $this->request->query->get('disposition');
        $criteria = array();

        if ($this->request->query->get('parentId')) {
            $criteria['a.'.lcfirst($this->admin->getParent()->getClassName())] = $this->request->query->get('parentId');
        }
        $orderBy['a.position'] = 'ASC';
        $objects = $this->admin->getObjectManager()->getFindByQueryBuilder($criteria, array(), $orderBy)->getQuery()->execute();

        $this->admin->getObjectManager()->savePosition($objects, $disposition);

        return new Response();
    }

    // override this method if you need a custom form handler for your filters
    protected function getFilterFormHandler()
    {
        return $this->container->get('msi_admin.filter.form.handler');
    }

    // override this method if you need a custom form handler for your crud
    protected function getCrudFormHandler()
    {
        return $this->container->get('msi_admin.admin.form.handler');
    }

    protected function getIndexQueryBuilder()
    {
        $where = array();
        $join = array();
        $sort = array();

        // If is sortable.
        if (property_exists($this->admin->getObject(), 'position')) {
            $sort['a.position'] = 'ASC';
        }

        // If is nested.
        if ($this->admin->hasParent() && $this->request->query->get('parentId')) {
            $where['a.'.strtolower($this->admin->getParent()->getClassName())] = $this->request->query->get('parentId');
        }

        if (!$this->request->query->get('q')) {
            $qb = $this->admin->getObjectManager()->getFindByQueryBuilder($where, $join, $sort);
        } else {
            $qb = $this->admin->getObjectManager()->getSearchQueryBuilder($this->request->query->get('q'), $this->admin->getOption('search_fields'), $where, $join, $sort);
        }

        $this->configureIndexQueryBuilder($qb);

        return $qb;
    }

    protected function configureIndexQueryBuilder(QueryBuilder $qb)
    {
    }

    protected function processForm()
    {
        $form = $this->admin->getForm();
        $process = $this->getCrudFormHandler()->setAdmin($this->admin)->process($form, $this->admin->getObject());

        return $process;
    }

    protected function onSuccess()
    {
        if ($this->request->isXmlHttpRequest()) {
            return new Response('ok');
        } else {
            $this->container->get('session')->getFlashBag()->add('success', $this->container->get('translator')->trans('The action was executed successfully!'));
            return new RedirectResponse($this->admin->genUrl('index'));
        }
    }

    protected function check($role)
    {
        if (!$this->admin->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }

    protected function init()
    {
        $this->admin = $this->container->get($this->request->attributes->get('_admin'));

        $this->admin->query->set('page', $this->request->query->get('page'));
        $this->admin->query->set('q', $this->request->query->get('q'));
        $this->admin->query->set('parentId', $this->request->query->get('parentId'));
        $this->admin->query->set('filter', $this->request->query->get('filter'));
    }
}
