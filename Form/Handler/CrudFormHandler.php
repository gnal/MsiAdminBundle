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

    public function process($form, $entity)
    {
        $form->setData($entity);

        if ($this->request->getMethod() === 'POST') {
            $form->bindRequest($this->request);

            if ($form->isValid()) {
                $this->onSuccess($entity);

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
            $setter = 'set'.$this->admin->getParent()->getClassName();
            $entity->$setter($this->admin->getParentEntity());
        }
        $this->admin->getModelManager()->save($entity);
    }
}
