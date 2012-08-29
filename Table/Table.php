<?php

namespace Msi\Bundle\AdminBundle\Table;

use Doctrine\Common\Collections\Collection;

class Table
{
    private $columns;
    private $paginator;
    private $data;
    private $sortable = false;

    public function __construct($columns)
    {
        $this->columns = $columns;
    }

    public function getSortable()
    {
        return $this->sortable;
    }

    public function setSortable($sortable)
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function getLength()
    {
        $count = count($this->columns);

        return $this->sortable ? $count + 1 : $count;
    }

    public function setData(Collection $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function setPaginator($paginator)
    {
        $this->paginator = $paginator;
    }

    public function getPaginator()
    {
        return $this->paginator;
    }
}
