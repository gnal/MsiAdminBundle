<?php

namespace Msi\Bundle\AdminBundle\DataTable\Column;

class ActionColumn extends Column
{
    public function render()
    {
        $html = '<td class="text-right">';

        if ($this->admin->hasChild())
            $html .= '<a class="btn btn-mini" href="'.$this->admin->getChild()->genUrl('index', array('parentId' => $this->object->getId())).'">'.$this->options['nested']['label'].'</a> ';

        $html .= $this->renderEdit();
        $html .= $this->renderDelete();

        $html .= '</td>';

        return $html;
    }

    public function renderEdit()
    {
        $html = '';
        $roles = $this->options['edit']['roles'];

        if ($this->admin->getSecurityContext()->isGranted($roles)) {
            $html .= '<a class="btn btn-mini" href="'.$this->admin->genUrl('edit', array('id' => $this->object->getId())).'">'.$this->options['edit']['label'].'</a> ';
        }

        return $html;
    }

    public function renderDelete()
    {
        $html = '';
        $roles = $this->options['delete']['roles'];

        if ($this->admin->getSecurityContext()->isGranted($roles)) {
            $html .= '<a class="action-delete btn btn-mini" href="#" data-url="'.$this->admin->genUrl('delete', array('id' => $this->object->getId())).'">'.$this->options['delete']['label'].'</a> ';
        }

        return $html;
    }

    public function getDefaultOptions()
    {
        return array(
            'nested' => array(
                'label' => $this->admin->getChild() ? $this->admin->getChild()->getLabel(2) : null,
                'roles' => 'ROLE_ADMIN',
            ),
            'edit' => array(
                'label' => 'Edit',
                'roles' => 'ROLE_ADMIN',
            ),
            'delete' => array(
                'label' => 'Delete',
                'roles' => 'ROLE_ADMIN',
            ),
        );
    }
}
