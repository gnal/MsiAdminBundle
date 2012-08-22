<?php

namespace Msi\Bundle\AdminBundle\Entity;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseManager
{
    protected $em;
    protected $repository;
    protected $class;
    protected $metadata;
    protected $appLocales;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function findBy(array $where = array(), array $join = array(), array $orderBy = array(), $limit = null, $offset = null)
    {
        $qb = $this->repository->createQueryBuilder('a');

        $i = 1;
        foreach ($where as $k => $v) {
            $token = 'eqMatch'.$i;
            $qb->andWhere($qb->expr()->eq($k, ':'.$token))->setParameter($token, $v);
            $i++;
        }

        foreach ($join as $k => $v) {
            $qb->leftJoin($k, $v);
            $qb->addSelect($v);
        }

        foreach ($orderBy as $k => $v) {
            $qb->addOrderBy($k, $v);
        }

        if (null !== $limit)
            $qb->setMaxResults($limit);

        if (null !== $offset)
            $qb->setFirstResult($offset);

        if ($this->isTranslatable()) {
            $qb->leftJoin('a.translations', 't');
            $qb->addSelect('t');
        }

        return $qb;
    }

    public function findByQ($q, array $searchFields, array $where = array(), array $join = array())
    {
        $qb = $this->repository->createQueryBuilder('a');

        $strings = explode(' ', $q);

        $orX = $qb->expr()->orX();
        $i = 1;
        foreach ($searchFields as $field) {
            foreach ($strings as $str) {
                $orX->add($qb->expr()->like($field, ':likeMatch'.$i));
                $qb->setParameter('likeMatch'.$i, '%'.$str.'%');
                $i++;
            }
        }

        $qb->andWhere($orX);

        if ($this->isTranslatable()) {
            $qb->leftJoin('a.translations', 't');
            $qb->addSelect('t');
        }

        $i = 1;
        foreach ($where as $key => $val) {
            $qb->andWhere($qb->expr()->eq($key, ':match'.$i));
            $qb->setParameter('match'.$i, $val);
            $i++;
        }

        foreach ($join as $k => $v) {
            $qb->leftJoin($k, $v);
            $qb->addSelect($v);
        }

        return $qb;
    }

    public function findOneOrCreate($id = null)
    {
        if ($id) {
            $object = $this->findBy(array('a.id' => $id))->getQuery()->getOneOrNullResult();
            if (!$object) {
                throw new NotFoundHttpException();
            }
        } else {
            $object = $this->create();
        }

        if ($this->isTranslatable()) {
            $object->createTranslations($this->class.'Translation', $this->appLocales);
        }

        return $object;
    }

    public function savePosition($objects, $disposition)
    {
        $i = 1;
        $l = 0;
        foreach ($objects as $object) {
            if (in_array($object->getId(), $disposition)) {
                $object->setPosition($i + array_search($object->getId(), $disposition) - $l);
                $l++;
            } else {
                $object->setPosition($i);
            }
            $i++;
            $this->save($object);
        }
    }

    public function save($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function saveBatch($entity, $i)
    {
        $this->em->persist($entity);
        $batchSize = 20;
        if ($i % $batchSize === 0) {
            $this->em->flush();
            $this->em->clear();
        }
    }

    public function delete($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function change($entity, $field)
    {
        $getter = 'get'.ucfirst($field);
        $setter = 'set'.ucfirst($field);

        $entity->$getter() ? $entity->$setter(false) : $entity->$setter(true);

        $this->save($entity);
    }

    public function moveUp($entity)
    {
        $this->repository->moveUp($entity, 1);
        $this->save($entity);
    }

    public function moveDown($entity)
    {
        $this->repository->moveDown($entity, 1);
        $this->save($entity);
    }

    public function create()
    {
        return new $this->class();
    }

    public function getClass()
    {
        return $this->class;
    }

    public function isTranslatable()
    {
        return is_subclass_of($this->class, 'Msi\Bundle\AdminBundle\Entity\Translatable');
    }

    public function getAppLocales()
    {
        return $this->appLocales;
    }

    public function setAppLocales($appLocales)
    {
        $this->appLocales = $appLocales;

        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($this->class);

        return $this;
    }
}
