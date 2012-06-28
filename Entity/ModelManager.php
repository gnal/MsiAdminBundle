<?php

namespace Msi\Bundle\AdminBundle\Entity;

use Doctrine\ORM\EntityManager;

class ModelManager
{
    protected $em;
    protected $repository;
    protected $class;

    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($class);
        $this->class = $em->getClassMetadata($class)->name;
    }
    // Needs refactoring
    public function findBy(array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->repository->createQueryBuilder('a');

        if (null !== $criteria) {
            foreach ($criteria as $k => $v) {
                $token = 'a'.substr($k, strpos($k, '.') + 1);
                $qb->andWhere($k.' = :'.$token)->setParameter($token, $v);
            }
        }

        if (null !== $orderBy) {
            foreach ($orderBy as $k => $v) {
                $qb->addOrderBy($k, $v);
            }
        }

        if (null !== $limit)
            $qb->setMaxResults($limit);

        if (null !== $offset)
            $qb->setFirstResult($offset);

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
