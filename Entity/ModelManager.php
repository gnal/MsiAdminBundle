<?php

namespace Msi\Bundle\AdminBundle\Entity;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ModelManager
{
    protected $em;
    protected $repository;
    protected $class;
    protected $session;

    public function __construct(EntityManager $em, $class, $session)
    {
        $this->em = $em;
        $this->session = $session;
        $this->repository = $em->getRepository($class);
        $this->class = $em->getClassMetadata($class)->name;
    }

    public function findBy(array $criteria = array(), array $joins = array(), array $orderBy = array(), $limit = null, $offset = null, $translate = true)
    {
        $select = array('a');
        $qb = $this->repository->createQueryBuilder('a');

        $i = 1;
        foreach ($criteria as $k => $v) {
            $token = 'eqMatch'.$i;
            $qb->andWhere($k.' = :'.$token)->setParameter($token, $v);
            $i++;
        }

        foreach ($joins as $k => $v) {
            $qb->leftJoin($k, $v);
            $select[] = $v;
        }

        foreach ($orderBy as $k => $v) {
            $qb->addOrderBy($k, $v);
        }

        if (null !== $limit)
            $qb->setMaxResults($limit);

        if (null !== $offset)
            $qb->setFirstResult($offset);

        if ($this->isTranslatable() && $translate === true) {
            $qb
                ->andWhere('t.locale = :locale')->setParameter('locale', $this->session->getLocale())
                ->leftJoin('a.translations', 't')
            ;
            $select[] = 't';
        }

        $qb->select($select);

        return $qb;
    }

    public function findByQ($q, array $likeFields, array $criteria = array())
    {
        $qb = $this->repository->createQueryBuilder('a');
        $select = array('a');

        $strings = explode(' ', $q);

        $orX = $qb->expr()->orX();
        $i = 1;
        foreach ($likeFields as $field) {
            foreach ($strings as $str) {
                $alias = property_exists($this->class, $field) ? 'a': 't';
                $orX->add($qb->expr()->like($alias.'.'.$field, ':likeMatch'.$i));
                $qb->setParameter('likeMatch'.$i, '%'.$str.'%');
                $i++;
            }
        }

        $qb->andWhere($orX);

        if ($this->isTranslatable()) {
            $qb->leftJoin('a.translations', 't');
            $select[] = 't';
        }

        $i = 1;
        foreach ($criteria as $key => $val) {
            $qb->andWhere($qb->expr()->eq($key, ':match'.$i));
            $qb->setParameter('match'.$i, $val);
            $i++;
        }

        $qb->select($select);

        return $qb;
    }

    public function getAdminEntity($id, $translationLocales)
    {
        if ($id) {
            $entity = $this->findBy(array('a.id' => $id), array(), array(), null, null, false)->getQuery()->getOneOrNullResult();
            if (!$entity) {
                throw new NotFoundHttpException();
            }
        } else {
            $entity = $this->create();
        }

        if ($this->isTranslatable()) {
            $entity->createTranslations($this->getClass().'Translation', $translationLocales);
        }

        return $entity;
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
        return new $this->getClass();
    }

    public function getClass()
    {
        return $this->class;
    }

    public function isTranslatable()
    {
        return is_subclass_of($this->class, 'Msi\Bundle\AdminBundle\Entity\Translatable');
    }
}
