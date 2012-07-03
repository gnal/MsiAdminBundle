<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class MenuColumn extends BaseColumn
{
    public function render()
    {
        $prefix = '';
        $i = $this->object->getLvl() - 1;

        for ($i=$this->object->getLvl() - 1; $i > 0; $i--) {
            $prefix .= '<i class="icon-arrow-right"></i> ';
        }

        if ($this->options['edit'] === false)
            $html = $prefix.$this->value;
        else
            $html = $prefix.'<a href="'.$this->admin->genUrl('edit', array('id' => $this->object->getId())).'">'.$this->value.'</a>';

        return '<td>'.$html.'</td>';
    }

    public function getDefaultOptions()
    {
        return array(
            'edit' => false,
        );
    }
}
