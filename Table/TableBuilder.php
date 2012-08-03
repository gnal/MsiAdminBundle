<?php

namespace Msi\Bundle\AdminBundle\Table;

class TableBuilder
{
    protected $fields = array();

    public function add($name, $type = null, array $options = array())
    {
        if ($type === null) $type = 'text';

        $this->fields[$name] = array('type' => $type, 'options' => $options);

        return $this;
    }

    public function buildColumns()
    {
        $columns = array();

        foreach ($this->fields as $name => $builder) {
            $class = 'Msi\Bundle\AdminBundle\Table\Column\\'.ucfirst($builder['type']).'Column';
            $columns[$name] = new $class($name, $builder);
        }

        return $columns;
    }

    public function getTable()
    {
        return new Table($this->buildColumns());
    }
}
