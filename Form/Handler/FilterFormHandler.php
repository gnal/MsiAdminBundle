<?php

namespace Msi\Bundle\AdminBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;

class FilterFormHandler
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function process($form, $qb)
    {
        $filter = $this->request->query->get('filter');
        if ($filter) {
            $form->bindRequest($this->request);

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
                } else if ($field !== '_token' && $value) {
                    $qb->andWhere('a.'.$field.' = :filter'.$i)->setParameter('filter'.$i, $value);
                    $i++;
                }
            }
        }
    }
}
