<?php

namespace Msi\Bundle\AdminBundle\Model;

interface FileInterface
{
    function getUploadDir();

    function getWebDir();

    function processFile();

    function getAllowedExt();
}
