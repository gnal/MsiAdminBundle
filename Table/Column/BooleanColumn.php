<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class BooleanColumn extends BaseColumn
{
    public function getDefaultOptions()
    {
        return array(
            // 'label_true' => '<span class="badge badge-success"><i class="icon-ok icon-white"><span class="hide">1</span></i></span>',
            // 'label_false' => '<span class="badge"><i class="icon-ok icon-white"><span class="hide">0</span></i></span>',
            'attr' => array('style' => 'text-align:center;'),
            'badge_true' => 'badge-success',
            'badge_false' => '',
            'icon_true' => 'icon-ok',
            'icon_false' => 'icon-ok',
        );
    }
}
