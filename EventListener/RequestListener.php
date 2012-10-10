<?php

namespace Msi\Bundle\AdminBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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

        if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST && !in_array($request->getLocale(), $this->appLocales)) {
            $request->setLocale(array_shift($this->appLocales));
        }
    }
}
