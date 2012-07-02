<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

abstract class Column
{
    protected $name;
    protected $object;
    protected $value;
    protected $options = array();
    protected $admin;

    public function __construct($name, $options, $admin)
    {
        $this->name = $name;
        $this->admin = $admin;

        $this->set('label', $name);
        $this->set('attr', array());

        $this->options = array_merge($this->options, $this->getDefaultOptions(), $options);
    }

    public function setObject($object)
    {
        $this->object = $object;

        if ($this->name) {
            $getter = 'get'.ucfirst($this->name);

            if (!method_exists($this->object, $getter)) {
                $this->value = $this->object->getTranslation()->$getter();
            } else {
                $this->value = $this->object->$getter();
            }
        }

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
