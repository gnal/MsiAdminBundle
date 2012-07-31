<?php

namespace Msi\Bundle\AdminBundle\Admin;

interface AdminInterface
{
    function getAdminIds();

    function getBundleName();

    function getLikeFields();

    function getObject();

    function setAdminIds(array $adminIds);

    function setObject($object);

    function getChild();

    function setChild(AdminInterface $child);

    function hasChild();

    function getParent();

    function setParent(AdminInterface $parent);

    function hasParent();

    function createTableBuilder();

    function buildTable($builder);

    function getTable();

    function createFormBuilder($name);

    function buildForm($builder);

    function getForm();

    function buildFilterForm($builder);

    function getFilterForm();

    function buildRoutes();
}
