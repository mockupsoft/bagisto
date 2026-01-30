<?php

return [
    /**
     * Tenants Management ACL (Patch 12-15)
     */
    [
        'key'   => 'tenants',
        'name'  => 'admin::app.acl.tenants',
        'route' => 'admin.tenants.index',
        'sort'  => 3.5,
    ],
    [
        'key'   => 'tenants.create',
        'name'  => 'admin::app.acl.create',
        'route' => 'admin.tenants.create',
        'sort'  => 1,
    ],
    [
        'key'   => 'tenants.view',
        'name'  => 'admin::app.acl.view',
        'route' => 'admin.tenants.show',
        'sort'  => 2,
    ],
    [
        'key'   => 'tenants.manage',
        'name'  => 'admin::app.acl.manage',
        'route' => 'admin.tenants.toggle',
        'sort'  => 3,
    ],
];
