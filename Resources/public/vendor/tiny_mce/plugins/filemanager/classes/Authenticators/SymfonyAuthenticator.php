<?php

umask(0000);
require_once $_SERVER['DOCUMENT_ROOT'].'/../app/bootstrap.php.cache';
require_once $_SERVER['DOCUMENT_ROOT'].'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

use Symfony\Component\HttpFoundation\Request;

class Moxiecode_SymfonyAuthenticator extends Moxiecode_ManagerPlugin
{
    function Moxiecode_SymfonyAuthenticator()
    {
    }

    function onAuthenticate(&$man)
    {
        $kernel = new AppKernel('prod', false);
        $kernel->loadClassCache();
        //$kernel = new AppCache($kernel);
        $kernel->handle(Request::createFromGlobals());

        return $kernel->getContainer()->get('security.context')->isGranted('ROLE_ADMIN');
    }
}

$man->registerPlugin("SymfonyAuthenticator", new Moxiecode_SymfonyAuthenticator());
