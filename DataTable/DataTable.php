<?php

namespace Msi\Bundle\AdminBundle\DataTable;

use Doctrine\Common\Collections\Collection;

class DataTable
{
    private $columns = array();

    private $paginator;

    private $admin;

    private $data;

    public function __construct($columns, $admin)
    {
        $this->columns = $columns;
        $this->admin = $admin;
    }

    public function setData(Collection $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    public function getAdmin()
    {
        return $this->admin;
    }

    public function getColumns()
    {
        return $this->columns;
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
