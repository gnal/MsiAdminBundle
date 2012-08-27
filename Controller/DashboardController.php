<?php

namespace Msi\Bundle\AdminBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DashboardController extends ContainerAware
{
    public function dashboardAction()
    {
        return $this->container->get('templating')->renderResponse('MsiAdminBundle:Dashboard:dashboard.html.twig');
    }

    public function localeAction()
    {
        $locale = $this->container->get('request')->query->get('lang');
        $url = $this->container->get('router')->generate('msi_admin_main_dashboard', array('_locale' => $locale));

        return new RedirectResponse($url);
    }

    public function limitAction()
    {
        $limit = intval($this->container->get('request')->request->get('limit'));

        if ($limit < 1) {
            $limit = 10;
        }

        $this->container->get('session')->set('limit', $limit);

        if ($_SERVER['HTTP_REFERER']) {
            $url = preg_replace('@\??&?page=\d+@', '', $_SERVER['HTTP_REFERER']);
        } else {
            $url = '/';
        }

        return new RedirectResponse($url);
    }
}
