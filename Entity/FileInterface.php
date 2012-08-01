<?php

namespace Msi\Bundle\AdminBundle\Entity;

interface FileInterface
{
    function getUploadDir();

    function preUpload();

    function postUpload();

    function removeUpload();
}
