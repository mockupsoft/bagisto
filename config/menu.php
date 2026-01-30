<?php

return [
    'admin' => [
        /**
         * Tenants Management (Patch 12-15)
         */
        [
            'key'        => 'tenants',
            'name'       => 'admin::app.components.layouts.sidebar.tenants',
            'route'      => 'admin.tenants.index',
            'sort'       => 3.5,
            'icon'       => 'icon-customer-2',
        ],
        // Tenant submenu items (accessed via tenant detail page)
        // These are shown in tenant detail view, not in main menu
    ],
];
