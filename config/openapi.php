<?php

return [
    'openapi' => '3.0.3',
    'info' => [
        'title' => config('app.name', 'Caritas System').' API',
        'version' => '1.0.0',
        'description' => 'API para autenticacao, paroquias e usuarios administrativos.',
    ],
    'servers' => [
        [
            'url' => rtrim((string) config('app.url'), '/').'/api',
            'description' => 'API',
        ],
    ],
    'tags' => [
        ['name' => 'Autenticacao'],
        ['name' => 'Paroquias'],
        ['name' => 'Usuarios'],
    ],
    'paths' => [
        '/diocese/login' => [
            'post' => [
                'tags' => ['Autenticacao'],
                'summary' => 'Login como admin da diocese',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/LoginRequest'],
                            'example' => [
                                'email' => 'test@example.com',
                                'password' => 'password',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => ['$ref' => '#/components/responses/LoginSuccess'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/parish/login' => [
            'post' => [
                'tags' => ['Autenticacao'],
                'summary' => 'Login em uma paroquia',
                'description' => 'Informe parish_id ou parish_slug junto com email e senha.',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ParishLoginRequest'],
                            'example' => [
                                'email' => 'admin@paroquia.com',
                                'password' => 'password',
                                'parish_id' => 1,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => ['$ref' => '#/components/responses/LoginSuccess'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/me' => [
            'get' => [
                'tags' => ['Autenticacao'],
                'summary' => 'Dados do usuario autenticado',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Usuario autenticado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'user' => ['$ref' => '#/components/schemas/User'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                ],
            ],
        ],
        '/logout' => [
            'post' => [
                'tags' => ['Autenticacao'],
                'summary' => 'Revoga o token atual',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Logout realizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                ],
            ],
        ],
        '/parishes' => [
            'get' => [
                'tags' => ['Paroquias'],
                'summary' => 'Lista paroquias ativas',
                'responses' => [
                    '200' => [
                        'description' => 'Lista de paroquias',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/Parish'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'post' => [
                'tags' => ['Paroquias'],
                'summary' => 'Cria uma paroquia',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreParishRequest'],
                            'example' => [
                                'name' => 'Paroquia Sao Jose',
                                'cnpj' => null,
                                'active' => true,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Paroquia criada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/Parish'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/roles' => [
            'get' => [
                'tags' => ['Usuarios'],
                'summary' => 'Lista roles disponiveis',
                'description' => 'Retorna roles do sistema e roles de vinculo com paroquia para preencher selects no frontend.',
                'responses' => [
                    '200' => [
                        'description' => 'Lista de roles',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'system_roles' => [
                                                    'type' => 'array',
                                                    'items' => ['$ref' => '#/components/schemas/RoleOption'],
                                                ],
                                                'parish_roles' => [
                                                    'type' => 'array',
                                                    'items' => ['$ref' => '#/components/schemas/RoleOption'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/users' => [
            'post' => [
                'tags' => ['Usuarios'],
                'summary' => 'Cria usuario administrativo',
                'description' => 'Token da diocese pode informar parish_ids. Token paroquial cria usuario na propria paroquia.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreUserRequest'],
                            'example' => [
                                'name' => 'Novo Admin',
                                'email' => 'novo@example.com',
                                'password' => 'password',
                                'parish_ids' => [1],
                                'parish_role' => 'admin',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Usuario criado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/User'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
    ],
    'components' => [
        'securitySchemes' => [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'Sanctum token',
            ],
        ],
        'schemas' => [
            'LoginRequest' => [
                'type' => 'object',
                'required' => ['email', 'password'],
                'properties' => [
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string', 'format' => 'password'],
                ],
            ],
            'ParishLoginRequest' => [
                'allOf' => [
                    ['$ref' => '#/components/schemas/LoginRequest'],
                    [
                        'type' => 'object',
                        'properties' => [
                            'parish_id' => ['type' => 'integer', 'nullable' => true],
                            'parish_slug' => ['type' => 'string', 'nullable' => true],
                        ],
                    ],
                ],
            ],
            'StoreParishRequest' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'cnpj' => ['type' => 'string', 'nullable' => true, 'maxLength' => 18],
                    'active' => ['type' => 'boolean', 'default' => true],
                ],
            ],
            'StoreUserRequest' => [
                'type' => 'object',
                'required' => ['name', 'email', 'password'],
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string', 'format' => 'password', 'minLength' => 8],
                    'system_role' => ['type' => 'string', 'enum' => ['user', 'diocese_admin']],
                    'parish_ids' => ['type' => 'array', 'items' => ['type' => 'integer']],
                    'parish_role' => ['type' => 'string', 'enum' => ['member', 'admin'], 'default' => 'admin'],
                ],
            ],
            'Parish' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'slug' => ['type' => 'string'],
                    'cnpj' => ['type' => 'string', 'nullable' => true],
                    'active' => ['type' => 'boolean'],
                ],
            ],
            'User' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'system_role' => ['type' => 'string', 'enum' => ['user', 'diocese_admin']],
                    'parishes' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/Parish'],
                    ],
                ],
            ],
            'RoleOption' => [
                'type' => 'object',
                'properties' => [
                    'value' => ['type' => 'string'],
                    'label' => ['type' => 'string'],
                ],
            ],
            'ValidationError' => [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string'],
                    'errors' => ['type' => 'object'],
                ],
            ],
        ],
        'responses' => [
            'LoginSuccess' => [
                'description' => 'Token criado com sucesso',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'token_type' => ['type' => 'string', 'example' => 'Bearer'],
                                'access_token' => ['type' => 'string'],
                                'abilities' => ['type' => 'array', 'items' => ['type' => 'string']],
                                'user' => ['$ref' => '#/components/schemas/User'],
                                'parish' => ['$ref' => '#/components/schemas/Parish', 'nullable' => true],
                            ],
                        ],
                    ],
                ],
            ],
            'Unauthenticated' => [
                'description' => 'Nao autenticado',
            ],
            'Forbidden' => [
                'description' => 'Sem permissao para executar esta acao',
            ],
            'ValidationError' => [
                'description' => 'Erro de validacao',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ValidationError'],
                    ],
                ],
            ],
        ],
    ],
];
