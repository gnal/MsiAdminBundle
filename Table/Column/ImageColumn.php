<?php

namespace Msi\Bundle\AdminBundle\Table\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImageColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label' => 'image',
        ));
    }
}
