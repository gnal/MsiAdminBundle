<?php

namespace Msi\Bundle\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

abstract class Translatable
{
    public function __construct(array $locales)
    {
        $this->translations = new ArrayCollection();

        foreach ($locales as $locale) {
            $class = get_class($this).'Translation';
            $translation = new $class();
            $translation->setLocale($locale)->setObject($this);
            $this->getTranslations()->add($translation);
        }
    }

    public function getTranslation()
    {
        return $this->translations->first();
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations($translations)
    {
        $this->translations = $translations;

        return $this;
    }
}
