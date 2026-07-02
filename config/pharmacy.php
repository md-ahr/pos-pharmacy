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

    'pos' => [
        'tax_rate' => env('PHARMACY_TAX_RATE', '0.15'),
        'require_open_shift' => (bool) env('PHARMACY_REQUIRE_OPEN_SHIFT', true),
    ],

    'rate_limits' => [
        'registration_per_minute' => (int) env('PHARMACY_REGISTRATION_RATE_LIMIT', 5),
    ],

    'schedule' => [
        'expiry_check_time' => env('PHARMACY_EXPIRY_CHECK_TIME', '06:00'),
        'backup_time' => env('PHARMACY_BACKUP_TIME', '02:00'),
        'daily_report_time' => env('PHARMACY_DAILY_REPORT_TIME', '23:55'),
    ],

    'monitoring' => [
        'telescope_enabled' => (bool) env('TELESCOPE_ENABLED', false),
        'sentry_dsn' => env('SENTRY_LARAVEL_DSN'),
    ],

    'display' => [
        'datetime' => 'M j, Y g:i A',
        'time' => 'g:i A',
    ],

];
