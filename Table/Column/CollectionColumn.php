<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CollectionColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }
}
