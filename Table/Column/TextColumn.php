<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class TextColumn extends BaseColumn
{
    public function getDefaultOptions()
    {
        return array(
            'edit' => false,
        );
    }
}
