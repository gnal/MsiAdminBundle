<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

abstract class BaseColumn
{
    protected $name;
    protected $object;
    protected $value;
    protected $type;
    protected $options = array();

    protected $admin;

    public function __construct($name, $builder, $admin)
    {
        $this->name = $name;
        $this->admin = $admin;
        $this->type = $builder['type'];

        $this->set('label', $name);
        $this->set('attr', array());

        $this->options = array_merge($this->options, $this->getDefaultOptions(), $builder['options']);
    }

    public function render()
    {
        return $this->admin->getContainer()->get('templating')->render('MsiAdminBundle:Column:'.$this->type.'.html.twig', array('object' => $this->object, 'value' => $this->value, 'options' => $this->options));
    }

    public function setObject($object)
    {
        $this->object = $object;

        // If it's not the action column
        if ($this->name) {
            $pieces = explode('.', $this->name);
            $getter = 'get'.ucfirst($pieces[0]);

            // If the getter gets an array key (ex: settings in block)
            if (isset($pieces[1])) {
                $this->value = $this->object->$getter($pieces[1]);
            // Else translation
            } else if (!property_exists($this->object, $this->name)) {
                $this->value = $this->object->getTranslation()->$getter();
            // Else normal value
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
