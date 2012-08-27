<?php

namespace Msi\Bundle\AdminBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TranslatableListener implements EventSubscriber
{
    protected $container;
    protected $skipPostLoad = false;

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

        if (!$this->skipPostLoad && is_subclass_of($metadata->rootEntityName, 'Msi\Bundle\AdminBundle\Entity\Translatable')) {
            $entity->setRequestLocale($this->container->get('request')->getLocale());
        }
    }

    public function getSkipPostLoad()
    {
        return $this->skipPostLoad;
    }

    public function setSkipPostLoad($skipPostLoad)
    {
        $this->skipPostLoad = $skipPostLoad;

        return $this;
    }
}
