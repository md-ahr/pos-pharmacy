<?php

return [

    'session' => [
        'tenant_id' => 'pharmacy.tenant_id',
        'branch_id' => 'pharmacy.branch_id',
    ],

    'privileges' => [
        'pos' => 'pos.access',
        'inventory' => 'inventory.manage',
        'reports' => 'reports.view',
        'settings' => 'settings.manage',
    ],

    'registration' => [
        'owner_role_slug' => 'owner',
    ],

];
