MsiAdminBundle
===
Add to deps:

    [MsiAdminBundle]
        git=git://github.com/gnal/MsiAdminBundle.git
        target=bundles/Msi/Bundle/AdminBundle

Register bundle:

    new Msi\Bundle\AdminBundle\MsiAdminBundle(),

Register namespace:

    'Msi' => __DIR__.'/../vendor/bundles',

Configure routing:

    MsiAdminBundle:
        resource: "@MsiAdminBundle/Controller/"
        type:     annotation
        prefix:   /
