<?php

namespace Msi\Bundle\AdminBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;
use Msi\Bundle\AdminBundle\Model\FileInterface;

class FileListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        );
    }

    public function prePersist(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof FileInterface) {
            $this->preUpload($entity);
        }
    }

    public function preUpdate(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof FileInterface) {
            $this->preUpload($entity);
            $em   = $e->getEntityManager();
            $uow  = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($entity));
            $uow->recomputeSingleEntityChangeSet($meta, $entity);
        }
    }

    public function postPersist(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof FileInterface) {
            $this->postUpload($entity);
        }
    }

    public function postUpdate(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof FileInterface) {
            $this->postUpload($entity);
        }
    }

    public function postRemove(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof FileInterface) {
            $this->removeUpload($entity);
        }
    }

    protected function preUpload($entity)
    {
        $file = $entity->getFile();

        if ($file === null) return;

        $ext = $file->guessExtension();

        $allowedExt = $entity->getAllowedExt();

        if (!empty($allowedExt) && !in_array($ext, $allowedExt)) {
            die('"'.$file->guessExtension().'" extension is not allowed. Allowed extensions are "'.implode('", "', $entity->getAllowedExt()).'".');
        }

        $this->removeUpload($entity);
        $entity->setFilename(uniqid(time()).'.'.$ext);
    }

    protected function postUpload($entity)
    {
        $file = $entity->getFile();

        if ($file === null) return;

        $file = $file->move($entity->getPath(), $entity->getFilename());

        $entity->processFile($file);

        unset($file);
    }

    protected function removeUpload($entity)
    {
        $file = $entity->getPath().'/'.$entity->getFilename();

        if (is_file($file)) unlink($file);
    }
}
