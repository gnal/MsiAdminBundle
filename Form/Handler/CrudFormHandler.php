<?php

namespace Msi\Bundle\AdminBundle\Form\Handler;

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

    protected function onSuccess($object)
    {
        if ($this->admin->hasParent()) {
            $parent = $this->admin->getParent()->getModelManager()->findBy(array('a.id' => $this->request->query->get('parentId')), array(), array(), 1)->getQuery()->getSingleResult();
            $setter = 'set'.ucfirst($this->admin->getParent()->getClassName());
            $object->$setter($parent);
        }

        $this->admin->getModelManager()->save($object);
    }
}
