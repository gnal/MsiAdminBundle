<?php

namespace Msi\Bundle\AdminBundle\Renderer;

use Msi\Bundle\AdminBundle\DataTable\DataTable;

class DataTableRenderer extends Renderer
{
    public function render(DataTable $dt)
    {
        $columns = $dt->getColumns();

        $html = '<table class="table table-bordered table-striped">';
        $html .= $this->renderHead($columns);

        foreach ($dt->getData() as $element) {
            $html .= $this->renderRow($columns, $element);
        }

        $html .= '</table>';

        if ($dt->getData()->count()) {
            $html .= $this->renderFoot($dt);
        }

        return $html;
    }

    public function renderHead($columns)
    {
        $html = '<thead><tr>';
        foreach ($columns as $col) {
            $html .= '<th'.$this->renderAttr($col->get('attr')).'>';
            $html .= ucfirst($col->get('label'));
            $html .= '</th>';
        }
        $html .= '</tr></thead>';

        return $html;
    }

    public function renderFoot($dt)
    {
        $paginator = $dt->getPaginator();

        $content = $dt->getAdmin()->getTranslator()->transChoice('Showing %from% to %to% of %of% entries', $paginator->getLength(), array(
            '%from%' => $paginator->getFrom(),
            '%to%' => $paginator->getTo(),
            '%of%' => $paginator->getLength(),
        ), 'MsiAdminBundle');

        $html = '<div style="line-height: 36px;float: left;">'.$content.'</div>'.$paginator->render().'</td>';

        return $html;
    }

    public function renderRow($columns, $element)
    {
        $html = '<tr data-id="'.$element->getId().'" id="el'.$element->getId().'">';
        foreach ($columns as $col) {
            $col->setObject($element);
            $html .= $col->render();
        }

        return $html.'</tr>';
    }

    public function renderColumn($column)
    {
        $html = '<td'.$this->renderAttr($column->get('attr')).'>';

        return $html.'</td>';
    }
}
