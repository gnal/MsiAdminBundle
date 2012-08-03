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
     * @Route("/{_locale}/admin/")
     * @Template()
     */
    public function dashboardAction()
    {
        return array();
    }

    /**
     * @Route("/{_locale}/admin/change-language.html")
     */
    public function localeAction()
    {
        if ($this->container->get('request')->getLocale() === 'fr') {
            $locale = 'en';
        } else {
            $locale = 'fr';
        }

        $url = $this->container->get('router')->generate('msi_admin_main_dashboard', array('_locale' => $locale));

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
