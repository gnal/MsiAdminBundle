<?php

namespace Msi\Bundle\AdminBundle\DataTable\Column;

class DateColumn extends Column
{
    public function render()
    {
        $getter = $this->getter;

        $html = $this->object->$getter()->format($this->options['format']);

        return '<td>'.$html.'</td>';
    }

    public function getDefaultOptions()
    {
        return array(
            'format' => 'd-m-Y',
        );
    }
}
