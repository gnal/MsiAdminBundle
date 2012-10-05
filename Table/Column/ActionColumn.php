<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class ActionColumn extends BaseColumn
{
    public function getDefaultOptions()
    {
        return array(
            'tree' => false,
            'delete' => array(
                'ajax' => true,
            ),
            'actions' => array(),
            'attr' => array('class' => 'span1'),
        );
    }
}
