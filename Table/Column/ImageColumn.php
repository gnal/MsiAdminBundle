<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class ImageColumn extends BaseColumn
{
    public function render()
    {
        $html = '<a target="_blank" href="'.$this->options['path'].$this->value.'"><img height="22" class="pull-left" src="'.$this->options['path'].$this->value.'" alt="0"></a>';

        return '<td>'.$html.'</td>';
    }

    public function getDefaultOptions()
    {
        return array(
            'path' => '/uploads/',
        );
    }
}
