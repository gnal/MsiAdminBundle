<?php

namespace Msi\Bundle\AdminBundle\Provider;

interface EntityProviderInterface
{
    function get($id);

    function setModelManager($modelManager);
}
