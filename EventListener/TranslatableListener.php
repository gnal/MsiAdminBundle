<?php

namespace Msi\Bundle\AdminBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TranslatableListener implements EventSubscriber
{
    protected $container;

    public function __construct(ContainerInterface $container)
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
        $em = $e->getEntityManager();
        $metadata = $em->getClassMetadata(get_class($entity));

        if (is_subclass_of($metadata->rootEntityName, 'Msi\Bundle\AdminBundle\Entity\Translatable')) {
            $entity->setRequestLocale($this->container->get('request')->getLocale());
        }
    }
}
