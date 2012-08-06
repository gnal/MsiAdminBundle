<?php

namespace Msi\Bundle\AdminBundle\Form\Handler;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\Common\Collections\Collection;

class CrudFormHandler
{
    protected $request;
    protected $admin;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function process($form, $object)
    {
        $form->setData($object);

        if ($this->request->getMethod() === 'POST') {
            $form->bindRequest($this->request);

            if ($form->isValid()) {
                $this->onSuccess($object);

                return true;
            }
        }

        return false;
    }

    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    protected function onSuccess($entity)
    {
        $em = $this->admin->getContainer()->get('doctrine')->getEntityManager();

        if ($this->admin->hasParent() && !$entity->getId()) {
            $accessor = 'get'.ucfirst($this->admin->getParentFieldName());
            if ($entity->$accessor() instanceof Collection) {
                $entity->$accessor()->add($this->admin->getParentEntity());
            } else {
                $mutator = 'set'.ucfirst($this->admin->getParentFieldName());
                $entity->$mutator($this->admin->getParentEntity());
                $em->persist($entity);
            }

            $accessor = 'get'.ucfirst($this->admin->getParent()->getChildFieldName());
            $this->admin->getParentEntity()->$accessor()->add($entity);

            $em->flush();
        } else {
            $this->admin->getModelManager()->save($entity);
        }
    }
}
