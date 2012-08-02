<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class TreeColumn extends BaseColumn
{
    public function getDefaultOptions()
    {
        return array(
            'edit' => false,
        );
    }
}
