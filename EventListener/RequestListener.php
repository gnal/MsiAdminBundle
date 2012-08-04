<?php

namespace Msi\Bundle\AdminBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RequestListener
{
    protected $translationLocales;

    public function __construct($translationLocales)
    {
        $this->translationLocales = $translationLocales;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $locale = $request->getLocale();

        if (!in_array($locale, $this->translationLocales)) {
            $request->setLocale(array_shift($this->translationLocales));
            throw new NotFoundHttpException('Locale "'.$locale.'" is not allowed');
        }
    }
}
