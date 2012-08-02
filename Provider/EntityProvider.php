<?php

namespace Msi\Bundle\AdminBundle\Provider;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityProvider implements EntityProviderInterface
{
    protected $entity;
    protected $translationLocales;
    protected $modelManager;

    public function __construct($translationLocales)
    {
        $this->translationLocales = $translationLocales;
    }

    public function get($id)
    {
        if ($id) {
            $this->entity = $this->modelManager->findBy(array('a.id' => $id), array(), array(), null, null, false)->getQuery()->getOneOrNullResult();
            if (!$this->entity) {
                throw new NotFoundHttpException();
            }
            if ($this->modelManager->isTranslatable()) {
                $this->entity->createTranslations($this->translationLocales);
            }
        } else {
            if ($this->modelManager->isTranslatable()) {
                $this->entity = $this->modelManager->create($this->translationLocales);
            } else {
                $this->entity = $this->modelManager->create();
            }
        }

        return $this->entity;
    }

    public function setModelManager($modelManager)
    {
        $this->modelManager = $modelManager;

        return $this;
    }
}
