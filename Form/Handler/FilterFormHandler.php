<?php

namespace Msi\Bundle\AdminBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;

class FilterFormHandler
{
    protected $request;
    protected $em;

    public function __construct(Request $request, $em)
    {
        $this->request = $request;
        $this->em = $em;
    }

    public function process($form, $entity, $qb)
    {
        $mappings = $this->em->getClassMetadata(get_class($entity))->associationMappings;
        $filter = $this->request->query->get('filter');
        if ($filter) {
            $form->bind($this->request);

            $i = 1;
            foreach ($filter as $field => $value) {
                if (is_array($value)) {
                    $orX = $qb->expr()->orX();
                    $qb->leftJoin('a.'.$field, $field);
                    foreach ($value as $id) {
                        if ($id) {
                            $orX->add($qb->expr()->eq($field.'.id', ':filter'.$i));
                            $qb->setParameter('filter'.$i, $id);
                            $i++;
                        }
                    }
                    $qb->andWhere($orX);
                } else if ($field !== '_token' && $value !== null && $value !== '') {
                    if (isset($mappings[$field])) {
                        switch ($mappings[$field]['type']) {
                            case 8:
                                $qb->leftJoin('a.'.$field, $field);
                                $qb->andWhere($field.'.id = :filter'.$i)->setParameter('filter'.$i, $value);
                            case 2:
                                $qb->leftJoin('a.'.$field, $field);
                                $qb->andWhere($field.'.id = :filter'.$i)->setParameter('filter'.$i, $value);
                        }
                    } else {
                        $qb->andWhere('a.'.$field.' = :filter'.$i)->setParameter('filter'.$i, $value);
                    }
                    $i++;
                }
            }
        }
    }
}
