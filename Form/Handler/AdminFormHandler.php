<?php

namespace Msi\Bundle\AdminBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;

class AdminFormHandler
{
    protected $request;
    protected $admin;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function process($form, $entity)
    {
        $form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $form->bind($this->request);

            if ($form->isValid()) {
                $isNew = $entity->getId() ? false : true;

                if ($this->admin->hasParent() && !$entity->getId()) {
                    $setter = 'set'.$this->admin->getParent()->getClassName();
                    $entity->$setter($this->admin->getParentObject());
                }

                if ($isNew) {
                    $this->admin->prePersist($entity);
                } else {
                    $this->admin->preUpdate($entity);
                }

                $this->admin->getObjectManager()->save($entity);

                if ($isNew) {
                    $this->admin->postPersist($entity);
                } else {
                    $this->admin->postUpdate($entity);
                }

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
}
