<?php

namespace Msi\Bundle\AdminBundle\Form\Handler;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        if ($this->admin->hasParent() && !$entity->getId()) {
            $accessor = 'get'.ucfirst($this->admin->getParentFieldName());
            $entity->$accessor()->add($this->admin->getParentEntity());
            $accessor = 'get'.ucfirst($this->admin->getParent()->getChildFieldName());
            $this->admin->getParentEntity()->$accessor()->add($entity);

            $this->admin->getContainer()->get('doctrine')->getEntityManager()->flush();
        } else {
            $this->admin->getModelManager()->save($entity);
        }
    }
}
