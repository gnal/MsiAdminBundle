<?php

namespace Msi\Bundle\AdminBundle\Entity;

abstract class Translatable
{
    private $requestLocale;

    public function createTranslations($translationClass, array $appLocales)
    {
        foreach ($appLocales as $locale) {
            if (!$this->hasTranslation($locale)) {
                $translation = new $translationClass();
                $translation->setLocale($locale)->setObject($this);
                $this->getTranslations()->add($translation);
            }
        }
    }

    public function getTranslation($locale = null)
    {
        if ($this->translations->count() === 0) {
            die('Translatable entity '.get_class($this).' is supposed to have translations, but it has no translation. Did you forget to create them?');
        }

        if (null !== $locale) {
            foreach ($this->translations as $translation) {
                if ($locale === $translation->getLocale()) {
                    return $translation;
                }
            }
        }

        foreach ($this->translations as $translation) {
            if ($this->requestLocale === $translation->getLocale()) {
                return $translation;
            }
        }

        return $this->translations->first();
    }

    public function hasTranslation($locale)
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
