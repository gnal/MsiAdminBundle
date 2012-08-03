<?php

namespace Msi\Bundle\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

abstract class Translatable
{
    public function createTranslations(array $locales)
    {
        foreach ($locales as $locale) {
            if (!$this->hasTranslationForLocale($locale)) {
                $class = get_class($this).'Translation';
                $translation = new $class();
                $translation->setLocale($locale)->setObject($this);
                $this->getTranslations()->add($translation);
            }
        }
    }

    public function getTranslation()
    {
        if ($this->translations->count() === 0) {
            die('Translatable entity '.get_class($this).' has no translation. Did you forget to create it/them?');
        }

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

    public function hasTranslationForLocale($locale)
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return true;
            }
        }

        return false;
    }
}
