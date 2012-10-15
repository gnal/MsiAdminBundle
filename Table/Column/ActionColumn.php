<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ActionColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'tree' => false,
            'delete' => array(
                'ajax' => true,
            ),
            'actions' => array(),
            'attr' => array('class' => 'span1'),
        ));
    }
}
