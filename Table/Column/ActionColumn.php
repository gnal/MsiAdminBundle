<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class ActionColumn extends BaseColumn
{
    public function getDefaultOptions()
    {
        return array(
            'tree' => false,
            'nested' => array(
                'label' => $this->admin->getChild() ? $this->admin->getChild()->getLabel(2) : null,
            ),
            'delete' => array(
                'ajax' => true,
            ),
            'actions' => array(),
        );
    }
}
