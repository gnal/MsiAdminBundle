<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class DateColumn extends Column
{
    public function render()
    {
        $html = $this->value->format($this->options['format']);

        return '<td>'.$html.'</td>';
    }

    public function getDefaultOptions()
    {
        return array(
            'format' => 'd-m-Y',
        );
    }
}
