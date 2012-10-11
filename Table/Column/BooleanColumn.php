<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class BooleanColumn extends BaseColumn
{
    public function getDefaultOptions()
    {
        return array(
            'attr' => array('style' => 'text-align:center;'),
            'badge_true' => 'badge-success',
            'badge_false' => '',
            'icon_true' => 'icon-ok',
            'icon_false' => 'icon-ok',
        );
    }
}
