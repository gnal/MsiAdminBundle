<?php

namespace Msi\Bundle\AdminBundle\Twig\Extension;

class AdminExtension extends \Twig_Extension
{
    private $environment;

    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            'msi_admin_is_image' => new \Twig_Function_Method($this, 'isImage', array('is_safe' => array('html'))),
        );
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getGlobals()
    {
        $adminId = $this->container->get('request')->attributes->get('_admin');

        if ($adminId) {
            $admin = $this->container->get($this->container->get('request')->attributes->get('_admin'));

            return array('admin' => $admin);
        }

        return array();
    }

    public function isImage($pathname)
    {
        if (!is_file($_SERVER['DOCUMENT_ROOT'].$pathname)) {
            return false;
        }

        $handle = @getimagesize($_SERVER['DOCUMENT_ROOT'].$pathname);

        return $handle ? true : false;
    }

    public function getName()
    {
        return 'msi_admin';
    }
}
