<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class DateColumn extends BaseColumn
{
    public function getDefaultOptions()
    {
        return array(
            'format' => 'Y-m-d',
        );
    }
}
