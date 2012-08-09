<?php

namespace Msi\Bundle\AdminBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;

class TranslatableListener implements EventSubscriber
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::postLoad,
        );
    }

    public function postLoad(EventArgs $e)
    {
        $entity = $e->getEntity();
        if (is_subclass_of($entity, 'Msi\Bundle\AdminBundle\Entity\Translatable')) {
            $entity->setRequestLocale($this->container->get('request')->getLocale());
        }
    }
}
