<?php

namespace Msi\Bundle\AdminBundle\Renderer;

abstract class Renderer
{
    public function renderAttr($attr)
    {
        $html = '';
        foreach ($attr as $name => $val) {
            $html .= ' '.$name.'="'.$val.'"';
        }
        return $html;
    }
}
