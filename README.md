MsiAdminBundle
===
Add MsiGalleryBundle to your deps:

    [MsiAdminBundle]
        git=git://github.com/gnal/MsiAdminBundle.git
        target=bundles/Msi/Bundle/AdminBundle

Add to kernel:

    new Msi\Bundle\AdminBundle\MsiAdminBundle(),

Add to routing:

    MsiAdminBundle:
        resource: "@MsiAdminBundle/Controller/"
        type:     annotation
        prefix:   /
