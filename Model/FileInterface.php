<?php

namespace Msi\Bundle\AdminBundle\Model;

interface FileInterface
{
    function getPath();

    function getPathname();

    function processFile(\SplFileInfo $file);

    function getAllowedExt();

    function getFile();

    function setFile($file);
}
