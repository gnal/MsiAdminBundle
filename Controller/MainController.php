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

    /**
     * @Route("/admin/locale.html")
     */
    public function localeAction()
    {
        $request = $this->container->get('request');

        if ($request->getLocale() === 'fr') {
            $request->setLocale('en');
        } else {
            $request->setLocale('fr');
        }

        $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $this->container->get('router')->generate('msi_admin_main_dashboard');

        return new RedirectResponse($url);
    }

    /**
     * @Route("/admin/limit.html")
     */
    public function limitAction()
    {
        $this->container->get('session')->set('limit', $this->container->get('request')->request->get('limit'));

        return new RedirectResponse(preg_replace('@\??&?page=\d+$@', '', $_SERVER['HTTP_REFERER']));
    }
}
