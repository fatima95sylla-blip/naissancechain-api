<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'NaissanceChain API Documentation',
                'description' => 'API complète pour la gestion des actes de naissance avec blockchain et QR codes',
                'version' => '1.0.0',
                'contact' => [
                    'email' => 'support@naissancechain.gn',
                    'name' => 'Équipe NaissanceChain',
                ],
                'license' => [
                    'name' => 'MIT',
                    'url' => 'https://opensource.org/licenses/MIT',
                ],
            ],
            'servers' => [
                [
                    'url' => config('app.url') . '/api/v1',
                    'description' => 'API de Production',
                ],
                [
                    'url' => 'http://localhost:8000/api/v1',
                    'description' => 'API de Développement',
                ],
            ],
            'security' => [
                [
                    'BearerAuth' => [],
                ],
            ],
            'securityDefinitions' => [
                'BearerAuth' => [
                    'type' => 'apiKey',
                    'description' => 'JWT Bearer Token',
                    'name' => 'Authorization',
                    'in' => 'header',
                ],
            ],
            'schemes' => ['http', 'https'],
            'consumes' => ['application/json', 'multipart/form-data'],
            'produces' => ['application/json'],
        ],
    ],
    'paths' => [
        'base' => 'api/v1',
        'annotations' => base_path('app/Http/Controllers/Api'),
        'docs' => storage_path('api-docs'),
        'docs_json' => 'api-docs.json',
        'docs_yaml' => 'api-docs.yaml',
        'excludes' => [
            '_token*',
            '_method*',
            'test*',
            'health*',
            'telescope*',
            'horizon*',
            'nova*',
            'debugbar*',
        ],
    ],
    'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
    'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),
    'proxy' => false,
    'additional_config_url' => null,
    'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),
    'validator_url' => null,
    'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ],
    'constants' => [
        'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://localhost:8000'),
    ],
    'try_it_out' => [
        'enabled' => true,
        'url' => null,
        'auth' => [
            'enabled' => true,
            'name' => 'Authorization',
            'type' => 'bearer',
            'value' => 'Bearer {token}',
        ],
    ],
    'display' => [
        'default_models_expand_depth' => 2,
        'default_model_expand_depth' => 2,
        'default_model_sampling' => 'enum',
        'display_operation_id' => false,
        'display_request_duration' => true,
        'doc_expansion' => 'none',
        'filter' => true,
        'max_displayed_tags' => 0,
        'show_extensions' => false,
        'show_common_extensions' => false,
        'try_it_out_enabled' => true,
    ],
];
