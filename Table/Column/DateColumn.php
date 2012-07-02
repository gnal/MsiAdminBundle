<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class DateColumn extends Column
{
    public function render()
    {
        $html = $this->value->format($this->options['format']);

        return '<td title="'.$this->options['format'].'">'.$html.'</td>';
    }

    public function getDefaultOptions()
    {
        return array(
            'format' => 'Y-m-d',
        );
    }
}
