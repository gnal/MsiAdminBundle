<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class TextColumn extends BaseColumn
{
    public function render()
    {
        if ($this->options['edit'] === false)
            $html = $this->value;
        else
            $html = '<a href="'.$this->admin->genUrl('edit', array('id' => $this->object->getId())).'">'.$this->value.'</a>';

        return '<td>'.$html.'</td>';
    }

    public function getDefaultOptions()
    {
        return array(
            'edit' => false,
        );
    }
}
