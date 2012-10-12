<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

abstract class BaseColumn
{
    protected $name;
    protected $object;
    protected $value;
    protected $type;
    protected $options = array();
    protected $translationValues = array();

    public function __construct($name, $builder)
    {
        $this->name = $name;
        $this->type = $builder['type'];

        $this->set('label', $name);
        $this->set('attr', array());

        $this->options = array_merge($this->options, $this->getDefaultOptions(), $builder['options']);
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
                // translation fallback
                $this->value = $this->object->getTranslation()->$getter();
                if (!$this->value) {
                    foreach ($this->object->getTranslations() as $translation) {
                        if ($this->value = $translation->$getter()) {
                            break;
                        }
                    }
                }
                foreach ($this->object->getTranslations() as $translation) {
                    $this->translationValues[$translation->getLocale()] = $translation->$getter();
                }
                // order translation in the good order par rapport a la request locale
                $requestLocale = $this->object->getRequestLocale();
                if (isset($this->translationValues[$requestLocale])) {
                    $foo = $this->translationValues[$requestLocale];
                    unset($this->translationValues[$requestLocale]);
                    $this->translationValues[$requestLocale] = $foo;
                    $this->translationValues = array_reverse($this->translationValues);
                }
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

    public function getOptions()
    {
        return $this->options;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getTranslationValues()
    {
        return $this->translationValues;
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
