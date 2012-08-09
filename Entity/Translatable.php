<?php

namespace Msi\Bundle\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

abstract class Translatable
{
    protected $requestLocale;

    public function createTranslations($translationClass, array $locales)
    {
        foreach ($locales as $locale) {
            if (!$this->hasTranslationForLocale($locale)) {
                $translation = new $translationClass();
                $translation->setLocale($locale)->setObject($this);
                $this->getTranslations()->add($translation);
            }
        }
    }

    public function getTranslation()
    {
        if ($this->translations->count() === 0) {
            die('Translatable entity '.get_class($this).' has no translation. Did you forget to create them?');
        }

        foreach ($this->translations as $translation) {
            if ($this->requestLocale === $translation->getLocale()) {
                return $translation;
            }
        }

        return $this->translations->first();
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

    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations($translations)
    {
        $this->translations = $translations;

        return $this;
    }

    public function getRequestLocale()
    {
        return $this->requestLocale;
    }

    public function setRequestLocale($requestLocale)
    {
        $this->requestLocale = $requestLocale;

        return $this;
    }
}
