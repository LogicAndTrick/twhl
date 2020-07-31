<?php

return [
    'api_site_key'                 => env('RECAPTCHA_PUBLIC_KEY', ''),
    'api_secret_key'               => env('RECAPTCHA_PRIVATE_KEY', ''),
    'version'                      => 'v2',
    'curl_timeout'                 => 10,
    'skip_ip'                      => [],
    'default_validation_route'     => 'biscolab-recaptcha/validate',
    'default_token_parameter_name' => 'token',
    'default_language'             => null,
    'default_form_id'              => 'biscolab-recaptcha-invisible-form',
    'explicit'                     => false,
    'tag_attributes'               => [
        'theme'            => 'light',
        'size'             => 'normal',
        'tabindex'         => 0,
        'callback'         => null,
        'expired-callback' => null,
        'error-callback'   => null,
    ]
];
