<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class FileColumn extends BaseColumn
{
    public function getDefaultOptions()
    {
        return array(
            'path' => '',
        );
    }
}
