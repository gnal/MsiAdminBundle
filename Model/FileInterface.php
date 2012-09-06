<?php

namespace Msi\Bundle\AdminBundle\Model;

interface FileInterface
{
    /**
     * Returns the path to the upload directory.
     */
    function getPath();

    /**
     * Returns the path to the uploaded file.
     */
    function getPathname($prefix);

    function processFile(\SplFileInfo $file);

    /**
     * Whitelist of allowed extensions.
     */
    function getAllowedExt();

    function getFile();

    function setFile($file);
}
