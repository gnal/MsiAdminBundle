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

        if (!in_array($request->getSession()->getLocale(), $this->translationLocales)) {
            $request->getSession()->setLocale(array_shift($this->translationLocales));

            throw new NotFoundHttpException();
        }
    }
}
