<?php

namespace Msi\Bundle\AdminBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RequestListener
{
    protected $locales;

    public function __construct($locales)
    {
        $this->locales = $locales;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $locale = $request->getLocale();

        if (!in_array($locale, $this->locales)) {
            $request->setLocale(array_shift($this->locales));
            throw new NotFoundHttpException('Locale "'.$locale.'" is not allowed');
        }
    }
}
