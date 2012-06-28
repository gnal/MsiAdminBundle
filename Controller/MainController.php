<?php

namespace Msi\Bundle\AdminBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends ContainerAware
{
    /**
     * @Route("/admin/")
     * @Template()
     */
    public function dashboardAction()
    {
        return array();
    }

    public function changeLocaleAction()
    {
        $request = $this->container->get('request');

        if ($request->getLocale() === 'fr') {
            $request->setLocale('en');
        } else {
            $request->setLocale('fr');
        }

        $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $this->container->get('router')->generate('dashboard');

        return new RedirectResponse($url);
    }
}
