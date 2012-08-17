<?php

namespace Msi\Bundle\AdminBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RequestListener
{
    private $appLocales;

    public function __construct($appLocales)
    {
        $this->appLocales = $appLocales;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!in_array($request->getLocale(), $this->appLocales)) {
            $request->setLocale(array_shift($this->appLocales));
            throw new NotFoundHttpException('Locale "'.$request->getLocale().'" is not allowed');
        }
    }
}
