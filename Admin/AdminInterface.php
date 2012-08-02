<?php

namespace Msi\Bundle\AdminBundle\Admin;

interface AdminInterface
{
    function getAdminIds();

    function getBundleName();

    function getEntity();

    function getLikeFields();

    function setAdminIds(array $adminIds);

    function getChild();

    function setChild(AdminInterface $child);

    function hasChild();

    function getParent();

    function setParent(AdminInterface $parent);

    function hasParent();

    function createTableBuilder();

    function buildIndexTable($builder);

    function getTable($name);

    function createFormBuilder($name);

    function buildForm($builder);

    function getForm($name);

    function buildRoutes();
}
