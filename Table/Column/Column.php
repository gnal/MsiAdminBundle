<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

abstract class Column
{
    protected $name;

    protected $admin;

    protected $getter;

    protected $options = array();

    protected $object;

    public function __construct($name, $options, $admin)
    {
        $this->name = $name;
        $this->admin = $admin;

        $this->set('label', $name);
        $this->set('attr', array());

        $this->options = array_merge($this->options, $this->getDefaultOptions(), $options);
        $this->getter = 'get'.ucfirst($name);
    }

    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getName()
    {
        return $this->name;
    }

    public function get($name)
    {
        return $this->options[$name];
    }

    public function set($name, $val)
    {
        $this->options[$name] = $val;
    }

    abstract public function getDefaultOptions();
}
