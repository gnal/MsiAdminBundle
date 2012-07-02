<?php

namespace Msi\Bundle\AdminBundle\Entity;

class Translatable
{
    protected $translation;

    protected $locale;

    public function getTranslation()
    {
        $locale = $this->locale ?: 'en';

        $translations = $this->getTranslations()->filter(function($translation) use ($locale) {
            return $locale === $translation->getLocale();
        });

        return $translations->first();
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
