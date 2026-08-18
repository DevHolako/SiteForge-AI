<?php

declare(strict_types=1);



if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}


// Delete plugin options and transients
$options = [
    'siteforge_ai_settings',
    'siteforge_ai_encryption_key',
    'siteforge_ai_batches',
    'siteforge_ai_api_keys',
];

array_map('delete_option', $options);


// Delete plugin transients
$transients = [
    'siteforge_ai_just_activated',
];

array_map('delete_transient', $transients);
