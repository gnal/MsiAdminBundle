MsiAdminBundle
==============
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
        resource: "@MsiAdminBundle/Resources/config/routing/routing.xml"
        defaults: { _locale: fr }

    msi_admin_loader:
        resource: .
        type: msi_admin
