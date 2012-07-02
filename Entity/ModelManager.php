<?php

namespace Msi\Bundle\AdminBundle\Entity;

use Doctrine\ORM\EntityManager;

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

    public function findBy(array $criteria = array(), array $join = array(), array $orderBy = array(), $limit = null, $offset = null, $translate = true)
    {
        $select = array('a');
        $qb = $this->repository->createQueryBuilder('a');

        foreach ($criteria as $k => $v) {
            $token = 'a'.substr($k, strpos($k, '.') + 1);
            $qb->andWhere($k.' = :'.$token)->setParameter($token, $v);
        }

        foreach ($join as $k => $v) {
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

        if (property_exists($this->class, 'translations') && $translate === true) {
            $qb
                ->andWhere('t.locale = :locale')->setParameter('locale', $this->session->getLocale())
                ->leftJoin('a.translations', 't')
            ;
            $select[] = 't';
        }

        $qb->select($select);

        return $qb;
    }

    public function findByFilter(array $criteria = null)
    {
        $qb = $this->repository->createQueryBuilder('a');

        if (null !== $criteria) {
            $j = 1;
            foreach ($criteria as $k => $v) {
                if (is_array($v)) {
                    $i = 1;
                    $orX = $qb->expr()->orX();
                    foreach ($v as $key => $value) {
                        $orX->add('a.'.$k.' = :match'.$i.$j);
                        $qb->setParameter('match'.$i.$j, $value);
                        $i++;
                    }

                    $qb->andWhere($orX);
                    $j++;
                } else {
                    if ($k !== '_token' && $v != null) {
                        $qb->andWhere('a.'.$k.' = :match'.$j);
                        $qb->setParameter('match'.$j, $v);
                        $j++;
                    }
                }
            }
        }

        return $qb;
    }

    public function findByQ($q, array $likeFields, array $criteria = array())
    {
        $qb = $this->repository->createQueryBuilder('a');

        $strings = explode(' ', $q);

        $orX = $qb->expr()->orX();
        foreach ($likeFields as $field) {
            $i = 1;
            foreach ($strings as $str) {
                $orX->add($qb->expr()->like('a.'.$field, ':likeMatch'.$i));
                $qb->setParameter('likeMatch'.$i, '%'.$str.'%');
                $i++;
            }
        }

        $qb->andWhere($orX);

        $i = 1;
        foreach ($criteria as $key => $val) {
            $qb->andWhere($qb->expr()->eq($key, ':match'.$i));
            $qb->setParameter('match'.$i, $val);
            $i++;
        }

        return $qb;
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

    public function save($object)
    {
        $this->em->persist($object);
        $this->em->flush();
    }

    public function delete($object)
    {
        $this->em->remove($object);
        $this->em->flush();
    }

    public function change($object, $field)
    {
        $getter = 'get'.ucfirst($field);
        $setter = 'set'.ucfirst($field);

        $object->$getter() ? $object->$setter(false) : $object->$setter(true);

        $this->save($object);
    }

    public function create()
    {
        return new $this->class;
    }

    public function getClass()
    {
        return $this->class;
    }
}
