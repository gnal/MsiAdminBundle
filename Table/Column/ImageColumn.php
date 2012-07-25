<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class ImageColumn extends BaseColumn
{
    public function getDefaultOptions()
    {
        return array(
            'path' => '',
        );
    }
}
