<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class TextColumn extends Column
{
    public function render()
    {
        $getter = $this->getter;

        if ($this->options['edit'] === false)
            $html = $this->object->$getter();
        else
            $html = '<a href="'.$this->admin->genUrl('edit', array('id' => $this->object->getId())).'">'.$this->object->$getter().'</a>';

        return '<td>'.$html.'</td>';
    }

    public function getDefaultOptions()
    {
        return array(
            'edit' => false,
        );
    }
}
