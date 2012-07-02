<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class BoolColumn extends Column
{
    public function render()
    {
        $content = $this->value ? $this->get('label_true') : $this->get('label_false');

        $html = '<a href="#" class="action-change" data-url="'.$this->admin->genUrl('change', array('field' => $this->name,'id' => $this->object->getId())).'">'.$content.'</a>';

        return '<td class="text-center">'.$html.'</td>';
    }

    public function getDefaultOptions()
    {
        return array(
            'attr' => array(
                'class' => 'span1',
            ),
            'label_true' => '<span class="badge badge-success"><i class="icon-ok icon-white"><span class="hide">1</span></i></span>',
            'label_false' => '<span class="badge"><i class="icon-ok icon-white"><span class="hide">0</span></i></span>',
        );
    }
}
