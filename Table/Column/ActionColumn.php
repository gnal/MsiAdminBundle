<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

class ActionColumn extends BaseColumn
{
    // public function render()
    // {
    //     $html = '<td class="text-right">';
    //     if (isset($this->options['actions'])) {
    //         foreach ($this->options['actions'] as $k => $v) {
    //             $html .= '<a class="btn btn-mini" href="'.$this->admin->genUrl($v, array('id' => $this->object->getId())).'">'.$k.'</a> ';
    //         }
    //     }

    //     if ($this->admin->hasChild())
    //         $html .= '<a class="btn btn-mini" href="'.$this->admin->getChild()->genUrl('index', array('parentId' => $this->object->getId())).'">'.$this->options['nested']['label'].'</a> ';

    //     $html .= $this->renderEdit();
    //     $html .= $this->renderDelete();

    //     $html .= '</td>';

    //     return $html;
    // }

    // public function renderEdit()
    // {
    //     $html = '';

    //     $html .= '<a class="btn btn-mini" href="'.$this->admin->genUrl('edit', array('id' => $this->object->getId())).'">'.$this->options['edit']['label'].'</a> ';

    //     return $html;
    // }

    // public function renderDelete()
    // {
    //     $html = '';

    //     $html .= '<a class="action-delete btn btn-mini" href="#" data-url="'.$this->admin->genUrl('delete', array('id' => $this->object->getId())).'">'.$this->options['delete']['label'].'</a> ';

    //     return $html;
    // }

    public function getDefaultOptions()
    {
        return array(
            'nested' => array(
                'label' => $this->admin->getChild() ? $this->admin->getChild()->getLabel(2) : null,
            ),
            'delete' => array(
                'ajax' => true,
            ),
            'actions' => array(),
        );
    }
}
