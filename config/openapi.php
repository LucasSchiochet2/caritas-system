<?php

return [
    'openapi' => '3.0.3',
    'info' => [
        'title' => config('app.name', 'Caritas System').' API',
        'version' => '1.0.0',
        'description' => 'API para autenticação, estoque e clientes do bazar, paróquias e usuários administrativos.',
    ],
    'servers' => [
        [
            'url' => rtrim((string) config('app.url'), '/').'/api',
            'description' => 'API',
        ],
    ],
    'tags' => [
        ['name' => 'Autenticação'],
        ['name' => 'Bazar'],
        ['name' => 'Paróquias'],
        ['name' => 'Usuários'],
    ],
    'paths' => [
        '/diocese/login' => [
            'post' => [
                'tags' => ['Autenticação'],
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
                'tags' => ['Autenticação'],
                'summary' => 'Login em uma paróquia',
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
                'tags' => ['Autenticação'],
                'summary' => 'Dados do usuário autenticado',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Usuário autenticado',
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
            'patch' => [
                'tags' => ['Autenticação'],
                'summary' => 'Atualiza os dados do usuário autenticado',
                'description' => 'Permite alterar apenas nome, email e senha do próprio usuário.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateSelfRequest'],
                            'example' => [
                                'name' => 'Meu Nome',
                                'email' => 'meu-email@example.com',
                                'password' => 'nova-senha',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Usuário atualizado',
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
        '/logout' => [
            'post' => [
                'tags' => ['Autenticação'],
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
        '/bazaar-items' => [
            'get' => [
                'tags' => ['Bazar'],
                'summary' => 'Lista itens do estoque do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de itens do bazar',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/BazaarItem'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Bazar'],
                'summary' => 'Adiciona item ao estoque do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreBazaarItemRequest'],
                            'example' => [
                                'suggested_price' => 49.9,
                                'name' => 'Camisa social',
                                'color' => 'Azul',
                                'size' => 'M',
                                'gender' => 'masculino',
                                'quantity' => 4,
                                'condition' => 'seminovo',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Item criado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BazaarItem'],
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
        '/bazaar-items/{bazaarItem}' => [
            'patch' => [
                'tags' => ['Bazar'],
                'summary' => 'Atualiza item do estoque do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'bazaarItem',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateBazaarItemRequest'],
                            'example' => [
                                'suggested_price' => 59.9,
                                'quantity' => 2,
                                'condition' => 'usado',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Item atualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BazaarItem'],
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
            'delete' => [
                'tags' => ['Bazar'],
                'summary' => 'Exclui item do estoque do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'bazaarItem',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Item excluído'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/bazaar-customers' => [
            'get' => [
                'tags' => ['Bazar'],
                'summary' => 'Lista clientes do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de clientes do bazar',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/BazaarCustomer'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Bazar'],
                'summary' => 'Cadastra cliente do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreBazaarCustomerRequest'],
                            'example' => [
                                'name' => 'Maria Silva',
                                'birth_date' => '1988-04-20',
                                'cpf' => '123.456.789-01',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Cliente criado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BazaarCustomer'],
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
        '/bazaar-customers/{bazaarCustomer}' => [
            'patch' => [
                'tags' => ['Bazar'],
                'summary' => 'Atualiza cliente do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'bazaarCustomer',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateBazaarCustomerRequest'],
                            'example' => [
                                'name' => 'Maria Oliveira',
                                'birth_date' => '1988-05-21',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Cliente atualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BazaarCustomer'],
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
        '/parishes' => [
            'get' => [
                'tags' => ['Paróquias'],
                'summary' => 'Lista paróquias ativas',
                'responses' => [
                    '200' => [
                        'description' => 'Lista de paróquias',
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
                'tags' => ['Paróquias'],
                'summary' => 'Cria uma paróquia',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreParishRequest'],
                            'example' => [
                                'name' => 'Paróquia São José',
                                'cnpj' => null,
                                'active' => true,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Paróquia criada',
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
        '/parishes/{parish}' => [
            'patch' => [
                'tags' => ['Paróquias'],
                'summary' => 'Atualiza uma paróquia',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parish',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateParishRequest'],
                            'example' => [
                                'name' => 'Paróquia São José',
                                'cnpj' => '12.345.678/0001-90',
                                'active' => true,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Paróquia atualizada',
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
            'delete' => [
                'tags' => ['Paróquias'],
                'summary' => 'Exclui uma paróquia',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parish',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Paróquia excluída'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/roles' => [
            'get' => [
                'tags' => ['Usuários'],
                'summary' => 'Lista perfis disponíveis',
                'description' => 'Retorna perfis do sistema e perfis de vínculo com paróquia para preencher selects no frontend.',
                'responses' => [
                    '200' => [
                        'description' => 'Lista de perfis',
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
            'get' => [
                'tags' => ['Usuários'],
                'summary' => 'Lista usuários',
                'description' => 'Admin da diocese lista todos os usuários. Admin paroquial lista usuários vinculados à própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de usuários',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/User'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Usuários'],
                'summary' => 'Cria usuário administrativo',
                'description' => 'Token da diocese pode informar parish_ids. Token paroquial cria usuário na própria paróquia.',
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
                        'description' => 'Usuário criado',
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
        '/users/{user}' => [
            'patch' => [
                'tags' => ['Usuários'],
                'summary' => 'Atualiza um usuário',
                'description' => 'Admins podem editar usuários no seu escopo. O próprio usuário pode editar dados básicos também pelo endpoint /me.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'user',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateUserRequest'],
                            'example' => [
                                'name' => 'Usuário Atualizado',
                                'email' => 'usuario@example.com',
                                'parish_ids' => [1],
                                'parish_role' => 'admin',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Usuário atualizado',
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
            'delete' => [
                'tags' => ['Usuários'],
                'summary' => 'Exclui um usuário',
                'description' => 'Admins podem excluir usuários no seu escopo. Não é permitido excluir a própria conta por este endpoint.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'user',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Usuário excluído'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
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
            'StoreBazaarItemRequest' => [
                'type' => 'object',
                'required' => ['suggested_price', 'name', 'quantity', 'condition'],
                'properties' => [
                    'suggested_price' => ['type' => 'number', 'format' => 'float', 'minimum' => 0],
                    'name' => ['type' => 'string'],
                    'color' => ['type' => 'string', 'nullable' => true],
                    'size' => ['type' => 'string', 'nullable' => true],
                    'gender' => ['type' => 'string', 'nullable' => true],
                    'quantity' => ['type' => 'integer', 'minimum' => 0],
                    'condition' => ['type' => 'string'],
                ],
            ],
            'UpdateBazaarItemRequest' => [
                'type' => 'object',
                'properties' => [
                    'suggested_price' => ['type' => 'number', 'format' => 'float', 'minimum' => 0],
                    'name' => ['type' => 'string'],
                    'color' => ['type' => 'string', 'nullable' => true],
                    'size' => ['type' => 'string', 'nullable' => true],
                    'gender' => ['type' => 'string', 'nullable' => true],
                    'quantity' => ['type' => 'integer', 'minimum' => 0],
                    'condition' => ['type' => 'string'],
                ],
            ],
            'StoreBazaarCustomerRequest' => [
                'type' => 'object',
                'required' => ['name', 'birth_date', 'cpf'],
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'birth_date' => ['type' => 'string', 'format' => 'date'],
                    'cpf' => ['type' => 'string', 'maxLength' => 14],
                ],
            ],
            'UpdateBazaarCustomerRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'birth_date' => ['type' => 'string', 'format' => 'date'],
                    'cpf' => ['type' => 'string', 'maxLength' => 14],
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
            'UpdateParishRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'cnpj' => ['type' => 'string', 'nullable' => true, 'maxLength' => 18],
                    'active' => ['type' => 'boolean'],
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
            'UpdateSelfRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string', 'format' => 'password', 'minLength' => 8],
                ],
            ],
            'UpdateUserRequest' => [
                'allOf' => [
                    ['$ref' => '#/components/schemas/UpdateSelfRequest'],
                    [
                        'type' => 'object',
                        'properties' => [
                            'system_role' => ['type' => 'string', 'enum' => ['user', 'diocese_admin']],
                            'parish_ids' => ['type' => 'array', 'items' => ['type' => 'integer']],
                            'parish_role' => ['type' => 'string', 'enum' => ['member', 'admin']],
                        ],
                    ],
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
            'BazaarItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'suggested_price' => ['type' => 'number', 'format' => 'float'],
                    'name' => ['type' => 'string'],
                    'color' => ['type' => 'string', 'nullable' => true],
                    'size' => ['type' => 'string', 'nullable' => true],
                    'gender' => ['type' => 'string', 'nullable' => true],
                    'quantity' => ['type' => 'integer'],
                    'condition' => ['type' => 'string'],
                ],
            ],
            'BazaarCustomer' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'birth_date' => ['type' => 'string', 'format' => 'date'],
                    'cpf' => ['type' => 'string'],
                ],
            ],
            'UserParish' => [
                'allOf' => [
                    ['$ref' => '#/components/schemas/Parish'],
                    [
                        'type' => 'object',
                        'properties' => [
                            'role' => ['type' => 'string', 'enum' => ['member', 'admin']],
                        ],
                    ],
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
                        'items' => ['$ref' => '#/components/schemas/UserParish'],
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
                'description' => 'Não autenticado',
            ],
            'Forbidden' => [
                'description' => 'Sem permissão para executar esta ação',
            ],
            'ValidationError' => [
                'description' => 'Erro de validação',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ValidationError'],
                    ],
                ],
            ],
        ],
    ],
];
