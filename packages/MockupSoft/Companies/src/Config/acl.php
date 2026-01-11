<?php

return [
    [
        'key'   => 'mockupsoft',
        'name'  => 'mockupsoft-companies::app.acl.mockupsoft',
        'route' => 'mockupsoft.companies.index',
        'sort'  => 10,
    ],
    [
        'key'   => 'mockupsoft.companies',
        'name'  => 'mockupsoft-companies::app.acl.companies',
        'route' => 'mockupsoft.companies.index',
        'sort'  => 1,
    ],
    [
        'key'   => 'mockupsoft.companies.create',
        'name'  => 'mockupsoft-companies::app.acl.create',
        'route' => 'mockupsoft.companies.store',
        'sort'  => 1,
    ],
    [
        'key'   => 'mockupsoft.companies.edit',
        'name'  => 'mockupsoft-companies::app.acl.edit',
        'route' => 'mockupsoft.companies.update',
        'sort'  => 2,
    ],
    [
        'key'   => 'mockupsoft.companies.delete',
        'name'  => 'mockupsoft-companies::app.acl.delete',
        'route' => 'mockupsoft.companies.delete',
        'sort'  => 3,
    ],
];
